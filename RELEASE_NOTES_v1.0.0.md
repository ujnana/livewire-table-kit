# unlab/livewire-table-kit v1.0.0

Initial public release of the reusable Livewire table kit.

## Highlights

- Livewire table base component with search, filters, sorting, pagination, and row actions.
- Bulk delete support with selectable rows and event-driven confirmation flow.
- Optional export support for CSV, XLSX, and PDF.
- Custom export columns, export filename hooks, and PDF layout hooks.
- Package service provider with view loading and publishable views.
- Package README with installation and usage examples.
- Package license, publish metadata, and baseline package test.

## Included Components

- `BaseTable` abstraction for reusable data tables.
- `Column`, `BadgeColumn`, `ActionColumn`, `TableAction`, and `Filter` helpers.
- Package views for the table shell, empty state, action dropdown, badge rendering, pagination, and PDF export.

## Export Support

- CSV export streams the current filtered and sorted dataset.
- XLSX export uses Maatwebsite Excel.
- PDF export uses DomPDF.
- Export is opt-in per table through `supportsExport()`.
- Tables can override `exportColumns()` to include export-only fields.
- Tables can override `exportFilename()` to generate descriptive filenames.

## PDF Customization

- Table-specific PDF titles
- Paper size customization
- Orientation customization
- Margin customization
- Font size customization

## Requirements

- PHP 8.4
- Laravel 13
- Livewire 4
- Flux UI 2
- Maatwebsite Excel
- barryvdh/laravel-dompdf

## Install

```bash
composer require unlab/livewire-table-kit
```

## Publish Views

```bash
php artisan vendor:publish --tag=livewire-table-kit-views
```

## Notes

- This release follows semantic versioning.
- The package is designed for reuse across Laravel applications.
- View overrides are supported through Laravel's package view system.
