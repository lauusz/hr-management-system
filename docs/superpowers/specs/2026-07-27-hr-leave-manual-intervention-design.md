# HR Leave Manual Intervention Design

**Date:** 2026-07-27  
**Status:** Approved for planning  
**Scope:** Manual intervention of all leave request types by HRD and HR Staff

## Goal

Allow HRD and HR Staff to manually correct any leave request, regardless of its current status or submission date. Saving an intervention is a final HR decision, so the request becomes `APPROVED` immediately without repeating supervisor or HR approval.

This change must preserve leave-balance correctness, auditability, existing supporting files, and concurrency safety.

## Explicit Constraints

- Do not run, create, or modify PHP migrations.
- Do not change the employee-facing edit endpoint.
- Do not give supervisors or employees the HR override capability.
- Do not delete historical leave requests or their ledger records.
- Existing uncommitted OFF SPV work must be preserved.

## Authorization

The existing HR route group remains the trust boundary:

- `HRD` may intervene.
- `HR STAFF` may intervene.
- All other roles remain forbidden.

The intervention capability applies to every `LeaveType` and every known status:

- `PENDING_SUPERVISOR`
- `PENDING_HR`
- `APPROVED`
- `REJECTED`
- `BATAL`
- Legacy `CANCEL_REQ`

## State Transition

Add one explicit state-machine action:

`HR_OVERRIDE_APPROVE`

Every known source status maps to `APPROVED`. The state machine remains the only authority that writes the target status. A stale request cannot overwrite a concurrent update because the request row is reloaded with `lockForUpdate`.

An HR intervention never sends the request back to a supervisor and never leaves it pending.

## Editable Data

The existing HR edit form and endpoint remain in use. HR may correct:

- type;
- start and end dates;
- start and end times;
- reason;
- HR note;
- substitute PIC and phone;
- special-leave category;
- supporting file;
- meal-allowance deduction flag.

Type-specific normalization still applies:

- `OFF_SPV` is one Saturday and has no time range.
- Non-time-based types clear start and end times.
- `IZIN_TENGAH_KERJA` may retain both times.
- `IZIN_TELAT` and `IZIN_PULANG_AWAL` use `start_time`.
- Non-`CUTI_KHUSUS` requests clear the special-leave category.

HR intervention may use old dates. No minimum lead time or active-period restriction blocks a manual HR correction. OFF SPV remains restricted to supervisors and Saturdays, but HR may correct historical OFF SPV records without consuming or requiring active-period quota.

## H-7 Warning

An intervention is never rejected because the new start date is less than seven days away or already in the past.

When the final request is `CUTI` and its new start date is earlier than seven calendar days from the intervention date, the system appends a standardized warning to `notes`:

`[Warning] Perubahan oleh HR dilakukan kurang dari H-7.`

The warning:

- is visible on the employee leave-detail page through the existing notes section;
- does not change the final `APPROVED` status;
- does not automatically set `deduct_um`;
- replaces the previous standardized H-7 warning instead of accumulating duplicates.

The HR audit note remains separate from the warning.

## Audit Trail

Every successful intervention appends a system note containing:

- actor name and role;
- intervention timestamp;
- previous status;
- previous type and final type;
- previous date range and final date range.

The intervention also sets:

- `approved_by` to the HR actor;
- `approved_at` to the intervention time;
- `supervisor_ack_at` only if it is currently null, because the final HR override represents acknowledged final data.

Existing notes and HR notes are preserved unless the HR form explicitly replaces the editable HR note.

## Leave-Balance Reconciliation

Balance reconciliation runs inside the same database transaction as the locked request update.

### Target deduction

The target net leave deduction is:

1. Final type `CUTI` and `deduct_um` is false:
   calculate effective days from the final date range using `LeaveBalanceService`.
2. Final type is unchanged `SAKIT` or `IZIN` and the request has a historical explicit leave deduction:
   preserve that explicit amount.
3. All other final non-`CUTI` cases:
   target deduction is zero.
4. If `deduct_um` is true:
   target leave deduction is zero.

This means:

- changing an approved CUTI date or range recalculates the deduction;
- changing non-CUTI to CUTI applies the effective-day deduction;
- changing CUTI to non-CUTI refunds the previous net CUTI deduction;
- reopening a cancelled CUTI reapplies the appropriate deduction;
- reopening a cancelled SAKIT/IZIN preserves its former explicit deduction when its type is unchanged;
- repeated identical intervention does not change the balance twice.

### Ledger behavior

Historical ledger rows are never deleted or rewritten. Reconciliation writes a new idempotent adjustment row for the intervention and changes `users.leave_balance` by only the difference between:

- the request's current net deduction represented by its ledger; and
- the target deduction after intervention.

The ledger helper used by cancellation/refund must recognize intervention adjustments so a later HR cancellation refunds the actual current net deduction, not only the first historical `DEDUCT`.

If the user balance cannot cover an increased target deduction, the entire intervention rolls back and HR receives the existing insufficient-balance error.

Legacy approved CUTI without a DEDUCT ledger uses the existing legacy effective-day fallback as its current deduction baseline. The intervention records a ledger adjustment from that baseline so future operations have an auditable value.

## Supporting Files

A replacement supporting file is stored before the database mutation. The old file is deleted only after the database update succeeds. If validation, reconciliation, or transaction work fails, the newly uploaded file is removed and the old file remains available.

## Error Handling

- Authorization failure returns the existing HR access error.
- Invalid type-specific input returns validation errors with the submitted form data.
- Insufficient balance rolls back the request, status, ledger, and balance together.
- A stale/concurrent status or data change is resolved against the locked database row.
- OFF SPV actor/type/date validation returns a user-facing error without partial changes.

## User Interface

The existing HR leave-detail edit modal is reused.

Changes:

- Show the edit action for every status.
- Label the primary action `Simpan & Setujui`.
- Show a short notice that saving is a final HR intervention and the result will be `APPROVED`.
- Employee pages require no new component; they already display `notes`, `notes_hrd`, status, and approver.

No new page, route group, JavaScript library, or database table is introduced.

## Tests

Feature tests must first fail against the current implementation and then cover:

1. HRD edits an old approved CUTI and it remains `APPROVED`.
2. HR Staff has the same intervention capability.
3. Employee and supervisor cannot invoke the HR endpoint.
4. Pending, rejected, cancelled, and legacy cancel-request records become `APPROVED`.
5. Editing a date less than H-7 succeeds and produces one visible warning.
6. Editing a past date succeeds.
7. Approved CUTI date/range changes reconcile only the deduction delta.
8. Cancelled CUTI with a historical refund is correctly deducted when reopened.
9. CUTI changed to non-CUTI refunds its current net deduction.
10. Non-CUTI changed to CUTI deducts the calculated effective days.
11. Existing explicit SAKIT/IZIN deduction is preserved when its type is unchanged.
12. Repeating the same intervention is balance-idempotent.
13. Insufficient balance rolls back request, ledger, and balance.
14. A later cancellation refunds the post-intervention net deduction.
15. Every leave type can be corrected through the HR intervention path.
16. OFF SPV remains supervisor-only and Saturday-only while historical dates remain editable by HR.
17. Replacing a supporting file does not lose the old file when the transaction fails.

Run the focused HR leave, balance, workflow, state-machine, and OFF SPV tests. No migration command may be used during verification.

## Out of Scope

- Employee or supervisor override permissions.
- Outbound email, push, or database notifications.
- Migration or production-schema normalization.
- Ledger backfill for unrelated historical requests.
- Redesigning the leave-detail pages.
