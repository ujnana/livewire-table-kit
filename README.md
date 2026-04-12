# unlab/livewire-table-kit

Reusable Livewire table component kit with:

- search
- filters
- sorting
- pagination
- row actions
- bulk delete
- CSV / XLSX / PDF export

## Install

```bash
composer require unlab/livewire-table-kit
```

## Publish views

```bash
php artisan vendor:publish --tag=livewire-table-kit-views
```

## Publish config and MCP assets

```bash
php artisan vendor:publish --tag=livewire-table-kit-config
php artisan vendor:publish --tag=livewire-table-kit-mcp
php artisan vendor:publish --tag=livewire-table-kit-stubs
php artisan vendor:publish --tag=livewire-table-kit-lang
```

## Install MCP AI skills

If you want to use the package MCP server from an AI client, install the local `.mcp` config file:

```bash
php artisan livewire-table-kit:install-mcp
```

That command creates `.mcp/livewire-table-kit.json` with a `php artisan livewire-table-kit:mcp` server entry.

## Generate a table component

Use the package generator command to scaffold a table component from an Eloquent model:

```bash
php artisan livewire-table-kit:make-table App\\Models\\User UsersTable
```

You can also use the alias:

```bash
php artisan make:livewire-table App\\Models\\User UsersTable
```

If you already have a table name and want the command to infer the model, pass only the table name:

```bash
php artisan make:livewire-table UsersTable --model-namespace=App\\Models
```

Options:

- `--path=` to change the output directory
- `--namespace=` to change the generated namespace
- `--model-namespace=` to change the namespace used for model inference
- `--force` to overwrite an existing file

By default the command writes to a path derived from the namespace, so `App\Livewire\Tables` maps to `app/Livewire/Tables`.

The generator uses basic schema heuristics:

- string-like columns become searchable and sortable
- numeric/date/boolean columns become sortable
- status-like and active-like columns become badge columns
- sensitive fields like passwords and tokens are skipped

## Structure

- `src/Livewire/Components/Tables/BaseTable.php`
- `src/Livewire/Components/Tables/Columns/*`
- `src/Livewire/Components/Tables/Filters/Filter.php`
- `resources/views/livewire/components/tables/*`
- `resources/views/components/tables/pagination.blade.php`

## Usage

Register the package in a Laravel app, then extend `Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable` in your table component.

The table component is designed to be used as a normal Livewire component:

1. Extend `BaseTable`.
2. Implement `query()` and `columns()`.
3. Optionally define `filters()`, `supportsExport()`, `supportsBulkDelete()`, and the export/PDF hooks.
4. Render it from Blade using the Livewire component tag.

### Minimal table example

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Platform\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Models\User;
use Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Column;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Filters\Filter;

class Table extends BaseTable
{
    public function query(): Builder|EloquentCollection
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name')
                ->field('name')
                ->searchable()
                ->sortable(),

            Column::make('Email')
                ->field('email')
                ->searchable()
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('status', 'Status', [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]),
        ];
    }

    public function supportsExport(): bool
    {
        return true;
    }

    protected function exportFilename(string $extension): string
    {
        return 'platform-users-'.now()->toDateString().'.'.$extension;
    }
}
```

### Blade usage

```blade
<livewire:platform.user.table />
```

You can also use the component inside a page layout or modal content, as long as the Livewire component is mounted in a normal Blade context.

### Column helpers

- `Column::make('Label')` for normal columns
- `->field('database_column')` for searchable and sortable fields
- `->view('your.view')` for custom cell rendering
- `->html()` if the value returns HTML that should not be escaped
- `->exportable(false)` to exclude a column from export

### Custom export columns

Use `exportColumns()` when the exported file should contain more data than the UI table, or when the export column order should be different from the visible table.

```php
protected function exportColumns(): array
{
    return [
        Column::make('Name')
            ->field('name'),

        Column::make('Email')
            ->field('email'),

        Column::make('Notes')
            ->field('notes'),

        Column::make('Created At')
            ->value(static fn ($row): string => optional($row->created_at)?->format('Y-m-d H:i:s') ?? ''),
    ];
}
```

This is useful when:

- the table UI shows a simplified set of columns
- the export needs extra audit fields or helper fields
- the export file should be normalized for downstream reporting

### PDF overrides

PDF export defaults are intentionally conservative:

- title: derived from the table class name
- paper size: `A4`
- orientation: `landscape`
- margins: `14mm 12mm 14mm 12mm`
- font size: `10px`

Override the hooks below when a table needs a custom PDF layout:

```php
protected function exportPdfTitle(): string
{
    return 'Tenant Overview';
}

protected function exportPdfPaperSize(): string
{
    return 'a4';
}

protected function exportPdfOrientation(): string
{
    return 'landscape';
}

protected function exportPdfMargins(): array
{
    return [
        'top' => '10mm',
        'right' => '10mm',
        'bottom' => '12mm',
        'left' => '10mm',
    ];
}

protected function exportPdfFontSize(): string
{
    return '9px';
}
```

### Bulk delete and row actions

Enable bulk delete when the table should support multi-row deletion:

```php
public function supportsBulkDelete(): bool
{
    return true;
}

public function actionBulkDelete(array $payload): void
{
    User::whereIn('id', $payload)->delete();
}
```

The component dispatches an `openBulkDeleteConfirm` event with the selected IDs and a confirmation title. After deletion, it dispatches a `notify` event with a success message and refreshes the table.

For row-level actions, use `ActionColumn` and `TableAction`:

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions\TableAction;

ActionColumn::make()->actions([
    TableAction::wire(
        'Edit',
        static fn ($row): string => "editItem('{$row->id}')",
        icon: 'pencil-square'
    ),
    TableAction::link(
        'View',
        static fn ($row): string => route('users.show', $row),
        icon: 'eye'
    ),
]);
```

`TableAction::wire()` is best for Livewire events or component methods, while `TableAction::link()` is best for direct URLs.

### Action columns

Use `ActionColumn` when you want a row actions dropdown:

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions\TableAction;

ActionColumn::make()->actions([
    TableAction::wire('Edit', static fn ($row): string => "editItem('{$row->id}')", icon: 'pencil-square'),
    TableAction::link('View', static fn ($row): string => route('users.show', $row), icon: 'eye'),
]);
```

### Filter helpers

- `Filter::select()` for select inputs
- `Filter::text()` for text search inputs
- `Filter::date()` for date inputs
- `Filter::number()` for numeric inputs

You can also pass an array configuration to `Filter::select()` when you want to keep the filter definition declarative.

### Export behavior

- Export is opt-in. Override `supportsExport()` and return `true`.
- Default export columns are the visible exportable columns.
- Override `exportColumns()` when the export should include extra fields or a different column order.
- Override `exportFilename()` to control the filename per table.
- Override `exportPdfTitle()`, `exportPdfPaperSize()`, `exportPdfOrientation()`, `exportPdfMargins()`, and `exportPdfFontSize()` for PDF-specific tuning.
- CSV export streams the current filtered/sorted dataset.
- XLSX export uses Maatwebsite Excel and the same export column definitions.
- PDF export uses DomPDF and the package PDF Blade view.

## Views

The package views are loaded with the namespace `livewire-table-kit::`.

You can override a package view in your app by publishing the package views and editing the copied Blade file in `resources/views/vendor/livewire-table-kit`.

Common view paths:

- `livewire-table-kit::livewire.components.tables.base-table`
- `livewire-table-kit::livewire.components.tables.columns.action-column`
- `livewire-table-kit::livewire.components.tables.columns.badge-column`
- `livewire-table-kit::livewire.components.tables.empty-state`
- `livewire-table-kit::livewire.components.tables.export-pdf`
- `livewire-table-kit::components.tables.pagination`

## Requirements

- PHP 8.4
- Laravel 13
- Livewire 4
- Flux UI 2
- Maatwebsite Excel
- barryvdh/laravel-dompdf

## MCP

The package includes a small MCP server command at `livewire-table-kit:mcp` for scaffolding table classes from an Eloquent model.

The server currently exposes:

- `livewire_table_generate`
- `livewire_table_schema`

The schema text is stored in `docs/mcp-schema.md` and is also used as a fallback if the file is unavailable at runtime.

## Official Reference

Laravel package development docs:

- https://laravel.com/docs/master/packages

That page is the canonical reference for package service providers, `loadViewsFrom`, view overriding, and `vendor:publish` behavior.
