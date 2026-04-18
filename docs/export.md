# Exporting Data

Livewire Table Kit supports exporting your table data to CSV, XLSX, and PDF.

## Enabling Exports

To enable the export functionality, override the `supportsExport()` method in your table component:

```php
public function supportsExport(): bool
{
    return true;
}
```

This will automatically add an "Export" button to the table header with CSV, XLSX, and PDF options.

## Exportable Columns

By default, all visible columns are included in the export. You can exclude a specific column by using the `exportable(false)` method:

```php
Column::make('Actions')
    ->exportable(false)
```

## Customizing Export Filename

By default, the filename is derived from the table class name. You can customize it by overriding the `exportFilename()` method:

```php
protected function exportFilename(string $extension): string
{
    return 'user-report-' . now()->format('Y-m-d') . '.' . $extension;
}
```

## PDF Customization

Livewire Table Kit uses Barryvdh DomPDF for PDF generation. You can customize the PDF layout by overriding these methods:

- `exportPdfTitle()`: The title of the PDF report.
- `exportPdfPaperSize()`: Paper size (`a4`, `letter`, etc.).
- `exportPdfOrientation()`: Paper orientation (`portrait` or `landscape`).
- `exportPdfMargins()`: Array with `top`, `right`, `bottom`, `left` values.
- `exportPdfFontSize()`: The base font size for the PDF report.

### PDF Override Example

```php
protected function exportPdfTitle(): string
{
    return 'System Audit Log';
}

protected function exportPdfPaperSize(): string
{
    return 'a3';
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
        'bottom' => '10mm',
        'left' => '10mm',
    ];
}
```

## Advanced Customization

### Defining Custom Export Columns

If you want the exported data to have a different set of columns than what's displayed in the UI, you can override the `exportColumns()` method:

```php
protected function exportColumns(): array
{
    return [
        Column::make('ID')->field('id'),
        Column::make('User Name')->field('name'),
        Column::make('Email Address')->field('email'),
        Column::make('Created At')->value(fn ($row) => $row->created_at->format('Y-m-d H:i')),
    ];
}
```
