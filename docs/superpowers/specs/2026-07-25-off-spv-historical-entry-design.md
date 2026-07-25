# OFF SPV Historical Entry Design

## Scope

Add historical OFF SPV entry for January–July 2026 to the existing HR supervisor detail page. HRD and HR Staff can record missing Saturday dates without duplicating existing leave requests.

## User Flow

1. HR opens **Supervisor**, selects a supervisor, and views year 2026.
2. January–July rows show **Input Data Lampau**.
3. A modal shows:
   - cutoff dates;
   - automatically calculated Saturday count and base quota;
   - editable effective quota;
   - every Saturday in the cutoff as a checkbox;
   - existing OFF SPV dates as checked and disabled;
   - an optional note.
4. HR selects only missing dates and saves.
5. The page refreshes with updated used, expired, annual totals, and audit history.

Historical input only adds or links data. It does not remove an existing OFF SPV entry.

## Data Model

No new usage table or aggregate usage column is required.

- `off_spv_periods` stores the generated monthly period and quota.
- `leave_requests.off_spv_period_id` links every historical usage date to its period.
- A selected date without an existing active OFF SPV request creates one approved `leave_requests` row.
- An existing active OFF SPV request in the cutoff is linked to the period instead of duplicated.
- `off_spv_changes` records period initialization and any effective quota correction.

This keeps historical and current analytics on the same source of truth.

## Backend Rules

- Access is limited to the existing HRD/HR Staff supervisor routes.
- The target user must be a Supervisor.
- Historical import is limited to period labels January–July 2026.
- Every submitted date must:
  - be a Saturday;
  - fall inside the selected period's 26–25 cutoff;
  - belong to the generated list shown by the server.
- Effective quota is clamped to a minimum of zero.
- Saving runs in one database transaction.
- Existing active OFF SPV requests are linked; missing dates are created as approved historical entries.
- Rejected or cancelled requests do not count as usage and do not block a new historical entry.
- Repeated submission remains idempotent and does not create duplicate active requests.

## UI

Reuse the current supervisor details table, modal component, form classes, pills, alerts, and responsive layout. Empty January–July rows become actionable; months outside the import scope keep the existing inactive message.

## Error Handling

Invalid supervisor, month, date, or cross-period input is rejected by server validation. Database changes roll back together if any selected date cannot be saved.

## Tests

- HR can create a historical period and missing approved Saturday entries.
- Existing OFF SPV dates are linked without duplication.
- Non-Saturday and out-of-cutoff dates are rejected.
- Repeated submission is idempotent.
- Non-HR users remain forbidden.
- Annual used and expired analytics include historical entries.

