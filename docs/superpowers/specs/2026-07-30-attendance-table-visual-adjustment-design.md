# Attendance Table Visual Adjustment

## Scope

Adjust only the `/hr/attendances` view. No controller, database, filter behavior, attendance status logic, or other page changes.

## Visual Changes

1. Replace the green `Lengkap` badge for `CLOSED` attendance with a compact green check icon.
2. Keep an accessible `Lengkap` label for screen readers and a native tooltip for pointer users.
3. Keep `OPEN`, `MISSED_CLOCK_OUT`, `LATE_CLOCK_OUT`, and unknown states as their existing text badges.
4. Keep `Filter` and `Reset` on their existing action row, but align that row to the right on desktop.
5. Preserve the current full-width Filter and Reset layout on mobile.

## Existing Patterns

Reuse the check icon already used by the attendance approval screen and the existing green status colors. Add no dependency or reusable component.

## Verification

- Confirm the `CLOSED` state renders the green check and accessible label.
- Confirm non-closed states retain their text.
- Confirm desktop actions align right and mobile actions remain full width.
- Run the focused attendance view tests or the smallest available rendering check.
