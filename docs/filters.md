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

### 3. `Filter::radio(key, label, options)`

Creates a radio group for mutually exclusive filter options.

```php
Filter::radio('status', 'Status', [
    'active' => 'Active',
    'inactive' => 'Inactive',
])->placeholder('All statuses')
```

### 4. `Filter::checkbox(key, label, options)`

Creates a multi-select checkbox filter. In the default table UI, it is rendered inside a dropdown trigger to keep the filter toolbar compact.

```php
Filter::checkbox('status', 'Statuses', [
    'active' => 'Active',
    'pending' => 'Pending',
    'inactive' => 'Inactive',
])
```

By default, checkbox filters apply a `whereIn($key, $selectedValues)` style filter when used against an Eloquent query.

### 5. `Filter::date(key, label)`

Creates a date input for filtering date-based fields.

```php
Filter::date('created_at', 'Registered After')
```

### 6. `Filter::number(key, label, placeholder)`

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

### Display Mode

You can control how option-based filters are rendered in the toolbar:

```php
Filter::radio('status', 'Status', [
    'active' => 'Active',
    'inactive' => 'Inactive',
])->display('dropdown')
```

Supported display modes:

- `inline`: Render the filter directly in the toolbar.
- `dropdown`: Render the filter behind a dropdown trigger.

By default:

- `checkbox` filters use `dropdown`
- other filters use `inline`

### Toolbar Mode

You can also control the filter toolbar at the table level:

```php
protected function filterToolbarMode(): string
{
    return 'dropdown';
}
```

Supported toolbar modes:

- `inline`: Render filters directly in the toolbar.
- `dropdown`: Render all filters inside one `Filters` dropdown trigger.

Use `dropdown` when a table has many filters or wide option lists that would otherwise crowd the toolbar.

### Placeholder

Customize the placeholder text:

```php
Filter::text('search', 'Search')->placeholder('Search everything...')
```

For `select` and `radio` filters, the placeholder is used as the reset option label:

```php
Filter::radio('status', 'Status', [
    'active' => 'Active',
    'inactive' => 'Inactive',
])->placeholder('All statuses')
```
