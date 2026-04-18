# Table Filters

Table filters provide a way to narrow down the dataset based on user input.

## Basic Usage

Filters are defined by overriding the `filters()` method in your table component.

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Filters\Filter;

public function filters(): array
{
    return [
        Filter::select('role', 'User Role', [
            'admin' => 'Admin',
            'editor' => 'Editor',
            'user' => 'User',
        ]),
    ];
}
```

## Available Filter Types

### 1. `Filter::select(key, label, options)`

Creates a dropdown select input.

- `key`: The database field or filter identifier.
- `label`: The display label for the filter.
- `options`: An associative array of `value => label`.

### 2. `Filter::text(key, label, placeholder)`

Creates a simple text input.

```php
Filter::text('search_name', 'Name search', 'Enter a name...')
```

### 3. `Filter::date(key, label)`

Creates a date input for filtering date-based fields.

```php
Filter::date('created_at', 'Registered After')
```

### 4. `Filter::number(key, label, placeholder)`

Creates a numeric input for filtering numeric fields.

```php
Filter::number('min_age', 'Minimum Age')
```

## Advanced Customization

### Custom Query logic

By default, filters use a simple `where($key, $value)` comparison. For more complex logic, use the `query()` method:

```php
Filter::select('status', 'Status', [
    'active' => 'Active',
    'archived' => 'Archived',
])
->query(function ($query, $value) {
    if ($value === 'archived') {
        $query->onlyTrashed();
    } else {
        $query->where('status', $value);
    }
})
```

### Default Values

You can set a default value for a filter:

```php
Filter::select('status', 'Status', [...])
    ->default('active')
```

### Placeholder

Customize the placeholder text:

```php
Filter::text('search', 'Search')->placeholder('Search everything...')
```
