# Changelog

All notable changes to `unlab/livewire-table-kit` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project aims to follow [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- **Radio Filters**: Added `Filter::radio()` as a first-class filter type for mutually exclusive options, alongside the existing select, text, date, and number filters.

### Improved
- **Filter UI Rendering**: Table filter rendering now supports Flux radio groups with a reset option driven by `placeholder()`.
- **Filter Documentation & Coverage**: Documented radio-filter usage and added focused test coverage for the new filter helper.

## [1.3.1] - 2026-05-14

### Fixed
- **AI Skill Front Matter Compatibility**: Quoted the `name` and `description` values in the packaged `SKILL.md` front matter to avoid YAML parsing failures in stricter skill loaders after installation.

### Tests
- **Skill Install Coverage**: Added assertions to verify the installed skill file preserves the hardened YAML front matter format.

## [1.3.0] - 2026-04-18

### Added
- **Comprehensive Documentation**: Added a full documentation suite in the `docs/` folder covering Getting Started, Columns, Filters, Actions, Exports, and MCP.
- **Tailwind CSS v4 Support**: Added documentation for Tailwind v4 integration using the `@source` directive in CSS.
- **Responsive Columns**: Added `Column::hiddenOn($breakpoint)` method to hide specific columns on `sm`, `md`, or `lg` screens.

### Improved
- **Mobile UI & Responsiveness**: 
    - Optimized header layout for mobile portrait (grouped search and export).
    - Refined footer layout with a responsive grid (Per Page left, Showing info center, Pagination right).
    - Fixed pagination button sizes for better mobile fit.
- **Horizontal Scrolling**: Added `whitespace-nowrap` and `sticky` positioning for Bulk Selection (left) and Action columns (right) to maintain context during horizontal scroll.
- **Informative Footer**: Added "Showing X to Y of Z entries" text to the footer for better data context.
- **AI Developer Experience**: Enhanced the package AI Skill (`SKILL.md`) with senior engineer workflows and technical architecture details.

## [1.2.0] - 2026-04-12

### Added

- Added `livewire-table-kit:make-table` and `make:livewire-table` commands to scaffold table components from Eloquent models.
- Added schema-driven column generation with inferred searchable, sortable, and badge-style columns.
- Added namespace-based output path inference and model-name inference from table names.
- Added a Codex skill for the Livewire Table Kit package workflow.

## [1.1.0] - 2026-04-12

### Added

- ✅ **Native AI Skills support via MCP Protocol**
- Added `livewire-table-kit:mcp` command as MCP Server
- Added `livewire-table-kit:install-mcp` interactive command for optional AI Skill installation
- Added configuration file with all default table settings
- Added publish tags: config, views, mcp, stubs, lang
- Added standard Laravel package .gitignore rules
- Livewire table base component with search, filters, sorting, pagination, and row actions.
- Bulk delete support with selectable rows and event-driven confirmation flow.
- Optional export support for CSV, XLSX, and PDF.
- Custom export columns, export filename hooks, and PDF layout hooks.
- Package service provider with view loading and publishable views.
- Package README with installation and usage examples.
- Package license, publish metadata, and baseline package test.

## [1.0.0] - 2026-04-11

### Added

- Initial public release of the reusable Livewire table kit.
- `BaseTable` abstraction for reusable data tables.
- `Column`, `BadgeColumn`, `ActionColumn`, `TableAction`, and `Filter` helpers.
- Package views for the table shell, empty state, action dropdown, badge rendering, pagination, and PDF export.
- CSV, XLSX, and PDF export support.
- Package discovery via Laravel service provider registration.

### Notes

- This release is intended for Laravel 13 and Livewire 4.
- PDF export uses DomPDF.
- Spreadsheet export uses Maatwebsite Excel.
