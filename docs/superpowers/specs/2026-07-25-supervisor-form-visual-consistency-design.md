# Supervisor Form Visual Consistency Design

## Goal

Align `/hr/supervisors/create` and `/hr/supervisors/{user}/edit` with the current HR supervisor UI without changing either form's behavior.

## Scope

- Use the standard application header slot with the same supervisor icon, title, and subtitle treatment as the supervisor index and detail pages.
- Use the bordered white HR back button above the form content.
- Replace the legacy bright-blue global palette with page-scoped project tokens: navy primary, neutral surfaces, gray borders, and existing semantic error/badge colors.
- Restyle the form card, identity summary, select fields, secondary action, and primary action using the existing HR form pattern.
- Preserve responsive behavior, with readable cards and full-width actions on mobile.
- Give every inline SVG intrinsic dimensions to prevent refresh-time scaling.

## Explicit Non-Goals

- No field, label, option, validation, route, controller, model, permission, or database changes.
- No new feature or shared component extraction.
- No redesign of supervisor index or detail pages.

## Implementation

Change only:

- `resources/views/hr/supervisors/create.blade.php`
- `resources/views/hr/supervisors/edit.blade.php`

Reuse the visual values already present in the supervisor detail page and HR employee edit form. Keep page-specific class names so this small visual pass does not couple unrelated forms.

## Verification

- Add rendered-DOM regression coverage for both supervisor forms.
- Run `tests/Feature/OffSpvSupervisorManagementTest.php`.
- Compile Blade views with `php artisan view:cache`.
- Run `git diff --check`.
- Confirm no backend or database files are changed.
