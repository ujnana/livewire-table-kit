# Table Columns

Livewire Table Kit provides a fluent API for defining columns in your table.

## Basic Usage

The `Column` class is the primary building block for all your table columns.

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Column;

public function columns(): array
{
    return [
        Column::make('Name')
            ->field('name')
            ->searchable()
            ->sortable(),
    ];
}
```

## Available Column Types

### 1. `Column` (Default)

Used for plain text or simple HTML columns.

- `->field(string $field)`: Map to a database field.
- `->value(Closure $callback)`: Custom value calculation.
- `->searchable(?string $field = null)`: Enable search (defaults to column field).
- `->searchableRaw(string $expression)`: Search using raw SQL.
- `->sortable(?string $field = null)`: Enable sorting.
- `->align(string $alignment)`: Set alignment (`left`, `center`, `right`).
- `->html()`: Treat the value as raw HTML.
- `->view(string $view)`: Use a custom Blade view.
- `->hiddenOn(string $breakpoint)`: Hide the column on specific breakpoints (`sm`, `md`, `lg`).

### 2. `BadgeColumn`

Used for status indicators or boolean values, styled with Flux badges.

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\BadgeColumn;

BadgeColumn::make('Status')
    ->field('status')
    ->colorMap([
        'active' => 'success',
        'pending' => 'warning',
        'inactive' => 'danger',
    ])
```

Colors are mapped to Flux badge colors: `primary`, `success`, `warning`, `danger`, `default`.

### 3. `ActionColumn`

Used for row-level actions, usually rendered as a dropdown.

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions\TableAction;

ActionColumn::make()->actions([
    TableAction::wire('Edit', fn ($row) => "edit('{$row->id}')", icon: 'pencil'),
    TableAction::link('View', fn ($row) => route('users.show', $row), icon: 'eye'),
]);
```

## Advanced Customization

### Value Resolvers

If you need more than just a direct field mapping, use the `value()` method:

```php
Column::make('Full Name')
    ->value(fn ($row) => $row->first_name . ' ' . $row->last_name)
    ->searchableRaw("CONCAT(first_name, ' ', last_name)")
```

### Custom Cell Views

You can provide a custom Blade view to render a cell:

```php
Column::make('Profile')
    ->view('components.tables.user-profile')
```

The view will receive `$row`, `$column`, and `$value` as variables.
