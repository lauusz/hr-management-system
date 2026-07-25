# Supervisor Leave Master Visual Design

## Goal

Make `/hr/supervisors` visually match `/hr/leave/master` from header through pagination while preserving every existing supervisor feature and data flow.

## Scope

- Move the supervisor title and subtitle into the layout header slot using the same inline section-header pattern as leave master.
- Place the existing “Tambah Supervisor” action in a CTA bar below the header.
- Remove the supervisor page’s `max-width: 1000px` wrapper so desktop content uses the full width supplied by the application layout.
- Match leave master’s alerts, spacing, buttons, table shell, table typography, badges, action buttons, empty state, pagination, shadows, radii, and responsive behavior.
- Keep the existing mobile supervisor cards, but restyle them with the same surfaces, spacing, typography, and action treatment as leave master.

## Explicit Non-Goals

- No statistics row.
- No search, filters, export, or other leave-master functionality.
- No new route, controller behavior, query, dependency, shared component, or database change.
- No changes to supervisor data, OFF SPV calculations, modal behavior, or action permissions.

## Implementation

Change only `resources/views/hr/supervisors/index.blade.php`.

Use supervisor-specific class names while copying the established layout and CSS values from `resources/views/hr/leave_requests/master.blade.php`. This avoids coupling the supervisor page to misleading `lm-*` names and avoids a broader refactor of the working leave-master page.

Desktop uses the application content width without a nested maximum width. Mobile continues to switch from the table to cards below `768px`.

## Verification

- Run `tests/Feature/OffSpvSupervisorManagementTest.php`.
- Compile Blade views with `php artisan view:cache`.
- Run `git diff --check`.
- Confirm no supervisor route, controller, model, or database file changes are introduced by this visual pass.
