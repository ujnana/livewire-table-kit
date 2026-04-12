<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class MakeTableCommand extends Command
{
    protected $signature = 'livewire-table-kit:make-table
        {model? : Full class name of the Eloquent model. If omitted, the command infers it from the table name}
        {name? : Table class name. If the first argument looks like a table name, this argument can be omitted}
        {--path= : Output directory. Defaults to a path derived from the namespace}
        {--namespace=App\\Livewire\\Tables : Table namespace}
        {--model-namespace=App\\Models : Namespace used when inferring a model class from a table name}
        {--force : Overwrite the file if it already exists}';

    protected $aliases = ['make:livewire-table'];

    protected $description = 'Create a Livewire table component for an Eloquent model';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $classes = $this->resolveClasses();

        if ($classes === null) {
            return self::FAILURE;
        }

        [$modelClass, $tableClass] = $classes;
        $namespace = trim((string) $this->option('namespace'), '\\');
        $directory = $this->resolveOutputDirectory($namespace);
        $filePath = $directory.DIRECTORY_SEPARATOR.$tableClass.'.php';

        if ($this->files->exists($filePath) && ! $this->option('force')) {
            $this->error("File already exists: {$filePath}");
            $this->line('Use --force to overwrite the existing file.');

            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists($directory);
        $this->files->put(
            $filePath,
            $this->buildStub(
                namespace: $namespace,
                class: $tableClass,
                modelClass: $modelClass,
                columns: $this->buildColumnsBlock($this->resolveModelInstance($modelClass))
            )
        );

        $this->info("Livewire table component created: {$filePath}");

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function resolveClasses(): ?array
    {
        $firstArgument = $this->normalizeClassName((string) $this->argument('model'));
        $secondArgument = trim((string) ($this->argument('name') ?? ''));
        $modelNamespace = trim((string) $this->option('model-namespace'), '\\');

        if ($firstArgument === '' && $secondArgument === '') {
            $this->error('You must provide either a model class or a table name.');

            return null;
        }

        if ($secondArgument !== '') {
            return [
                $firstArgument,
                $this->normalizeTableClass($secondArgument),
            ];
        }

        if ($this->looksLikeModelClass($firstArgument)) {
            return [
                $firstArgument,
                $this->defaultTableClassFromModel($firstArgument),
            ];
        }

        $tableClass = $this->normalizeTableClass($firstArgument);

        return [
            $this->inferModelClassFromTable($tableClass, $modelNamespace),
            $tableClass,
        ];
    }

    protected function resolveOutputDirectory(string $namespace): string
    {
        $path = trim((string) $this->option('path'));

        if ($path !== '') {
            $resolved = $this->isAbsolutePath($path) ? $path : base_path($path);

            return rtrim($resolved, DIRECTORY_SEPARATOR);
        }

        $namespacePath = $this->namespaceToPath($namespace);

        return $namespacePath === ''
            ? rtrim(app_path(), DIRECTORY_SEPARATOR)
            : rtrim(app_path($namespacePath), DIRECTORY_SEPARATOR);
    }

    protected function buildStub(string $namespace, string $class, string $modelClass, string $columns): string
    {
        $stub = $this->getStubContents();

        return str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
                '{{ model }}',
                '{{ model_basename }}',
                '{{ columns }}',
            ],
            [
                $namespace,
                $class,
                $modelClass,
                class_basename($modelClass),
                $columns,
            ],
            $stub
        );
    }

    protected function buildColumnsBlock(?Model $model): string
    {
        if ($model === null) {
            return <<<'PHP'
            Column::make('ID')
                ->field('id')
                ->sortable(),

            Column::make('Created At')
                ->field('created_at')
                ->sortable(),

            Column::make('Updated At')
                ->field('updated_at')
                ->sortable(),
PHP;
        }

        $definitions = [];

        foreach ($this->inspectSchemaColumns($model) as $column) {
            $name = $column['name'];

            if ($name === '' || $this->shouldSkipColumn($name)) {
                continue;
            }

            $definitions[] = $this->buildColumnDefinition(
                name: $name,
                type: $column['type']
            );
        }

        if ($definitions === []) {
            return <<<'PHP'
            Column::make('ID')
                ->field('id')
                ->sortable(),
PHP;
        }

        return implode(",\n\n", array_map(
            fn (string $definition): string => $this->indentBlock($definition, 12),
            $definitions
        )).',';
    }

    /**
     * @return array<int, array{name: string, type: ?string}>
     */
    protected function inspectSchemaColumns(Model $model): array
    {
        $connection = $model->getConnectionName() ?: config('database.default');
        $table = $model->getTable();

        try {
            $columns = Schema::connection($connection)->getColumns($table);

            if (is_array($columns) && $columns !== []) {
                return array_values(array_filter(array_map(
                    function (array $column): array {
                        $name = (string) ($column['name'] ?? $column['column_name'] ?? '');
                        $type = $this->resolveColumnType($column);

                        return [
                            'name' => $name,
                            'type' => $type,
                        ];
                    },
                    $columns
                ), static fn (array $column): bool => $column['name'] !== ''));
            }
        } catch (Throwable) {
            // Ignore schema errors and fall back to a generic scaffold.
        }

        try {
            $columnNames = Schema::connection($connection)->getColumnListing($table);

            if (is_array($columnNames) && $columnNames !== []) {
                return array_values(array_filter(array_map(
                    static fn (string $name): array => [
                        'name' => $name,
                        'type' => null,
                    ],
                    $columnNames
                ), static fn (array $column): bool => $column['name'] !== ''));
            }
        } catch (Throwable) {
            // Ignore schema errors and fall back to a generic scaffold.
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $column
     */
    protected function resolveColumnType(array $column): ?string
    {
        foreach (['type_name', 'type', 'data_type', 'column_type'] as $key) {
            if (! isset($column[$key])) {
                continue;
            }

            $value = trim((string) $column[$key]);

            if ($value !== '') {
                return strtolower($value);
            }
        }

        return null;
    }

    protected function buildColumnDefinition(string $name, ?string $type): string
    {
        $label = $this->labelForColumn($name);
        if ($this->shouldUseBadgeColumn($name, $type)) {
            return $this->buildBadgeColumnDefinition($name, $label, $type);
        }

        $definition = "Column::make('{$label}')\n    ->field('{$name}')";

        if ($this->shouldSearchColumn($name, $type)) {
            $definition .= "\n    ->searchable()";
        }

        if ($this->shouldSortColumn($name, $type)) {
            $definition .= "\n    ->sortable()";
        }

        return $definition;
    }

    protected function buildBadgeColumnDefinition(string $name, string $label, ?string $type): string
    {
        $definition = "BadgeColumn::make('{$label}')\n    ->field('{$name}')";

        if ($type === 'boolean' || $type === 'bool' || in_array($name, ['active', 'is_active', 'published', 'enabled'], true)) {
            $definition .= "\n    ->colorMap([\n        '1' => 'success',\n        '0' => 'danger',\n        'true' => 'success',\n        'false' => 'danger',\n    ])";

            return $definition;
        }

        if (in_array($name, ['status', 'state', 'visibility', 'priority', 'type', 'role'], true)) {
            $definition .= "\n    ->colorMap([\n        'active' => 'success',\n        'enabled' => 'success',\n        'published' => 'success',\n        'pending' => 'warning',\n        'draft' => 'default',\n        'inactive' => 'danger',\n        'disabled' => 'danger',\n        'archived' => 'danger',\n    ])";
        }

        return $definition;
    }

    protected function shouldSkipColumn(string $name): bool
    {
        return in_array($name, [
            'password',
            'remember_token',
            'two_factor_recovery_codes',
            'two_factor_secret',
        ], true) || Str::endsWith($name, ['_token', '_secret']);
    }

    protected function shouldSearchColumn(string $name, ?string $type): bool
    {
        if ($this->isStringLikeType($type)) {
            return true;
        }

        return (bool) preg_match('/(?:^|_)(name|title|email|status|slug|code|label)$/i', $name);
    }

    protected function shouldSortColumn(string $name, ?string $type): bool
    {
        if ($this->isDateLikeType($type) || $this->isNumericLikeType($type) || $this->isBooleanType($type)) {
            return true;
        }

        if ($this->isStringLikeType($type)) {
            return true;
        }

        return in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
    }

    protected function shouldUseBadgeColumn(string $name, ?string $type): bool
    {
        if ($this->isBooleanType($type)) {
            return true;
        }

        return in_array($name, [
            'status',
            'state',
            'type',
            'role',
            'priority',
            'visibility',
            'active',
            'is_active',
            'enabled',
            'published',
        ], true);
    }

    protected function isStringLikeType(?string $type): bool
    {
        return in_array($type, ['string', 'varchar', 'char', 'uuid', 'ulid', 'enum', 'citext'], true);
    }

    protected function isDateLikeType(?string $type): bool
    {
        return in_array($type, ['date', 'datetime', 'datetimetz', 'timestamp', 'time'], true);
    }

    protected function isNumericLikeType(?string $type): bool
    {
        return in_array($type, ['int', 'integer', 'bigint', 'smallint', 'mediumint', 'tinyint', 'decimal', 'double', 'float', 'real', 'numeric'], true);
    }

    protected function isBooleanType(?string $type): bool
    {
        return in_array($type, ['bool', 'boolean'], true);
    }

    protected function labelForColumn(string $name): string
    {
        return match (strtolower($name)) {
            'id' => 'ID',
            'uuid' => 'UUID',
            default => Str::headline($name),
        };
    }

    protected function indentBlock(string $block, int $spaces): string
    {
        $prefix = str_repeat(' ', $spaces);
        $lines = preg_split("/\r\n|\r|\n/", trim($block)) ?: [];

        return implode("\n", array_map(
            static fn (string $line): string => $prefix.$line,
            $lines
        ));
    }

    protected function normalizeClassName(string $class): string
    {
        return ltrim(trim($class), '\\');
    }

    protected function normalizeTableClass(string $name): string
    {
        $candidate = Str::studly(str_replace(['/', '\\'], ' ', trim($name)));

        return Str::endsWith($candidate, 'Table') ? $candidate : $candidate.'Table';
    }

    protected function looksLikeModelClass(string $class): bool
    {
        return Str::contains($class, '\\') || (class_exists($class) && is_subclass_of($class, Model::class));
    }

    protected function defaultTableClassFromModel(string $modelClass): string
    {
        return class_basename($modelClass).'Table';
    }

    protected function inferModelClassFromTable(string $tableClass, string $modelNamespace): string
    {
        $baseName = Str::beforeLast($tableClass, 'Table');
        $baseName = $baseName !== '' ? $baseName : $tableClass;
        $baseName = Str::singular($baseName);
        $modelNamespace = trim($modelNamespace, '\\');

        return $modelNamespace === ''
            ? Str::studly($baseName)
            : $modelNamespace.'\\'.Str::studly($baseName);
    }

    protected function resolveModelInstance(string $modelClass): ?Model
    {
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        return new $modelClass();
    }

    protected function namespaceToPath(string $namespace): string
    {
        $namespace = trim($namespace, '\\');

        if ($namespace === '' || $namespace === 'App') {
            return '';
        }

        if (Str::startsWith($namespace, 'App\\')) {
            $namespace = Str::after($namespace, 'App\\');
        }

        return str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    }

    protected function getStubContents(): string
    {
        $publishedStub = base_path('stubs/vendor/livewire-table-kit/table.stub');

        if ($this->files->exists($publishedStub)) {
            return $this->files->get($publishedStub);
        }

        return $this->files->get(__DIR__.'/../../../stubs/table.stub');
    }

    protected function isAbsolutePath(string $path): bool
    {
        return Str::startsWith($path, ['/', '\\']) || (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
