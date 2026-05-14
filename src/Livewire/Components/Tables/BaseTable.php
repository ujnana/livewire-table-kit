<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Stringable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Column;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Filters\Filter;

/**
 * @phpstan-type EmptyState array{
 *     icon: ?string,
 *     title: string,
 *     message: string,
 *     buttonIcon: ?string,
 *     buttonLabel: ?string,
 *     buttonUrl: mixed,
 *     buttonActionWire: ?string
 * }
 */
abstract class BaseTable extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * @var array<string, mixed>
     */
    public array $filters = [];

    public string $sortField = '';

    public string $sortDirection = 'asc';

    public int|string $perPage = 10;

    public array $selected = [];

    public bool $selectAllPage = false;

    public bool $loading = false;

    protected string $tableView = 'livewire-table-kit::livewire.components.tables.base-table';

    protected string $emptyStateView = 'livewire-table-kit::livewire.components.tables.empty-state';

    protected string $paginationView = 'livewire-table-kit::components.tables.pagination';

    /**
     * @var array<int, int|string>
     */
    protected array $defaultPerPageOptions = [10, 25, 50, 100, 'all'];

    protected $queryString = ['search', 'sortField', 'sortDirection', 'filters'];

    public function mount(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);
        $this->initializeFilters();
        $this->initializeSorting();
    }

    abstract public function query(): Builder|EloquentCollection;

    /**
     * @return array<int, Column>
     */
    abstract public function columns(): array;

    public function actionBulkDelete(array $payload): void {}

    /**
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * @return array<int, int|string>
     */
    public function perPageOptions(): array
    {
        return $this->defaultPerPageOptions;
    }

    public function supportsBulkDelete(): bool
    {
        return false;
    }

    public function supportsExport(): bool
    {
        return false;
    }

    protected function defaultSortField(): ?string
    {
        return null;
    }

    protected function defaultSortDirection(): string
    {
        return 'asc';
    }

    /**
     * @return EmptyState
     */
    protected function emptyState(): array
    {
        return [
            'icon' => null,
            'title' => 'No data found',
            'message' => 'Get started by adding your first item. It will appear here once it\'s created.',
            'buttonIcon' => null,
            'buttonLabel' => null,
            'buttonUrl' => null,
            'buttonActionWire' => null,
        ];
    }

    /**
     * @return EmptyState
     */
    public function getEmptyStateProperty(): array
    {
        return $this->emptyState();
    }

    public function hasSearch(): bool
    {
        return collect($this->columns())->contains(
            static fn (Column $column): bool => $column->searchable && $column->searchField !== null
        );
    }

    public function sortBy(string $field): void
    {
        $resolvedField = $this->resolveSortField($field);

        if ($resolvedField === null) {
            return;
        }

        if ($this->sortField === $resolvedField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $resolvedField;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
        $this->clearSelected();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelected();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->clearSelected();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);

        $this->resetPage();
        $this->clearSelected();

        if ($this->perPage === 'all') {
            $this->loading = true;
            $this->rowsQuery();
            $this->loading = false;
        }
    }

    public function updatedSelectAllPage(bool $value): void
    {
        if (! $value) {
            $this->clearSelected();

            return;
        }

        $currentPageIds = $this->currentPageRowIds();

        $this->selected = array_values(array_unique([
            ...$this->selected,
            ...$currentPageIds,
        ]));

        $this->selectAllPage = $currentPageIds !== [];
    }

    public function updatedSelected(): void
    {
        $selected = array_values(array_unique($this->selected));
        $currentPageIds = $this->currentPageRowIds();

        $this->selected = $selected;
        $this->selectAllPage = $currentPageIds !== [] && count(array_intersect($selected, $currentPageIds)) === count($currentPageIds);
    }

    public function clearSelected(): void
    {
        $this->selected = [];
        $this->selectAllPage = false;
    }

    #[On('refreshTable')]
    public function refreshTable(): void
    {
        $this->resetPage();
        $this->clearSelected();
    }

    public function bulkDelete(): void
    {
        if (! $this->supportsBulkDelete() || $this->selected === []) {
            return;
        }

        $this->dispatch('openBulkDeleteConfirm', $this->selected, $this->bulkDeleteConfirmationTitle());
    }

    #[On('bulkDeleteConfirmed')]
    public function onBulkDeleteConfirmed(array $payload): void
    {
        if (! $this->supportsBulkDelete() || $payload === []) {
            return;
        }

        $this->actionBulkDelete($payload);

        $this->clearSelected();

        $this->dispatch('notify', type: 'success', message: 'Items deleted successfully.');

        $this->refreshTable();
    }

    public function exportCsv(): StreamedResponse
    {
        $rowsQuery = $this->rowsQuery();
        $columns = $this->exportColumns();

        return response()->streamDownload(function () use ($rowsQuery, $columns): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                return;
            }

            fputcsv(
                $output,
                array_map(static fn (Column $column): string => $column->label, $columns)
            );

            if ($rowsQuery instanceof Builder) {
                $rowsQuery->chunk(500, function ($rows) use ($columns, $output): void {
                    foreach ($rows as $row) {
                        $this->writeExportRow($output, $row, $columns);
                    }
                });
            } elseif ($rowsQuery instanceof EloquentCollection) {
                foreach ($rowsQuery as $row) {
                    $this->writeExportRow($output, $row, $columns);
                }
            }

            fclose($output);
        }, $this->exportFilename('csv'), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(): BinaryFileResponse
    {
        return Excel::download(
            $this->buildSpreadsheetExport(),
            $this->exportFilename('xlsx'),
            'Xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportPdf(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo Pdf::loadView($this->exportPdfView(), $this->exportPdfViewData())
                ->setPaper($this->exportPdfPaperSize(), $this->exportPdfOrientation())
                ->output();
        }, $this->exportFilename('pdf'), ['Content-Type' => 'application/pdf']);
    }

    public function resolveColumnValue(mixed $row, Column $column): mixed
    {
        if ($column->value instanceof Closure) {
            return ($column->value)($row);
        }

        if (is_string($column->value) && $column->value !== '') {
            return data_get($row, $column->value);
        }

        if ($column->field !== null) {
            return data_get($row, $column->field);
        }

        return null;
    }

    public function resolveRowKey(mixed $row): mixed
    {
        return data_get($row, $this->getKeyName());
    }

    public function render(): View
    {
        $rowsQuery = $this->rowsQuery();
        $rows = $this->paginateRows($rowsQuery);

        return view($this->tableView, [
            'rows' => $rows,
            'columns' => $this->columns(),
            'availableFilters' => $this->filters(),
            'perPageOptions' => $this->perPageOptions(),
            'emptyState' => $this->emptyState(),
            'emptyStateView' => $this->emptyStateView,
            'paginationView' => $this->paginationView,
        ]);
    }

    protected function bulkDeleteConfirmationTitle(): string
    {
        return 'Delete selected items?';
    }

    /**
     * @return array<int, Column>
     */
    protected function exportableColumns(): array
    {
        return array_values(array_filter(
            $this->columns(),
            static fn (Column $column): bool => $column->exportable
        ));
    }

    /**
     * @return array<int, Column>
     */
    protected function exportColumns(): array
    {
        return $this->exportableColumns();
    }

    /**
     * @return array{headings: array<int, string>, rows: array<int, array<int, string>>}
     */
    protected function exportData(): array
    {
        $rowsQuery = $this->rowsQuery();
        $columns = $this->exportColumns();

        $rows = match (true) {
            $rowsQuery instanceof Builder => $rowsQuery->get(),
            $rowsQuery instanceof EloquentCollection => $rowsQuery,
            default => new EloquentCollection,
        };

        return [
            'headings' => array_map(
                static fn (Column $column): string => $column->label,
                $columns
            ),
            'rows' => $rows
                ->map(fn (mixed $row): array => $this->exportRowValues($row, $columns))
                ->values()
                ->all(),
        ];
    }

    protected function rowsQuery(): Builder|EloquentCollection|null
    {
        $query = $this->query();

        if ($query instanceof Builder) {
            $this->applySearchToBuilder($query);
            $this->applyFiltersToBuilder($query);
            $this->applySortingToBuilder($query);

            return $query;
        }

        if ($query instanceof EloquentCollection) {
            return $this->applyCollectionOperations($query);
        }

        return null;
    }

    protected function getKeyName(): string
    {
        $query = $this->query();

        if ($query instanceof Builder) {
            return $query->getModel()->getKeyName();
        }

        if ($query instanceof EloquentCollection) {
            $first = $query->first();

            if ($first instanceof Model) {
                return $first->getKeyName();
            }
        }

        return 'id';
    }

    protected function initializeFilters(): void
    {
        foreach ($this->filters() as $filter) {
            if (! array_key_exists($filter->key, $this->filters) && $filter->default !== null) {
                $this->filters[$filter->key] = $filter->default;
            }
        }
    }

    protected function initializeSorting(): void
    {
        if ($this->sortField !== '') {
            $this->sortField = $this->resolveSortField($this->sortField) ?? '';
            $this->sortDirection = $this->normalizeSortDirection($this->sortDirection);

            return;
        }

        $defaultField = $this->resolveSortField($this->defaultSortField());

        if ($defaultField === null) {
            return;
        }

        $this->sortField = $defaultField;
        $this->sortDirection = $this->normalizeSortDirection($this->defaultSortDirection());
    }

    protected function applySearchToBuilder(Builder $query): void
    {
        $search = trim($this->search);

        if ($search === '') {
            return;
        }

        $searchableColumns = collect($this->columns())
            ->filter(static fn (Column $column): bool => $column->searchable && $column->searchField !== null);

        if ($searchableColumns->isEmpty()) {
            return;
        }

        $query->where(function (Builder $builder) use ($search, $searchableColumns): void {
            foreach ($searchableColumns as $column) {
                if ($column->isRawExpression) {
                    $builder->orWhereRaw("LOWER({$column->searchField}) LIKE LOWER(?)", ["%{$search}%"]);

                    continue;
                }

                $builder->orWhere($column->searchField, 'like', "%{$search}%");
            }
        });
    }

    protected function applyFiltersToBuilder(Builder $query): void
    {
        foreach ($this->filters() as $filter) {
            $value = $this->filters[$filter->key] ?? null;

            if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                continue;
            }

            if ($filter->queryCallback instanceof Closure) {
                ($filter->queryCallback)($query, $value, $filter);

                continue;
            }

            if (is_array($value)) {
                $query->whereIn($filter->key, $value);

                continue;
            }

            $query->where($filter->key, $value);
        }
    }

    protected function applySortingToBuilder(Builder $query): void
    {
        $sortField = $this->resolveSortField($this->sortField);

        if ($sortField === null) {
            return;
        }

        $query->orderBy($sortField, $this->normalizeSortDirection($this->sortDirection));
    }

    protected function applyCollectionOperations(EloquentCollection $rows): EloquentCollection
    {
        $rows = $this->applySearchToCollection($rows);
        $rows = $this->applyFiltersToCollection($rows);

        return $this->applySortingToCollection($rows);
    }

    protected function applySearchToCollection(EloquentCollection $rows): EloquentCollection
    {
        $search = mb_strtolower(trim($this->search));

        if ($search === '') {
            return $rows;
        }

        $searchableColumns = collect($this->columns())
            ->filter(static fn (Column $column): bool => $column->searchable && $column->searchField !== null);

        if ($searchableColumns->isEmpty()) {
            return $rows;
        }

        return $rows->filter(function (mixed $row) use ($searchableColumns, $search): bool {
            foreach ($searchableColumns as $column) {
                $value = data_get($row, $column->searchField);

                if (is_scalar($value) && str_contains(mb_strtolower((string) $value), $search)) {
                    return true;
                }
            }

            return false;
        });
    }

    protected function applyFiltersToCollection(EloquentCollection $rows): EloquentCollection
    {
        foreach ($this->filters() as $filter) {
            $value = $this->filters[$filter->key] ?? null;

            if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                continue;
            }

            if ($filter->queryCallback instanceof Closure) {
                $result = ($filter->queryCallback)($rows, $value, $filter);

                if ($result instanceof EloquentCollection) {
                    $rows = $result;
                }

                continue;
            }

            if (is_array($value)) {
                $rows = $rows->filter(static fn (mixed $row): bool => in_array(data_get($row, $filter->key), $value, false));

                continue;
            }

            $rows = $rows->filter(static fn (mixed $row): bool => data_get($row, $filter->key) == $value);
        }

        return $rows->values();
    }

    protected function applySortingToCollection(EloquentCollection $rows): EloquentCollection
    {
        $sortField = $this->resolveSortField($this->sortField);

        if ($sortField === null) {
            return $rows->values();
        }

        $direction = $this->normalizeSortDirection($this->sortDirection);
        $sorted = $rows->sortBy(
            static fn (mixed $row): mixed => data_get($row, $sortField),
            options: SORT_NATURAL | SORT_FLAG_CASE,
            descending: $direction === 'desc',
        );

        return new EloquentCollection($sorted->values()->all());
    }

    protected function paginateRows(Builder|EloquentCollection|null $rowsQuery): LengthAwarePaginator
    {
        $perPage = $this->resolvePerPageValue($rowsQuery);
        $page = $this->getPage();

        if ($rowsQuery instanceof Builder) {
            return $rowsQuery->paginate($perPage, page: $page);
        }

        if ($rowsQuery instanceof EloquentCollection) {
            $items = $rowsQuery->values();
            $total = $items->count();

            return new LengthAwarePaginator(
                $items->forPage($page, $perPage)->values(),
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        }

        return new LengthAwarePaginator([], 0, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function exportPdfViewData(): array
    {
        return [
            'title' => $this->exportPdfTitle(),
            'paperSize' => $this->exportPdfPaperSize(),
            'orientation' => $this->exportPdfOrientation(),
            'margins' => $this->exportPdfMargins(),
            'fontSize' => $this->exportPdfFontSize(),
            ...$this->exportData(),
        ];
    }

    protected function exportPdfView(): string
    {
        return 'livewire-table-kit::livewire.components.tables.export-pdf';
    }

    protected function exportPdfTitle(): string
    {
        return $this->exportTitle();
    }

    protected function exportPdfPaperSize(): string
    {
        return 'a4';
    }

    protected function exportPdfOrientation(): string
    {
        return 'landscape';
    }

    /**
     * @return array{top: string, right: string, bottom: string, left: string}
     */
    protected function exportPdfMargins(): array
    {
        return [
            'top' => '14mm',
            'right' => '12mm',
            'bottom' => '14mm',
            'left' => '12mm',
        ];
    }

    protected function exportPdfFontSize(): string
    {
        return '10px';
    }

    protected function exportTitle(): string
    {
        $classBaseName = class_basename(static::class);

        if ($classBaseName === 'Table') {
            $tableSegment = Str::of(static::class)
                ->beforeLast('\\Table')
                ->afterLast('\\')
                ->toString();

            return Str::headline($tableSegment !== '' ? $tableSegment : 'Table');
        }

        return Str::headline($classBaseName);
    }

    protected function exportFilename(string $extension): string
    {
        return Str::of(static::class)
            ->replace('Unlab\\LivewireTableKit\\Livewire\\', '')
            ->replace('\\', '-')
            ->lower()
            ->append('.'.$extension)
            ->toString();
    }

    protected function buildSpreadsheetExport(): object
    {
        $data = $this->exportData();

        return new class($data['rows'], $data['headings']) implements FromCollection, WithHeadings
        {
            /**
             * @param  array<int, array<int, string>>  $rows
             * @param  array<int, string>  $headings
             */
            public function __construct(
                private readonly array $rows,
                private readonly array $headings,
            ) {}

            public function collection(): EloquentCollection
            {
                return new EloquentCollection($this->rows);
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };
    }

    /**
     * @return array<int, mixed>
     */
    protected function currentPageRowIds(): array
    {
        $paginator = $this->paginateRows($this->rowsQuery());
        $keyName = $this->getKeyName();

        return collect($paginator->items())
            ->map(static fn (mixed $row): mixed => data_get($row, $keyName))
            ->filter(static fn (mixed $value): bool => $value !== null)
            ->values()
            ->all();
    }

    protected function resolvePerPageValue(Builder|EloquentCollection|null $rowsQuery = null): int
    {
        if ($this->perPage !== 'all') {
            return max(1, (int) $this->perPage);
        }

        if ($rowsQuery instanceof Builder) {
            return max(1, $rowsQuery->toBase()->getCountForPagination());
        }

        if ($rowsQuery instanceof EloquentCollection) {
            return max(1, $rowsQuery->count());
        }

        return 1;
    }

    protected function normalizePerPage(int|string $perPage): int|string
    {
        $options = $this->perPageOptions();

        return in_array($perPage, $options, true) ? $perPage : $options[0];
    }

    protected function normalizeSortDirection(string $direction): string
    {
        return $direction === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @param  array<int, Column>  $columns
     * @return array<int, string>
     */
    protected function exportRowValues(mixed $row, array $columns): array
    {
        return array_map(
            fn (Column $column): string => $this->formatExportValue(
                $this->resolveColumnValue($row, $column),
                $column
            ),
            $columns
        );
    }

    protected function writeExportRow(mixed $output, mixed $row, array $columns): void
    {
        fputcsv($output, $this->exportRowValues($row, $columns));
    }

    protected function formatExportValue(mixed $value, Column $column): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof BackedEnum) {
            $value = (string) $value->value;
        } elseif ($value instanceof Stringable) {
            $value = (string) $value;
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        } elseif (! is_scalar($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        $formattedValue = (string) $value;

        if ($column->isHtml) {
            $formattedValue = strip_tags($formattedValue);
        }

        return $formattedValue;
    }

    protected function resolveSortField(?string $field): ?string
    {
        if ($field === null || $field === '') {
            return null;
        }

        return collect($this->columns())
            ->filter(static fn (Column $column): bool => $column->sortable)
            ->flatMap(static fn (Column $column): array => array_values(array_filter([
                $column->sortField,
                $column->field,
            ])))
            ->contains($field) ? $field : null;
    }
}
