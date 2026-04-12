# Livewire Table Kit MCP Schema

This package exposes a small MCP server for scaffold generation and schema discovery.

## Tools

### `livewire_table_generate`

Generate a Livewire table component skeleton for an Eloquent model.

Input:

- `model` (required): full class name of the Eloquent model
- `name` (optional): table class name, defaults to `{Model}Table`
- `columns` (optional): list of column names to include in the scaffold

Output:

- PHP class code extending `Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable`

### `livewire_table_schema`

Return the package schema documentation in plain text.

## Generated table shape

The generated table class includes:

- `query()` returning `Model::query()`
- `columns()` returning an array for column definitions
- a namespace under `App\Livewire\Tables`

## Example

```json
{
  "model": "App\\Models\\User",
  "name": "UsersTable"
}
```
