# Getting Started

Livewire Table Kit is a powerful, flexible, and schema-aware table component for Laravel Livewire 4, styled with Flux UI.

## Installation

Install the package via composer:

```bash
composer require unlab/livewire-table-kit
```

## Setup

### 1. Tailwind Configuration

Because this package uses Tailwind CSS, you need to tell your host application's Tailwind configuration to scan the package's views for classes. Add the package path to the `content` array in your `tailwind.config.js`:

```javascript
export default {
    content: [
        // ... your other paths
        './vendor/unlab/livewire-table-kit/resources/views/**/*.blade.php',
    ],
    // ...
}
```

After updating the config, run your build process:

```bash
npm run build
```

### 2. Publish Assets (Optional)

You should publish the configuration and view files if you need to customize the behavior or appearance:

```bash
php artisan vendor:publish --tag=livewire-table-kit-config
php artisan vendor:publish --tag=livewire-table-kit-views
```

### 3. Generate Your First Table

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
