# American Civic Bestiary

A premium-ready WordPress plugin for running the American Civic Bestiary assessment, collecting profile data, and presenting polished civic-animal reports that inherit from the active theme.

## Current product flow

- Anonymous visitors receive a secure `acb_profile_token` cookie and a matching profile record so quiz progress survives reloads without requiring login.
- The assessment shortcode shows quiz questions until the configured minimum answer threshold is reached.
- Once the minimum threshold is reached, the report is shown clearly instead of making the next batch of questions look like a broken retake.
- Additional unanswered questions are presented as an intentional refinement flow. Site owners can show refinement automatically or place it behind a button/details control.
- Already-answered questions are excluded from normal batches and skipped on submission by default. Admins can explicitly enable retake/reset-like behavior with the retake setting.
- If a visitor later logs in with an anonymous cookie profile, the plugin merges unanswered/unique anonymous answers into the user profile, associates the cookie with the user profile, and avoids presenting duplicate profiles for the same person.

## Shortcodes

- `[american_civic_bestiary]` — primary assessment/report flow.
- `[civic_bestiary]` — backward-compatible alias of the primary assessment/report flow.
- `[acb_dashboard]` — standalone dashboard/report display for the current profile.

## Admin controls

The Settings screen includes controls for:

- Minimum answers and questions per batch.
- Report position relative to refinement questions.
- Refinement display mode.
- Retake handling.
- Report layout mode: auto/responsive, single column, two column, or three column.
- Maximum report width for narrow or wide WordPress theme containers.
- Compact mode.
- Animal icons, primary/secondary cards, dimension bars, house alignment, capture-literacy overlay, and top match count.
- Custom intro/outro copy and CTA label/URL.
- Theme-friendly colors, radius, panel shadow, and custom CSS.

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

## Manual QA checklist

1. Visit a page with `[american_civic_bestiary]` while logged out and confirm the initial quiz appears with unlock progress.
2. Submit the minimum number of answers and confirm the report appears on refresh rather than the same first questions.
3. Confirm refinement questions are either behind the configured button/details control or shown automatically, and that they do not include already answered questions.
4. Refresh the page as the same anonymous visitor and confirm the report/profile persists.
5. Log in with the same browser/cookie and confirm the anonymous profile is associated/merged with the WordPress user profile.
6. Revisit in a new browser session as the same logged-in user and confirm the user profile remains stable.
7. Change layout settings in Civic Bestiary → Settings and confirm the report adapts in narrow and wide content containers.
8. Confirm `[american_civic_bestiary]`, `[civic_bestiary]`, and `[acb_dashboard]` all render without PHP warnings.

## Notes

This plugin intentionally keeps styling token-driven and scoped to `.acb-shell` so it can follow a broad variety of WordPress themes while still supporting a premium branded layer through the settings page.
