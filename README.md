# American Civic Bestiary

A premium-ready WordPress plugin for running the American Civic Bestiary assessment, collecting profile data, and presenting polished civic-animal reports that inherit from the active theme.

## What changed in this upgraded build

- Refactored the frontend into theme-overrideable template files under `templates/`
- Added a bundled 16-icon animal set extracted into lightweight WebP assets
- Added richer admin controls for assessment copy, report copy, display toggles, theme integration, colors, panel radius, shadow depth, and custom CSS
- Improved frontend presentation for reports and assessment flow
- Added profile CSV export
- Registered styles/scripts cleanly and only enqueue frontend styles when the shortcodes render
- Added `load_plugin_textdomain()` and automatic schema/version upgrade checks
- Fixed the consent-field markup issue in the original public form
- Added theme/template hooks and filterable icon URLs for easier premium customization

## Shortcodes

- `[american_civic_bestiary]`
- `[civic_bestiary]`
- `[acb_dashboard]`

## Theme overrides

Copy any file from `templates/` into your theme at:

`your-theme/american-civic-bestiary/`

Example:

`your-theme/american-civic-bestiary/dashboard.php`

## Useful filters

- `acb_template_candidates`
- `acb_template_args`
- `acb_wrapper_classes`
- `acb_animal_icon_url`

## Bundled icons

Bundled icons live in:

`assets/images/animals/`

## Notes

This plugin intentionally keeps styling token-driven so it can follow a broad variety of WordPress themes while still supporting a premium branded layer through the settings page.
