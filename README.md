# unlab/livewire-table-kit

Reusable Livewire table component kit with:

- search
- filters
- sorting
- pagination
- row actions
- bulk delete
- CSV / XLSX / PDF export

## Documentation

- [Getting Started](docs/getting-started.md)
- [Table Columns](docs/columns.md)
- [Table Filters](docs/filters.md)
- [Table Actions & Bulk Delete](docs/actions.md)
- [Exporting Data (CSV, XLSX, PDF)](docs/export.md)
- [AI Skills & MCP Server](docs/mcp.md)

## Install

```bash
composer require unlab/livewire-table-kit
```

## Setup

### 1. Tailwind Configuration

Add the package path to your Tailwind configuration to ensure styles are compiled.

**For Tailwind v4 (app.css):**
```css
@source "../../vendor/unlab/livewire-table-kit/resources/views/**/*.blade.php";
```

**For Tailwind v3 (tailwind.config.js):**
```javascript
content: [
    './vendor/unlab/livewire-table-kit/resources/views/**/*.blade.php',
],
```

Then run: `npm run build`

### 2. Publish views (Optional)

```bash
php artisan vendor:publish --tag=livewire-table-kit-views
```

### 3. Publish config and MCP assets (Optional)

```bash
php artisan vendor:publish --tag=livewire-table-kit-config
php artisan vendor:publish --tag=livewire-table-kit-mcp
php artisan vendor:publish --tag=livewire-table-kit-stubs
php artisan vendor:publish --tag=livewire-table-kit-lang
```

### Install AI skills (Optional)

If you want to use the package MCP server or project-local AI skills:

```bash
# Install local MCP server config
php artisan livewire-table-kit:install-mcp

# Install skill files for Codex and project-local workflows
php artisan livewire-table-kit:install-skill
```

## Generate a table component

Use the package generator command to scaffold a table component from an Eloquent model:

```bash
php artisan make:livewire-table App\\Models\\User UsersTable
```

The generator uses schema heuristics to automatically determine:
- Searchable and sortable fields
- Badge columns for statuses and booleans
- Sensible labels

## Advanced Usage

### Full Example

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Column;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\BadgeColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions\TableAction;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Filters\Filter;

class UsersTable extends BaseTable
{
    public function query(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('ID')->field('id')->sortable(),
            
            Column::make('Name')
                ->field('name')
                ->searchable()
                ->sortable(),

            Column::make('Email')
                ->field('email')
                ->searchable()
                ->sortable(),

            BadgeColumn::make('Status')
                ->field('status')
                ->colorMap([
                    'active' => 'success',
                    'pending' => 'warning',
                    'inactive' => 'danger',
                ]),

            ActionColumn::make()->actions([
                TableAction::wire('Edit', fn ($row) => "edit('{$row->id}')", icon: 'pencil'),
                TableAction::link('View', fn ($row) => route('users.show', $row), icon: 'eye'),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('role', 'Role', [
                'admin' => 'Administrator',
                'user' => 'User',
            ]),
            
            Filter::date('created_at', 'Created After'),
            
            // Custom query filter
            Filter::text('search_bio', 'Search Bio')
                ->query(fn ($query, $value) => $query->where('bio', 'like', "%{$value}%")),
        ];
    }

    public function supportsExport(): bool => true;
    public function supportsBulkDelete(): bool => true;

    public function actionBulkDelete(array $payload): void
    {
        User::whereIn('id', $payload)->delete();
    }
}
```

### Column Helpers

- `Column::make('Label')`: Create a column.
- `->field('db_column')`: Maps to a database column.
- `->value(fn ($row) => ...)`: Define a custom value resolver.
- `->searchable(?string $field = null)`: Enable search.
- `->searchableRaw(string $expression)`: Search using a raw SQL expression (e.g., `CONCAT(first_name, ' ', last_name)`).
- `->sortable(?string $field = null)`: Enable sorting.
- `->view('custom.cell-view')`: Render cells using a custom Blade view.
- `->align('center')`: Set text alignment (`left`, `center`, `right`).
- `->headerAlign('center')`: Set header alignment.
- `->html()`: Treat the value as raw HTML.
- `->exportable(false)`: Exclude from exports.

### Badge Columns

`BadgeColumn` uses Flux UI's badge styles.

```php
BadgeColumn::make('Status')
    ->field('status')
    ->colorMap([
        'active' => 'success',   // emerald
        'pending' => 'warning',  // amber
        'inactive' => 'danger',  // rose
        'default' => 'default',  // zinc
    ]);
```

Available colors: `primary`, `success`, `warning`, `danger`, `default`.

### Filter Types

- `Filter::select(key, label, options)`
- `Filter::text(key, label, placeholder)`
- `Filter::date(key, label)`
- `Filter::number(key, label, placeholder)`

### Events

The component listens for and dispatches several events:

- `refreshTable`: (Inbound) Refreshes the table data and resets pagination.
- `openBulkDeleteConfirm`: (Outbound) Triggered when bulk delete is clicked.
- `bulkDeleteConfirmed`: (Inbound) Triggered when the user confirms deletion.
- `notify`: (Outbound) Standard notification event with `type` and `message`.

## Customization

### BaseTable Hooks

Override these methods in your table class to customize behavior:

| Method | Description | Default |
|--------|-------------|---------|
| `defaultSortField()` | Initial sort column | `null` |
| `defaultSortDirection()` | Initial sort direction | `'asc'` |
| `perPageOptions()` | Options for the per-page selector | `[10, 25, 50, 100, 'all']` |
| `emptyState()` | Configure the empty state display | Array with title/message |
| `exportFilename(ext)` | Name of the exported file | Derived from class name |

### PDF Customization

```php
protected function exportPdfTitle(): string => 'User Report';
protected function exportPdfOrientation(): string => 'landscape'; // portrait or landscape
protected function exportPdfPaperSize(): string => 'a4';
protected function exportPdfMargins(): array => ['top' => '10mm', ...];
protected function exportPdfFontSize(): string => '9px';
```

## Requirements

- **PHP 8.4+**
- **Laravel 13+**
- **Livewire 4+**
- **Flux UI 2+** (The UI depends on Flux components)
- **Maatwebsite Excel** (For XLSX exports)
- **Barryvdh DomPDF** (For PDF exports)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
