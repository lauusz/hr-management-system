<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Exports\LeaveMasterExport;
use App\Models\LeaveRequest;
use App\Models\Pt;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class HrLeaveController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $leaveBalanceService,
        protected ImageCompressor $imageCompressor,
    )
    {
    }

    /**
     * Menampilkan daftar pengajuan dengan status PENDING_HR.
     * HR inbox hanya menangani final approval.
     *
     * Filter options:
     * - submitted_today: pengajuan yang dibuat hari ini (created_at = today)
     * - period_today: pengajuan yang periodenya mencakup hari ini (start_date <= today AND end_date >= today)
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $today = Carbon::today();

        $leaves = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division',
                'user.position',
                'user.profile.pt'
            ])
            ->whereIn('status', [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR]);

        // Filter: Pengajuan yang dilakukan hari ini (created_at = today)
        if ($request->boolean('submitted_today')) {
            $leaves->whereDate('created_at', $today);
        }

        // Filter: Periode izin yang diajukan mencakup hari ini (start_date <= today AND end_date >= today)
        if ($request->boolean('period_today')) {
            $leaves->whereDate('start_date', '<=', $today)
                   ->whereDate('end_date', '>=', $today);
        }

        $leaves = $leaves->orderByDesc('created_at')->paginate(100);

        return view('hr.leave_requests.index', [
            'leaves' => $leaves,
            'submittedToday' => $request->boolean('submitted_today'),
            'periodToday' => $request->boolean('period_today'),
        ]);
    }

    /**
     * Halaman Master / Riwayat Pengajuan (Semua Data)
     */
    public function master(Request $request)
    {
        $this->authorizeAccess();

        // Base Query
        $query = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division',
                'user.position',
                'user.profile.pt',
                'approver'
            ])
            ->orderByDesc('created_at');

        // --- 1. Filter Status ---
        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'BATAL',
            'CANCEL_REQ'
        ];

        $status = $request->query('status');
        if ($status && in_array($status, $statusOptions, true)) {
            $query->where('status', $status);
        }

        // --- 2. Filter Tipe Cuti ---
        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        }

        // --- 3. Filter Range Tanggal ---
        $submittedRange = trim((string) $request->query('submitted_range'));
        if ($submittedRange !== '') {
            try {
                $parts = preg_split('/\s+(to|sampai)\s+/i', $submittedRange);
                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = (clone $from)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                } elseif (count($parts) >= 2) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = Carbon::parse(trim($parts[1]))->endOfDay();
                    if ($from->gt($to)) { $temp = $from; $from = $to; $to = $temp; }
                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // --- 4. Filter Periode Izin (start_date - end_date) ---
        $periodRange = trim((string) $request->query('period_range'));
        if ($periodRange !== '') {
            try {
                $parts = preg_split('/\s+(to|sampai)\s+/i', $periodRange);
                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->toDateString();
                    $to = $from;
                } else {
                    $fromDate = Carbon::parse(trim($parts[0]))->startOfDay();
                    $toDate = Carbon::parse(trim($parts[1]))->endOfDay();
                    if ($fromDate->gt($toDate)) {
                        $temp = $fromDate;
                        $fromDate = $toDate;
                        $toDate = $temp;
                    }
                    $from = $fromDate->toDateString();
                    $to = $toDate->toDateString();
                }

                // Ambil pengajuan yang periodenya overlap dengan rentang filter.
                $query->whereDate('start_date', '<=', $to)
                    ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$from]);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // --- 5. Filter PT ---
        $ptId = $request->query('pt_id');
        if ($ptId) {
            $query->whereHas('user.profile', function (Builder $q) use ($ptId) {
                $q->where('pt_id', $ptId);
            });
        }

        // --- 6. Search ---
        $q = $request->query('q');
        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(100)->appends([
            'status'          => $status,
            'type'            => $typeFilter,
            'submitted_range' => $submittedRange,
            'period_range'    => $periodRange,
            'pt_id'           => $ptId,
            'q'               => $q,
        ]);

        $pts = Pt::orderBy('name', 'asc')->get();

        return view('hr.leave_requests.master', [
            'items'          => $items,
            'status'         => $status,
            'statusOptions'  => $statusOptions,
            'typeFilter'     => $typeFilter,
            'typeOptions'    => LeaveType::cases(),
            'submittedRange' => $submittedRange,
            'periodRange'    => $periodRange,
            'pt_id'          => $ptId,
            'q'              => $q,
            'pts'            => $pts,
        ]);
    }

    /**
     * Export master leave ke Excel
     */
    public function exportMaster(Request $request)
    {
        $this->authorizeAccess();

        $filters = [
            'status'          => $request->query('status'),
            'type'            => $request->query('type'),
            'submitted_range' => $request->query('submitted_range'),
            'period_range'    => $request->query('period_range'),
            'pt_id'           => $request->query('pt_id'),
            'q'               => $request->query('q'),
        ];

        // Build filename with filter info
        $parts = ['data_izin_cuti'];
        if (!empty($filters['status'])) {
            $parts[] = 'status_' . $filters['status'];
        }
        if (!empty($filters['type'])) {
            $parts[] = 'type_' . $filters['type'];
        }
        if (!empty($filters['submitted_range'])) {
            $parts[] = 'tgl_' . str_replace([' ', 'to', 'sampai'], '_', $filters['submitted_range']);
        }
        if (!empty($filters['period_range'])) {
            $parts[] = 'period_' . str_replace([' ', 'to', 'sampai'], '_', $filters['period_range']);
        }
        if (!empty($filters['pt_id'])) {
            $pt = Pt::find($filters['pt_id']);
            $parts[] = 'pt_' . ($pt ? preg_replace('/[^a-zA-Z0-9]/', '_', $pt->name) : $filters['pt_id']);
        }
        if (!empty($filters['q'])) {
            $parts[] = 'q_' . preg_replace('/[^a-zA-Z0-9]/', '_', $filters['q']);
        }
        $parts[] = now()->format('Ymd_His');

        $filename = implode('_', $parts) . '.xlsx';

        return Excel::download(new LeaveMasterExport($filters), $filename);
    }

    public function createManual()
    {
        $this->authorizeAccess();

        $employees = User::query()
            ->with(['position', 'division'])
            ->active()
            ->orderBy('name')
            ->get();

        $specialLeaveList = [
            ['id' => 'NIKAH_KARYAWAN', 'label' => 'Menikah', 'days' => 4],
            ['id' => 'ISTRI_MELAHIRKAN', 'label' => 'Istri Melahirkan', 'days' => 2],
            ['id' => 'ISTRI_KEGUGURAN', 'label' => 'Istri Keguguran', 'days' => 2],
            ['id' => 'KHITANAN_ANAK', 'label' => 'Khitanan Anak', 'days' => 2],
            ['id' => 'PEMBAPTISAN_ANAK', 'label' => 'Pembaptisan Anak', 'days' => 2],
            ['id' => 'NIKAH_ANAK', 'label' => 'Pernikahan Anak', 'days' => 2],
            ['id' => 'DEATH_EXTENDED', 'label' => 'Kematian (Adik/Kakak/Ipar)', 'days' => 2],
            ['id' => 'DEATH_CORE', 'label' => 'Kematian Inti (Ortu/Mertua/Menantu/Istri/Suami/Anak)', 'days' => 2],
            ['id' => 'DEATH_HOUSE', 'label' => 'Kematian Anggota Rumah', 'days' => 1],
            ['id' => 'HAJI', 'label' => 'Ibadah Haji (1x)', 'days' => 40],
            ['id' => 'UMROH', 'label' => 'Ibadah Umroh (1x)', 'days' => 14],
        ];

        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Supervisor',
            LeaveRequest::PENDING_HR => 'Menunggu HRD',
            LeaveRequest::STATUS_APPROVED => 'Disetujui',
            LeaveRequest::STATUS_REJECTED => 'Ditolak',
            'BATAL' => 'Dibatalkan',
        ];

        return view('hr.leave_requests.create_manual', [
            'employees' => $employees,
            'typeOptions' => LeaveType::cases(),
            'specialLeaveList' => $specialLeaveList,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function storeManual(Request $request)
    {
        $this->authorizeAccess();

        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'BATAL',
        ];

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'submitted_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in($statusOptions)],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string'],
            'notes_hrd' => ['nullable', 'string'],
            'substitute_pic' => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
            'special_leave_detail' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
        ]);

        $employee = User::query()->findOrFail($validated['user_id']);
        $status = $validated['status'] ?? $this->defaultManualStatusForUser($employee);
        $submittedAt = !empty($validated['submitted_at'])
            ? Carbon::parse($validated['submitted_at'])->startOfDay()
            : now();

        $type = $validated['type'];
        $isTimeBased = in_array($type, [
            LeaveType::IZIN_TELAT->value,
            LeaveType::IZIN_TENGAH_KERJA->value,
            LeaveType::IZIN_PULANG_AWAL->value,
            LeaveType::IZIN->value,
        ], true);

        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $fullPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $photoBasename = basename($fullPath);
        }

        $approvedBy = null;
        $approvedAt = null;
        if (in_array($status, [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED, 'BATAL'], true)) {
            $approvedBy = Auth::id();
            $approvedAt = $submittedAt->copy();
        }

        DB::transaction(function () use ($validated, $employee, $status, $submittedAt, $type, $isTimeBased, $photoBasename, $approvedBy, $approvedAt) {
            $leave = new LeaveRequest([
                'user_id' => $employee->id,
                'type' => $type,
                'special_leave_category' => $type === LeaveType::CUTI_KHUSUS->value
                    ? ($validated['special_leave_detail'] ?? null)
                    : null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $isTimeBased ? ($validated['start_time'] ?? null) : null,
                'end_time' => $type === LeaveType::IZIN_TENGAH_KERJA->value ? ($validated['end_time'] ?? null) : null,
                'reason' => $validated['reason'] ?? null,
                'photo' => $photoBasename,
                'status' => $status,
                'notes' => null,
                'notes_hrd' => $validated['notes_hrd'] ?? ('Input manual oleh ' . Auth::user()->name),
                'substitute_pic' => $validated['substitute_pic'] ?? null,
                'substitute_phone' => $validated['substitute_phone'] ?? null,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'supervisor_ack_at' => $status !== LeaveRequest::PENDING_SUPERVISOR ? $submittedAt->copy() : null,
            ]);

            $leave->created_at = $submittedAt->copy();
            $leave->updated_at = $submittedAt->copy();
            $leave->save();
        });

        return redirect()->route('hr.leave.master')->with('success', 'Data izin/cuti manual berhasil disimpan.');
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        // Load relasi yang diperlukan
        $leave->load(['user.profile.pt', 'user.division', 'user.position', 'approver']);

        $me = auth()->user();

        // [LOGIC TOMBOL APPROVE] Gunakan rule yang sama dengan endpoint approve/reject
        $canApprove = $this->canHrActOnLeave($me, $leave);

        // Check if current user is HR staff (for edit permissions)
        $isHrStaff = $me->isHR();

        // [DINAMIS] Cek apakah user ini adalah atasan langsung (supervisor/manager) dari pemohon
        $isDirectApprover = ((int) $leave->user->direct_supervisor_id === (int) $me->id)
            || ((int) $leave->user->manager_id === (int) $me->id);
        $canApproveAsSupervisor = $isDirectApprover && ($leave->status === LeaveRequest::PENDING_SUPERVISOR);

        return view('hr.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
            'isHrStaff' => $isHrStaff,
            'isDirectApprover' => $isDirectApprover,
            'canApproveAsSupervisor' => $canApproveAsSupervisor,
        ]);
    }

    /**
     * Update leave request by HRD / HR Staff.
     * HR can edit any status (including APPROVED). If status is APPROVED and type is CUTI,
     * the old balance is refunded and re-deducted with the new data.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        $user = auth()->user();
        $userRole = $this->normalizeRole($user->role);
        $isHrStaff = in_array($userRole, ['HRD', 'HR STAFF', 'HR MANAGER'], true);

        // Status options
        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'BATAL',
        ];

        $rules = [
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['nullable', 'string', 'max:5000'],
            'notes_hrd'  => ['nullable', 'string', 'max:1000'],
            'substitute_pic'   => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
            'special_leave_detail' => ['nullable', 'string'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'deduct_um_edit' => ['nullable', 'in:1'],
        ];

        // HRD/HR_STAFF can also update status
        if ($isHrStaff) {
            $rules['status'] = ['nullable', Rule::in($statusOptions)];
        }

        $validated = $request->validate($rules);

        $type = $validated['type'];

        // Handle Cuti Khusus category
        $specialLeaveCategory = null;
        if ($type === LeaveType::CUTI_KHUSUS->value) {
            $specialLeaveCategory = $validated['special_leave_detail'] ?? null;
        }

        // Determine if time-based
        $isTimeBased = in_array($type, [
            LeaveType::IZIN_TELAT->value,
            LeaveType::IZIN_TENGAH_KERJA->value,
            LeaveType::IZIN_PULANG_AWAL->value,
            LeaveType::IZIN->value,
        ], true);

        // Build notes
        $currentNotes = $leave->notes;
        $systemNote = "[System] Diedit oleh HR (" . auth()->user()->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        $updateData = [
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'start_time' => $isTimeBased ? ($validated['start_time'] ?? null) : null,
            'end_time'   => ($type === LeaveType::IZIN_TENGAH_KERJA->value) ? ($validated['end_time'] ?? null) : null,
            'reason'     => $validated['reason'] ?? $leave->reason,
            'notes'      => $newNotes,
            'notes_hrd'  => array_key_exists('notes_hrd', $validated) ? $validated['notes_hrd'] : $leave->notes_hrd,
            'substitute_pic'   => $validated['substitute_pic'] ?? $leave->substitute_pic,
            'substitute_phone' => $validated['substitute_phone'] ?? $leave->substitute_phone,
            'special_leave_category' => $specialLeaveCategory,
            'deduct_um'  => $request->filled('deduct_um_edit'),
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($leave->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leave->photo);
            }
            $fullPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $updateData['photo'] = basename($fullPath);
        }

        // Handle status change by HRD/HR_STAFF
        $newStatus = $validated['status'] ?? $leave->status;
        $oldStatus = $leave->status;
        $leaveTypeValue = $leave->type instanceof LeaveType ? $leave->type->value : $leave->type;
        $isCutiType = $leaveTypeValue === LeaveType::CUTI->value;

        // Only process balance if type is CUTI
        if ($isCutiType && $isHrStaff) {
            // Case 1: Was APPROVED, now changed to something else (REJECTED/BATAL/PENDING) -> REFUND
            if ($oldStatus === LeaveRequest::STATUS_APPROVED && $newStatus !== LeaveRequest::STATUS_APPROVED) {
                $this->leaveBalanceService->refundLeaveBalanceForLeave($leave);
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
            }
            // Case 2: Was NOT APPROVED, now changed to APPROVED -> DEDUCT
            elseif ($oldStatus !== LeaveRequest::STATUS_APPROVED && $newStatus === LeaveRequest::STATUS_APPROVED) {
                $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);
                $updateData['approved_by'] = auth()->id();
                $updateData['approved_at'] = now();
            }
            // Case 3: Was APPROVED and still APPROVED (editing data) -> refund old, deduct new
            elseif ($oldStatus === LeaveRequest::STATUS_APPROVED && $newStatus === LeaveRequest::STATUS_APPROVED) {
                $this->leaveBalanceService->refundLeaveBalanceForLeave($leave);
                $leave->fill($updateData);
                $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);
                $updateData['approved_by'] = auth()->id();
                $updateData['approved_at'] = now();
            }
        }

        // Apply status change if provided by HRD/HR_STAFF
        if ($isHrStaff && isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        $leave->update($updateData);

        return redirect()->route('hr.leave.show', $leave->id)->with('success', 'Data pengajuan berhasil diperbarui.');
    }

    /**
     * [UPDATE] APPROVE DENGAN LOGIKA HARI KERJA (5 HARI vs 6 HARI)
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        // 1. Validasi
        $request->validate([
            'notes_hrd'    => 'nullable|string|max:1000',
            'deduct_amount' => 'nullable|in:1,0.5', // Radio: 1=full, 0.5=half day (CUTI/CUTI_KHUSUS/DINAS_LUAR)
            'deduct_leave_sakit' => 'nullable|in:1', // Checkbox: potong cuti untuk SAKIT
            'deduct_amount_sakit' => 'nullable|in:1,0.5', // Radio: full/0.5 untuk SAKIT
            'deduct_leave_izin' => 'nullable|in:1', // Checkbox: potong cuti untuk IZIN
            'deduct_amount_izin' => 'nullable|in:1,0.5', // Radio: full/0.5 untuk IZIN
            'deduct_um' => 'nullable|in:1', // Checkbox: potong UM
        ]);

        // Pastikan status valid
        $allowedStatus = [LeaveRequest::PENDING_HR];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk disetujui.');

        abort_unless($this->canHrActOnLeave(auth()->user(), $leave), 403, 'Anda tidak memiliki izin untuk menyetujui pengajuan ini.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Etika Profesi: Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        }

        $actor = auth()->user();

        // PENDING_HR → HRD/HR Staff final approve
        try {
            DB::transaction(function () use ($request, $leave, $actor) {

                // LOGIKA APPROVE
                $leaveTypeValue = $leave->type instanceof LeaveType ? $leave->type->value : (string) $leave->type;

                // SAKIT: potong cuti jika checkbox dicentang
                $shouldDeductSakit = $request->filled('deduct_leave_sakit');
                $deductAmountSakit = $shouldDeductSakit ? (float) $request->input('deduct_amount_sakit') : null;

                // IZIN: potong cuti jika checkbox dicentang
                $shouldDeductIzin = $request->filled('deduct_leave_izin');
                $deductAmountIzin = $shouldDeductIzin ? (float) $request->input('deduct_amount_izin') : null;

                // SAKIT/IZIN: potong UM jika checkbox dicentang (tanpa cuti)
                $shouldDeductUM = $request->filled('deduct_um');

                // Potong cuti (CUTI: otomatis berdasarkan hari kerja efektif)
                if ($leaveTypeValue === LeaveType::CUTI->value && $leave->status !== LeaveRequest::STATUS_APPROVED) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);
                }

                // Potong cuti (SAKIT)
                if ($shouldDeductSakit && $leave->status !== LeaveRequest::STATUS_APPROVED) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($leave, $deductAmountSakit);
                }

                // Potong cuti (IZIN)
                if ($shouldDeductIzin && $leave->status !== LeaveRequest::STATUS_APPROVED) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($leave, $deductAmountIzin);
                }

                // Audit trail di notes
                $currentNotes = $leave->notes;
                $systemNote = "[System] Disetujui oleh HR (" . $actor->name . ") pada " . now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

                // Update status pengajuan jadi APPROVED
                $leave->update([
                    'status'      => LeaveRequest::STATUS_APPROVED,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                    'notes'       => $newNotes,
                    'notes_hrd'   => $request->notes_hrd,
                    'deduct_um'   => $shouldDeductUM ? true : ($leave->deduct_um ?? false),
                ]);

                // [AUTO DELETE DUPLIKAT] Hapus pengajuan duplikat yang masih pending
                $this->deleteDuplicateLeaveRequests($leave);
            });

            // Pesan sukses
            // Jika ini Cancel Request, pesannya "Pembatalan Disetujui"
            if ($leave->status === 'BATAL') {
                return redirect()->route('hr.leave.index')->with('success', 'Permintaan pembatalan telah disetujui.');
            }

            return redirect()->route('hr.leave.index')->with('success', 'Pengajuan disetujui & Saldo dipotong sesuai hari kerja Role.');

        } catch (\Exception $e) {
            return redirect()->route('hr.leave.index')->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        $request->validate([
            'notes_hrd' => 'required|string|max:1000',
        ]);

        $allowedStatus = [LeaveRequest::PENDING_HR];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk ditolak.');

        abort_unless($this->canHrActOnLeave(auth()->user(), $leave), 403, 'Anda tidak memiliki izin untuk menolak pengajuan ini.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Etika Profesi: Anda tidak dapat menolak pengajuan Anda sendiri.');
        }

        try {
            DB::transaction(function () use ($request, $leave) {
                // [REFUND LOGIC] Kembalikan saldo jika pengajuan ini sudah APPROVED sebelumnya dan tipe CUTI
                $leaveTypeValue = $leave->type instanceof LeaveType ? $leave->type->value : $leave->type;
                $targetValue = LeaveType::CUTI->value;

                if ($leave->status === LeaveRequest::STATUS_APPROVED && $leaveTypeValue === $targetValue) {
                    $this->leaveBalanceService->refundLeaveBalanceForLeave($leave);
                }

                // Audit trail di notes
                $currentNotes = $leave->notes;
                $systemNote = "[System] Ditolak oleh HR (" . auth()->user()->name . ") pada " . now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

                // Update status menjadi REJECTED
                $leave->update([
                    'status'      => LeaveRequest::STATUS_REJECTED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes'       => $newNotes,
                    'notes_hrd'   => $request->notes_hrd,
                ]);
            });

            return redirect()->route('hr.leave.index')->with('success', 'Pengajuan ditolak & saldo (jika ada) dikembalikan.');

        } catch (\Exception $e) {
            return redirect()->route('hr.leave.index')->with('error', $e->getMessage());
        }
    }

    private function authorizeAccess()
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'isHR') || !$user->isHR()) {
            abort(403, 'Akses khusus HRD');
        }
    }

    private function canHrActOnLeave(User $actor, LeaveRequest $leave): bool
    {
        // Self-approval tidak diperbolehkan
        if ($leave->user_id === $actor->id) {
            return false;
        }

        // [DINAMIS] Jika actor adalah atasan langsung (supervisor atau manager) dari pemohon,
        // izinkan act (untuk supervisor-level action dari halaman HR)
        if ((int) $leave->user->direct_supervisor_id === (int) $actor->id) {
            return true;
        }
        if ((int) $leave->user->manager_id === (int) $actor->id) {
            return true;
        }

        $actorRole = $this->normalizeRole($actor->role);
        $applicantRole = $this->normalizeRole($leave->user->role);

        // HRD / HR STAFF hanya boleh final approve/reject request dengan status PENDING_HR
        if ($leave->status !== LeaveRequest::PENDING_HR) {
            return false;
        }

        // MANAGER applicant: HANYA HRD yang boleh approve
        if ($applicantRole === 'MANAGER') {
            return $actorRole === 'HRD';
        }

        // HRD applicant: HANYA HRD yang boleh approve (HR_STAFF tidak boleh)
        if ($applicantRole === 'HRD') {
            return $actorRole === 'HRD';
        }

        // HR_STAFF applicant: HANYA HRD yang boleh approve
        if ($applicantRole === 'HR STAFF') {
            return $actorRole === 'HRD';
        }

        // EMPLOYEE / SUPERVISOR applicant: HRD dan HR_STAFF boleh approve
        return in_array($actorRole, ['HRD', 'HR STAFF'], true);
    }

    private function isHrdMaster(User $user): bool
    {
        $role = $this->normalizeRole($user->role);

        return $role === 'HRD';
    }

    private function isHrStaff(User $user): bool
    {
        return $this->normalizeRole($user->role) === 'HR STAFF';
    }

    private function normalizeRole(mixed $role): string
    {
        if ($role instanceof \App\Enums\UserRole) {
            $role = $role->value;
        }

        return strtoupper(str_replace('_', ' ', trim((string) $role)));
    }

    private function defaultManualStatusForUser(User $user): string
    {
        $applicantRole = $this->normalizeRole($user->role);
        $hasValidSupervisor = !empty($user->direct_supervisor_id);
        $hasValidManager = false;
        if (!empty($user->manager_id)) {
            $hasValidManager = User::where('id', $user->manager_id)->exists();
        }

        switch ($applicantRole) {
            case 'EMPLOYEE':
                return ($hasValidSupervisor || $hasValidManager)
                    ? LeaveRequest::PENDING_SUPERVISOR
                    : LeaveRequest::PENDING_HR;

            case 'SUPERVISOR':
                return $hasValidManager
                    ? LeaveRequest::PENDING_SUPERVISOR
                    : LeaveRequest::PENDING_HR;

            case 'MANAGER':
            case 'HR_STAFF':
                return LeaveRequest::PENDING_HR;

            case 'HRD':
                return $hasValidManager
                    ? LeaveRequest::PENDING_SUPERVISOR
                    : LeaveRequest::PENDING_HR;

            default:
                return LeaveRequest::PENDING_HR;
        }
    }

    /**
     * [HELPER] Hapus pengajuan duplikat yang masih pending di tanggal yang sama
     */
    private function deleteDuplicateLeaveRequests(LeaveRequest $approvedLeave)
    {
        // Cari pengajuan lain dari user yang sama, di tanggal yang overlap, masih pending
        $duplicates = LeaveRequest::where('user_id', $approvedLeave->user_id)
            ->where('id', '!=', $approvedLeave->id)
            ->whereIn('status', [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR])
            ->where(function ($query) use ($approvedLeave) {
                // Cek overlap tanggal: start_date atau end_date berada dalam range
                $query->whereBetween('start_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhereBetween('end_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhere(function ($q) use ($approvedLeave) {
                        // Atau pengajuan duplikat yang range-nya "meliputi" pengajuan approved
                        $q->where('start_date', '<=', $approvedLeave->start_date)
                          ->where('end_date', '>=', $approvedLeave->end_date);
                    });
            })
            ->get();

        // Delete semua duplikat yang ditemukan
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
    }
}