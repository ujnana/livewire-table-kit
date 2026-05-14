# What's changed

## Fixed
- Hardened the packaged `SKILL.md` YAML front matter by quoting `name` and `description` values.
- Prevented install-time parsing failures in stricter AI skill loaders after `livewire-table-kit:install-skill`.

## Tests
- Added coverage to ensure the installed skill file keeps the expected front matter format in both project-local and Codex skill targets.

## Notes
- This is a patch release focused on AI skill installation compatibility.
- No runtime table features or MCP APIs were changed in this release.
