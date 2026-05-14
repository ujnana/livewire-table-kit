# What's changed

## Added
- Added `Filter::radio()` for mutually exclusive table filters.
- Added support for rendering radio filter groups in the base table filter toolbar.

## Improved
- Added reset or "all" behavior for radio filters through the existing `placeholder()` API.
- Updated filter documentation to include radio-filter examples and behavior notes.
- Added focused test coverage for the new radio filter helper.

## Notes
- This release extends the filter API without changing existing select or text-filter behavior.
- Existing filter definitions remain backward compatible.
