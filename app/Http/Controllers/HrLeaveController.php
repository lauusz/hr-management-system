<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
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
use Illuminate\Validation\Rule;

class HrLeaveController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $leaveBalanceService,
        protected ImageCompressor $imageCompressor,
    )
    {
    }

    /**
     * Menampilkan daftar pengajuan yang statusnya:
     * 1. PENDING_HR (Tugas Utama HR)
     * 2. PENDING_SUPERVISOR tapi user-nya bawahan saya (Saya merangkap SPV)
     * 3. PENDING_SUPERVISOR tapi user-nya TIDAK PUNYA SPV (Orphan/Bypass)
     */
    public function index()
    {
        $this->authorizeAccess();

        $userId = Auth::id();

        $leaves = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division', 
                'user.position', 
                'user.profile.pt' 
            ])
            ->where(function ($query) use ($userId) {
                // 1. Ambil yang statusnya PENDING_HR (Tugas Global HR Manager)
                $query->where('status', LeaveRequest::PENDING_HR)
                
                // 2. ATAU Ambil yang statusnya PENDING_SUPERVISOR...
                ->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('status', LeaveRequest::PENDING_SUPERVISOR)
                        ->where(function ($q) use ($userId) {
                            // A. ...Dimana Saya adalah Supervisor-nya
                            $q->whereHas('user', function ($u) use ($userId) {
                                $u->where('direct_supervisor_id', $userId);
                            })
                            // B. ...ATAU User tersebut TIDAK PUNYA Supervisor (Orphan Data)
                            ->orWhereHas('user', function ($u) {
                                $u->whereNull('direct_supervisor_id');
                            });
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(100);

        return view('hr.leave_requests.index', compact('leaves'));
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
            ['id' => 'DEATH_CORE', 'label' => 'Kematian Inti (Ortu/Mertua/Istri/Anak)', 'days' => 2],
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

        // [LOGIC TOMBOL APPROVE] Gunakan rule yang sama dengan endpoint approve/reject
        $canApprove = $this->canHrActOnLeave(auth()->user(), $leave);

        return view('hr.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
        ]);
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
            'deduct_leave' => 'nullable|in:1', // Validasi checkbox
        ]);

        // Pastikan status valid
        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR, 'CANCEL_REQ'];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk disetujui.');

        abort_unless($this->canHrActOnLeave(auth()->user(), $leave), 403, 'Anda tidak memiliki izin untuk menyetujui pengajuan ini.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Etika Profesi: Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        }

        try {
            DB::transaction(function () use ($request, $leave) {
                
                // A. JIKA STATUS PERMINTAAN PEMBATALAN
                if ($leave->status === 'CANCEL_REQ') {
                    $leave->update([
                        'status'      => 'BATAL', // Set ke status BATAL (bukan APPROVED)
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'notes_hrd'   => $request->notes_hrd
                    ]);
                    // Return agar tidak lanjut ke logika potong saldo
                    return; 
                }

                // B. LOGIKA APPROVE CUTI BIASA
                $shouldDeduct = $request->input('deduct_leave') == '1';

                // Hanya potong jika checkbox dicentang DAN status sebelumnya belum approved
                if ($shouldDeduct && $leave->status !== LeaveRequest::STATUS_APPROVED) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);
                }

                // Update status pengajuan jadi APPROVED
                $leave->update([
                    'status'      => LeaveRequest::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes_hrd'   => $request->notes_hrd,
                ]);

                // [AUTO DELETE DUPLIKAT] Hapus pengajuan duplikat yang masih pending
                $this->deleteDuplicateLeaveRequests($leave);
            });

            // Pesan sukses
            // Jika ini Cancel Request, pesannya "Pembatalan Disetujui"
            if ($leave->status === 'BATAL') {
                return back()->with('success', 'Permintaan pembatalan telah disetujui.');
            }

            return back()->with('success', 'Pengajuan disetujui & Saldo dipotong sesuai hari kerja Role.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        $request->validate([
            'notes_hrd' => 'required|string|max:1000',
        ]);

        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR];
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

                // Update status menjadi REJECTED
                $leave->update([
                    'status'      => LeaveRequest::STATUS_REJECTED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes_hrd'   => $request->notes_hrd, 
                ]);
            });

            return back()->with('success', 'Pengajuan ditolak & saldo (jika ada) dikembalikan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function authorizeAccess()
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'isHR') || !$user->isHR()) {
            if (!in_array($user->role, ['HRD', 'HR STAFF', 'MANAGER HR'])) {
                 abort(403, 'Akses khusus HRD');
            }
        }
    }

    private function canHrActOnLeave(User $actor, LeaveRequest $leave): bool
    {
        if ($leave->user_id === $actor->id) {
            return false;
        }

        // Pengajuan milik HR Staff
        if ($this->isHrStaff($leave->user)) {
            // CUTI dan CUTI_KHUSUS tetap harus diapprove oleh HRD Master
            $typeValue = $leave->type instanceof LeaveType ? $leave->type->value : $leave->type;
            $isCutiOrSpecial = in_array($typeValue, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value], true);

            if ($isCutiOrSpecial) {
                // CUTI/CUTI KHUSUS: hanya HRD Master yang bisa approve
                return $this->isHrdMaster($actor);
            }

            // Non-CUTI (IZIN, SAKIT, dll): HR STAFF lain bisa approve
            if ($this->isHrStaff($actor)) {
                return true;
            }

            // HRD Master juga tetap bisa approve
            return $this->isHrdMaster($actor);
        }

        if ($leave->status === LeaveRequest::PENDING_HR || $leave->status === 'CANCEL_REQ') {
            return true;
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return false;
        }

        if ((int) $leave->user->direct_supervisor_id === (int) $actor->id || empty($leave->user->direct_supervisor_id)) {
            return true;
        }

        // Rule khusus: HRD (Master) dapat memproses pengajuan milik HR Staff.
        return $this->isHrdMaster($actor) && $this->isHrStaff($leave->user);
    }

    private function isHrdMaster(User $user): bool
    {
        $role = $this->normalizeRole($user->role);

        return in_array($role, ['HRD', 'HR MANAGER'], true);
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
        $role = $this->normalizeRole($user->role);

        if ($role === 'EMPLOYEE' && !empty($user->direct_supervisor_id)) {
            return LeaveRequest::PENDING_SUPERVISOR;
        }

        if (in_array($role, ['SUPERVISOR', 'HRD'], true) && !empty($user->manager_id)) {
            return LeaveRequest::PENDING_SUPERVISOR;
        }

        return LeaveRequest::PENDING_HR;
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