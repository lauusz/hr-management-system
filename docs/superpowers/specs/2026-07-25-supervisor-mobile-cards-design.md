# Supervisor Mobile Cards Design

## Goal

Make `/hr/supervisors` comfortable to use on mobile while preserving the existing desktop table and visual language used by leave request pages.

## Responsive Structure

- Below `768px`, hide the supervisor table and show one card per supervisor or manager.
- At `768px` and above, show the existing table and hide the mobile cards.
- The page header stacks on mobile and the **Tambah Supervisor** button uses the available width.
- Pagination remains shared below both views.

## Mobile Card

Each card contains the same data and actions as the desktop row:

- avatar, name, and email;
- Supervisor or Manager badge;
- position and division;
- current OFF SPV approved, pending, and remaining values when applicable;
- **Detail**, **Edit**, and **Demote** actions.

Manager cards show no OFF SPV detail action, matching current desktop behavior.

## Interaction and Accessibility

- Reuse the existing demote modal and `data-modal-open` behavior.
- Use text labels with icons for mobile actions so touch targets are clear.
- Keep links and buttons semantically correct.
- Preserve current flash messages, empty state, and pagination.

## Scope

Only `resources/views/hr/supervisors/index.blade.php` and its view test need changes. No controller, route, database, or business-rule changes are required.

## Verification

- A view test confirms both desktop table markup and mobile card markup render.
- Blade compilation succeeds.
- The existing supervisor feature tests remain green.
- A mobile-width browser check confirms cards display without horizontal scrolling.

