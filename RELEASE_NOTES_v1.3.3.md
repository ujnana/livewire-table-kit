# What's changed

## Added
- Added `filterToolbarMode()` to let each table choose between inline filters and a single dropdown-based `Filters` trigger.

## Improved
- Added `display('inline'|'dropdown')` for option-based filters so presentation can be tuned without changing filter query logic.
- Defaulted checkbox filters to dropdown presentation for more compact toolbars on wide or filter-heavy tables.
- Updated package docs and packaged skill guidance to cover toolbar modes, display modes, and filter behavior.

## Notes
- This release focuses on filter-toolbar ergonomics and package reusability.
- Existing filter query behavior remains compatible, while toolbar presentation is now more configurable.
