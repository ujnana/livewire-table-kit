# Table Actions

Table actions allow users to perform operations on individual rows or multiple selected rows.

## Row Actions

Row actions are defined within an `ActionColumn`. They are typically displayed as a dropdown menu at the end of each row.

```php
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;
use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions\TableAction;

public function columns(): array
{
    return [
        // ... other columns
        ActionColumn::make()->actions([
            TableAction::wire('Edit', fn ($row) => "edit('{$row->id}')", icon: 'pencil'),
            TableAction::link('View', fn ($row) => route('users.show', $row), icon: 'eye'),
            TableAction::wire('Delete', fn ($row) => "delete('{$row->id}')", icon: 'trash', color: 'danger'),
        ]),
    ];
}
```

### TableAction Types

- `TableAction::wire(label, method, color, icon)`: Dispatches a Livewire event or calls a component method.
- `TableAction::link(label, url, color, icon)`: Navigates to a specific URL.

### Colors and Icons

- `color`: Can be `default` (zinc) or `danger` (red).
- `icon`: The name of the icon (supports Flux/Heroicons).

## Bulk Actions

Bulk actions allow users to perform an operation on all selected rows.

### Enabling Bulk Actions

To enable bulk selection and actions, override the `supportsBulkDelete()` method:

```php
public function supportsBulkDelete(): bool
{
    return true;
}
```

### Implementing Bulk Delete

When `supportsBulkDelete` is enabled, the component will handle the selection UI and show a "Delete Selected" button. You must implement the `actionBulkDelete` method to perform the actual deletion:

```php
public function actionBulkDelete(array $payload): void
{
    // $payload contains an array of the selected row IDs
    User::whereIn('id', $payload)->delete();
}
```

### Custom Confirmation Message

You can customize the confirmation message by overriding `bulkDeleteConfirmationTitle()`:

```php
protected function bulkDeleteConfirmationTitle(): string
{
    return 'Are you sure you want to permanently delete these users?';
}
```

## Refreshing the Table

After performing an action (like an edit or delete) outside of the table component, you can refresh the table by dispatching the `refreshTable` event to it:

```php
$this->dispatch('refreshTable')->to(UsersTable::class);
```
