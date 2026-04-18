---
name: livewire-table-kit
description: Senior engineer workflow for Unlab Livewire Table Kit package development. Use for source edits, generator logic, AI skills, and documentation.
---

# Livewire Table Kit

## When to use this skill

Use this skill when developing or maintaining the `unlab/livewire-table-kit` package. This includes:
- Core table logic in `src/Livewire/Components/Tables`.
- Generator commands and stubs in `src/Console/Commands` and `stubs/`.
- UI/UX refinements in `resources/views/livewire/components/tables`.
- AI integration via MCP server and skills.
- Documentation updates in `README.md` and `docs/`.

## Technical Architecture

### Core Components
- **BaseTable**: The foundation for all table components. Handles pagination, sorting, searching, and exports.
- **Columns**: Fluent API for column definition (`Column`, `BadgeColumn`, `ActionColumn`).
- **Filters**: Declarative filter API (`Filter::select`, `text`, `date`, `number`).
- **Exports**: Opt-in system for CSV, XLSX (Maatwebsite), and PDF (DomPDF).

### Conventions
- **Flux UI 2**: All views MUST utilize Flux UI components (e.g., `<flux:button>`, `<flux:input>`).
- **PHP 8.4**: Use property promotion, typed properties, and strict types.
- **Blade Namespacing**: Views are namespaced as `livewire-table-kit::`.
- **Styling**: Prefer Tailwind CSS classes and Flux primitives.

## Workflows

### 1. Extending Table Features
When adding features to `BaseTable`:
- Update `BaseTable.php` with new logic/hooks.
- If it's a visual change, update `resources/views/livewire/components/tables/base-table.blade.php`.
- Ensure consistency with `BadgeColumn` and `ActionColumn`.
- Update tests in `tests/Feature`.

### 2. Modifying the Generator
When updating `livewire-table-kit:make-table`:
- Edit `MakeTableCommand.php` (check schema heuristics and column mapping).
- Update `stubs/table.stub` if the generated class structure changes.
- Verify heuristics in `inspectSchemaColumns` for new data types.

### 3. AI & MCP Integration
- **MCP Server**: `McpServerCommand` handles JSON-RPC. Keep it aligned with `docs/mcp-schema.md`.
- **Skill Assets**: When core APIs change, update this `SKILL.md` and `resources/boost/skills/livewire-table-kit/SKILL.md`.
- **Install Commands**: Ensure `InstallMcpCommand` and `InstallSkillCommand` use correct paths.

## Validation & Quality

- **Testing**: Use Pest for all tests. Tables should be tested for query correctness and UI rendering.
- **Static Analysis**: Ensure code adheres to PSR-12 and strict typing.
- **Documentation**: Any API change MUST be reflected in `README.md` and relevant files in `docs/`.

## Reference

- **BaseTable**: `Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable`
- **Views**: `resources/views/livewire/components/tables`
- **Stubs**: `stubs/table.stub`
