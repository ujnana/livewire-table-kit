<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Unlab\LivewireTableKit\Tests\Fixtures\Models\User;

beforeEach(function (): void {
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('status')->default('active');
        $table->boolean('active')->default(true);
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });
});

it('creates a Livewire table component from an inferred model and schema columns', function (): void {
    $outputDirectory = sys_get_temp_dir().'/livewire-table-kit-'.uniqid('', true);
    File::ensureDirectoryExists($outputDirectory);

    $this->artisan('make:livewire-table', [
        'model' => 'UsersTable',
        '--path' => $outputDirectory,
        '--model-namespace' => 'Unlab\\LivewireTableKit\\Tests\\Fixtures\\Models',
    ])->assertExitCode(0);

    $filePath = $outputDirectory.'/UsersTable.php';

    expect(File::exists($filePath))->toBeTrue();

    $contents = File::get($filePath);

    expect($contents)->toContain('namespace App\\Livewire\\Tables;');
    expect($contents)->toContain('class UsersTable extends BaseTable');
    expect($contents)->toContain('return User::query();');
    expect($contents)->toContain("Column::make('Name')");
    expect($contents)->toContain("Column::make('Email')");
    expect($contents)->toContain("BadgeColumn::make('Status')");
    expect($contents)->toContain("BadgeColumn::make('Active')");
    expect($contents)->toContain("Column::make('Created At')");
    expect($contents)->toContain("Column::make('Updated At')");
    expect($contents)->toContain('->searchable()');
    expect($contents)->toContain('->sortable()');
    expect($contents)->toContain("->colorMap([");
    expect($contents)->not->toContain('password');
    expect($contents)->not->toContain('remember_token');

    File::deleteDirectory($outputDirectory);
});

it('derives the output path from the namespace when no path is provided', function (): void {
    $this->artisan('make:livewire-table', [
        'model' => User::class,
        '--namespace' => 'App\\Livewire\\Platform\\Tables',
    ])->assertExitCode(0);

    $filePath = app_path('Livewire/Platform/Tables/UserTable.php');

    expect(File::exists($filePath))->toBeTrue();

    $contents = File::get($filePath);

    expect($contents)->toContain('namespace App\\Livewire\\Platform\\Tables;');

    File::delete($filePath);
});

it('does not overwrite an existing table unless forced', function (): void {
    $outputDirectory = sys_get_temp_dir().'/livewire-table-kit-'.uniqid('', true);
    File::ensureDirectoryExists($outputDirectory);

    $filePath = $outputDirectory.'/UsersTable.php';
    File::put($filePath, '<?php // existing file');

    $this->artisan('livewire-table-kit:make-table', [
        'model' => User::class,
        'name' => 'UsersTable',
        '--path' => $outputDirectory,
    ])->assertExitCode(1);

    expect(File::get($filePath))->toBe('<?php // existing file');

    File::deleteDirectory($outputDirectory);
});
