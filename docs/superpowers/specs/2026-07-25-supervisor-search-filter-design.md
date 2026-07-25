# Supervisor Search and Role Filter Design

## Goal

Add a small server-side filter to `/hr/supervisors` without changing existing supervisor management behavior.

## Behavior

- Search only the `users.name` column using the `q` GET parameter.
- Show Supervisor and Manager as checkboxes using the `roles[]` GET parameter.
- Allow either role or both roles to be selected.
- Treat no selected role as all supported roles.
- Show Reset when a keyword exists or the selected roles differ from the default.
- Preserve the keyword and role selection across pagination.

## UI

Place one filter card between the existing add button and supervisor list. Match the search-card styling already used by `/hr/leave/master`, with stacked controls on small screens and one row on wider screens.

## Scope

Modify only the supervisor index controller, supervisor index Blade view, and supervisor feature test. Do not add live search, JavaScript, routes, database changes, dependencies, or filters for email, position, or division.

## Verification

Test name matching, role filtering, combined filters, reset visibility, and pagination query preservation. Run the complete supervisor feature test and compile all Blade views.
