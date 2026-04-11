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

## Structure

- `src/Livewire/Components/Tables/BaseTable.php`
- `src/Livewire/Components/Tables/Columns/*`
- `src/Livewire/Components/Tables/Filters/Filter.php`
- `resources/views/livewire/components/tables/*`
- `resources/views/components/tables/pagination.blade.php`

## Usage

Register the package in a Laravel app, then extend `Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable` in your table component.

## Views

The package views are loaded with the namespace `livewire-table-kit::`.

## Requirements

- PHP 8.4
- Laravel 13
- Livewire 4
- Flux UI 2
- Maatwebsite Excel
- barryvdh/laravel-dompdf

## Official Reference

Laravel package development docs:

- https://laravel.com/docs/master/packages

That page is the canonical reference for package service providers, `loadViewsFrom`, view overriding, and `vendor:publish` behavior.
