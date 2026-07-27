# Reversible OFF SPV History Design

## Goal

Allow HRD and HR Staff to correct historical OFF SPV dates without deleting database records or creating duplicate records.

## Scope

- Existing historical date checkboxes remain checked but are no longer disabled.
- Saving an unchecked existing date changes that `leave_requests` record to `BATAL`.
- Saving a checked date reuses the matching record and changes it to `APPROVED`.
- Existing pending records selected as already used are normalized to `APPROVED`.
- A selected date without an existing record creates one approved historical `leave_requests` record.
- Corrections update `approved_by`, `approved_at`, and normal timestamps with the acting HR user.
- All changes run inside the existing database transaction.

## Database Impact

- No migration, table, column, index, or foreign-key change.
- No `DELETE` operation.
- Existing records retain their IDs and can be reactivated later.
- `off_spv_periods` and `off_spv_changes` keep their current behavior for quota initialization and quota adjustment.

## UI

The historical modal keeps the existing Saturday checklist. Replace the permanent-data note with short guidance that checked dates count as used and unchecked dates are cancelled but retained.

## Explicit Non-Goals

- No arbitrary non-Saturday date input in this change.
- No manager historical-entry access change.
- No new audit table or duplicate correction record.
- No changes to regular employee/supervisor leave-request flows.

## Verification

- Prove an existing approved date renders checked and enabled.
- Prove unchecking changes the same row to `BATAL` without deleting it.
- Prove checking it again restores the same row to `APPROVED` without duplication.
- Prove an existing pending historical date becomes `APPROVED` when checked and saved.
- Run the OFF SPV supervisor feature suite and compile Blade views.
