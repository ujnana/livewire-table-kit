# AI Skills & MCP Server

Livewire Table Kit includes built-in AI capabilities to help developers scaffold components and explore the package's features.

## MCP Server

The package provides a Model Context Protocol (MCP) server that AI agents can use to understand the package schema and generate table components.

### Installation

To enable the MCP server in your Laravel project, run:

```bash
php artisan livewire-table-kit:install-mcp
```

This creates a `.mcp/livewire-table-kit.json` file in your project root, which can be used by AI tools.

### Available Tools

The MCP server exposes the following tools:

- `livewire_table_generate`: Generates a Livewire table component based on an Eloquent model.
- `livewire_table_schema`: Returns the full package schema documentation.

## Project AI Skills

You can also install project-local AI skills that can be used by AI-powered IDEs and agents (like Codex).

```bash
php artisan livewire-table-kit:install-skill
```

This command installs skill files to:

- `~/.codex/skills/livewire-table-kit` (Global)
- `.ai/skills/livewire-table-kit` (Project-local)

## How it works

The MCP server runs via `php artisan livewire-table-kit:mcp` and communicates over STDIN/STDOUT using JSON-RPC. This allows AI agents to directly interact with your codebase to help you build tables faster.
