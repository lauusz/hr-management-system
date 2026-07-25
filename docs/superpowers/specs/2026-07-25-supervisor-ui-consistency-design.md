# Supervisor UI Consistency Design

## Goal

Align `/hr/supervisors` with the existing project UI without adding components, tokens, features, or behavior.

## Existing References

Use the established visual language from:

- `resources/views/layouts/app.blade.php`;
- `resources/views/leave_requests/index.blade.php`;
- `resources/views/hr/leave_requests/index.blade.php`.

## Changes

Only `resources/views/hr/supervisors/index.blade.php` changes.

- Remove the page-local blue V1 palette.
- Use existing global variables such as `--primary-dark`, `--primary`, `--accent`, `--white`, `--gray-50`, `--border`, `--border-light`, `--text-primary`, `--text-secondary`, `--text-muted`, `--success`, `--warning`, and `--error`.
- Inherit Plus Jakarta Sans from the application layout.
- Match existing page patterns for the heading icon, primary button, flash messages, white cards, borders, shadows, hover states, table header, badges, and mobile actions.
- Use navy styling for Supervisor and the existing gold accent for Manager.
- Keep warning styling for Demote.
- Preserve the desktop table, mobile cards, pagination, empty state, routes, modal behavior, data, and text.

## Non-Goals

- No shared component or stylesheet is created.
- No other page is refactored.
- No controller, route, database, JavaScript behavior, or business rule changes.

## Verification

- Existing supervisor feature tests remain green.
- Blade compilation and `git diff --check` pass.
- CSS no longer defines the old local colors `#2563eb` and `#1e40af`.
- Mobile and desktop markup remain present.

