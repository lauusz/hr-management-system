# Responsive Attendance Dashboard Design

Date: 2026-07-22
Status: Awaiting final specification review
Visual target: [mobile-attendance-dashboard-option-1.png](assets/mobile-attendance-dashboard-option-1.png)

## Goal

Simplify the mobile attendance dashboard for non-technical employees. The screen must answer three questions quickly:

1. Have I clocked in today?
2. What action can I take now?
3. Is there an older attendance session that needs attention?

## Scope

This change applies to the employee attendance dashboard at `resources/views/attendance/dashboard.blade.php` on mobile and desktop viewports.

The implementation will not change:

- attendance routes, controller queries, or database fields;
- clock-in or clock-out validation;
- camera and geolocation screens;
- remote attendance / Dinas Luar screens;
- HR attendance screens;

## Visual Direction

Use the selected first mockup as the visual target, adapted to the existing application shell rather than copied pixel-for-pixel.

- Keep the existing application header and mobile hamburger navigation.
- Remove the blue greeting hero at every viewport size.
- Use white or near-white surfaces for normal information.
- Reserve navy blue for the enabled primary action.
- Reserve amber or red for an actual warning or error.
- Avoid gradients, decorative color blocks, nested cards, metrics, and long instructions.
- Keep body text at a readable 14–16px and interactive controls at least 44px high.

## Mobile Information Hierarchy

The content appears in this order:

1. **Previous attendance warning**, only when `$previousIncompleteAttendance` exists.
2. **Current attendance status**, always visible.
3. **Bottom spacing**, large enough that the final content is not covered by the sticky actions.

The existing sections `attendance-hero`, `Ringkasan Hari Ini`, and `Panduan Singkat` will not be shown. Their data remains unchanged and remains available in HR views where applicable.

### Previous Attendance Warning

Show one compact warning surface containing:

- title: `Absensi sebelumnya belum selesai`;
- status label: `Terlewat` or `Berjalan`, based on the existing completion status;
- date;
- clock-in time;
- completion label such as `Belum Clock Out`.

No new detail route or corrective action will be invented. This block is informational because the current backend does not expose a dedicated recovery flow from this dashboard.

### Current Attendance Status

Use a single calm white section with one status message and no duplicate summary.

| State | Main message | Supporting information |
| --- | --- | --- |
| No attendance today | `Belum presensi` | `Silakan clock in saat tiba di lokasi kerja.` |
| Active attendance | `Sedang bekerja` | Show the recorded clock-in time. |
| Completed attendance | `Presensi selesai` | Show clock-in and clock-out times. |
Late, early-leave, and overtime values are not shown in the simplified mobile dashboard. They remain stored and available in the existing desktop/HR views.

## Sticky Actions

Clock In and Clock Out remain visible in a sticky bottom dock on mobile.

- Use `position: fixed` with `bottom: 0` on mobile so the dock is visible from the initial viewport and remains in place while `.main-content` scrolls.
- Keep its z-index below the existing mobile sidebar and modal layers.
- Add safe-area padding with `env(safe-area-inset-bottom)`.
- Add bottom spacing to the content so scrolling can reveal every item above the dock.
- Use a white dock with a subtle top border or shadow.
- Keep the selected mockup's vertical button arrangement: Clock In above Clock Out.

On viewports 768px and wider, the same action surface remains in normal document flow below the status section. It uses the same colors, labels, and state treatment, constrained to the same centered content column rather than spanning the viewport.

Button behavior continues to use the existing `$canClockIn` and `$canClockOut` values:

| State | Clock In | Clock Out |
| --- | --- | --- |
| No active attendance | Enabled | Disabled |
| Active attendance | Completed/disabled | Enabled |
| Attendance completed | Completed/disabled | Completed/disabled |

Disabled actions remain non-interactive with `aria-disabled="true"` and `tabindex="-1"`. Enabled actions retain their existing routes.

## Implementation Shape

The smallest implementation is preferred:

- edit only `resources/views/attendance/dashboard.blade.php` if possible;
- reuse the current Blade state variables and route logic;
- reuse existing CSS variables from `layouts/app.blade.php`;
- keep all responsive rules inside the page's existing CSS;
- do not introduce a component, dependency, or JavaScript module for a single screen.

No layout-file change is planned. A second file may be touched only if browser verification proves the page-level fixed dock cannot coexist with the current mobile shell.

## Accessibility and Interaction

- Maintain visible focus styles for enabled links.
- Do not communicate warning or disabled state by color alone; keep text labels.
- Preserve semantic links for enabled actions.
- Ensure the dock does not cover content at 320px, 390px, and 430px widths.
- Keep the desktop content centered with a readable maximum width and no new side panels or metrics.
- Respect the existing mobile sidebar and modal z-index hierarchy.
- Do not add animation beyond existing hover/press feedback.

## Error Handling

The dashboard performs no mutation, so it does not need new error handling. Clock-in and clock-out errors continue to be handled by the existing capture pages and global toast system.

Missing or partial attendance data must fall back to `-` or the existing neutral message rather than creating a new error state.

## Verification

Implementation is complete only when all checks pass:

1. Existing attendance feature tests remain green.
2. Mobile screenshots are checked at 390×844 for these states:
   - no attendance;
   - active clock-in;
   - completed attendance;
   - previous incomplete attendance.
3. Both actions remain visible while the content scrolls.
4. The last content item can scroll fully above the sticky dock.
5. Clock In and Clock Out enablement matches the existing backend state.
6. Desktop at 1024px or wider has no visual regression.
7. No horizontal overflow appears at 320px width.
8. Desktop screenshots at 1440×1000 show the same simplified hierarchy and the action panel in normal flow.

## Explicitly Deferred

- Refactoring the duplicated clock-in and clock-out camera code.
- Redesigning remote attendance / Dinas Luar.
- Redesigning HR or supervisor attendance views.
- Moving all inline CSS into a new design system.
- Adding a previous-attendance correction workflow.

These items should be handled only when their respective screens enter scope.
