# Getting Started

Livewire Table Kit is a powerful, flexible, and schema-aware table component for Laravel Livewire 4, styled with Flux UI.

## Installation

Install the package via composer:

```bash
composer require unlab/livewire-table-kit
```

## Setup

### 1. Publish Assets

You should publish the configuration and view files to customize the behavior and appearance:

```bash
php artisan vendor:publish --tag=livewire-table-kit-config
php artisan vendor:publish --tag=livewire-table-kit-views
```

### 2. Generate Your First Table

The quickest way to get started is by using the built-in generator. Provide an Eloquent model and a name for your table component:

```bash
php artisan make:livewire-table App\\Models\\User UsersTable
```

This will create a new Livewire component at `app/Livewire/Tables/UsersTable.php`.

### 3. Usage in Blade

Render the generated table in any Blade view using the Livewire tag:

```blade
<livewire:tables.users-table />
```

## Core Concepts

Every table component extends the `Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable` class. At a minimum, you must implement two methods:

- `query()`: Returns an Eloquent Builder or Collection.
- `columns()`: Returns an array of Column definitions.

### Basic Implementation

```php
public function query(): Builder
{
    return User::query();
}

public function columns(): array
{
    return [
        Column::make('Name')->field('name')->searchable()->sortable(),
        Column::make('Email')->field('name')->searchable(),
    ];
}
```
