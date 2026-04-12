---
name: livewire-table-kit
description: Use when working on the Unlab Livewire Table Kit package: generator commands, BaseTable, columns, filters, exports, MCP, docs, tests, and release notes.
---

# Livewire Table Kit

## When to use this skill

Use this skill when editing the package source in `livewire-table-kit`. Keep the generator, MCP server, views, docs, changelog, stubs, and tests aligned.

## Workflow

- Inspect `src/Livewire/Components/Tables`, `src/Console/Commands`, `resources/views`, `stubs`, `config`, `tests`, `README.md`, and `CHANGELOG.md`.
- For table feature changes, update `BaseTable`, the column/filter helpers, views, and tests together.
- For generator changes, update `MakeTableCommand`, `stubs/table.stub`, README, changelog, and tests together.
- For MCP changes, update `McpServerCommand`, MCP docs/stubs, README, and service provider registration together.

## Validation

- Run `php -l` on edited PHP files.
- Add or update tests for command and generator behavior.
- Avoid overwriting unrelated user edits.
