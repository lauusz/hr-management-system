<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\EmployeeShift;
use App\Models\LeaveRequest;
use App\Models\ShiftDay;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestStateMachine;
use App\Services\LeaveRequestWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    // Inject ImageCompressor
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected LeaveBalanceService $leaveBalanceService,
        protected LeaveRequestStateMachine $stateMachine,
        protected LeaveRequestWorkflowService $workflowService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // Eager Load
        $query = LeaveRequest::with(['user.directSupervisor', 'user.manager', 'approver'])
            ->orderByDesc('created_at');

        // [ADJUSTMENT] STRICTLY MY DATA (HANYA PUNYA SAYA)
        // Halaman ini murni "Riwayat Pengajuan Saya".
        // Tidak peduli role-nya apa, yang tampil hanya data milik user yang sedang login.
        // Data bawahan/master ada di Controller Approval/Master terpisah.
        $query->where('user_id', $userId);

        // Filter Form (Jenis Pengajuan)
        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        }

        // Filter Form (Status)
        $statusFilter = $request->query('status');
        $validStatuses = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            LeaveRequest::STATUS_CANCELLED,
        ];
        if ($statusFilter && in_array($statusFilter, $validStatuses, true)) {
            $query->where('status', $statusFilter);
        }

        // Filter Form (Range Tanggal)
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
                    if ($from->gt($to)) {
                        $temp = $from;
                        $from = $to;
                        $to = $temp;
                    }
                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception) {
            }
        }

        // Statistik dihitung dari seluruh data user (sebelum paginasi) agar akurat.
        $statsQuery = clone $query;
        $totalCount = (clone $statsQuery)->count();
        $approvedCount = (clone $statsQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count();
        $pendingCount = (clone $statsQuery)->whereIn('status', [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR])->count();
        $rejectedCount = (clone $statsQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count();

        $items = $query->paginate(20)->appends([
            'type' => $typeFilter,
            'status' => $statusFilter,
            'submitted_range' => $submittedRange,
        ]);

        return view('leave_requests.index', [
            'items' => $items,
            'typeFilter' => $typeFilter,
            'typeOptions' => LeaveType::cases(),
            'statusFilter' => $statusFilter,
            'statusOptions' => [
                LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Atasan',
                LeaveRequest::PENDING_HR => 'Menunggu HRD',
                LeaveRequest::STATUS_APPROVED => 'Disetujui',
                LeaveRequest::STATUS_REJECTED => 'Ditolak',
                LeaveRequest::STATUS_CANCELLED => 'Dibatalkan',
            ],
            'submittedRange' => $submittedRange,
            'stats' => [
                'total' => $totalCount,
                'approved' => $approvedCount,
                'pending' => $pendingCount,
                'rejected' => $rejectedCount,
            ],
        ]);
    }

    public function create()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $shiftEndTime = null;
        $employeeShift = EmployeeShift::where('user_id', $userId)->first();

        if ($employeeShift && $employeeShift->shift_id) {
            $today = now();
            $dayOfWeek = (int) $today->dayOfWeekIso;
            $shiftDay = ShiftDay::where('shift_id', $employeeShift->shift_id)
                ->where('day_of_week', $dayOfWeek)->where('is_holiday', false)->first();

            if ($shiftDay && $shiftDay->end_time) {
                try {
                    $shiftEndTime = Carbon::parse($shiftDay->end_time)->format('H:i');
                } catch (\Throwable) {
                }
            }
        }

        $canOffSpv = $this->isSpvUser($user);
        $offInfo = null;
        if ($canOffSpv) {
            $month = now()->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($month);
            $approvedCount = $this->offSpvApprovedCountInMonth($userId, $month);
            $remaining = max(0, $limit - $approvedCount);
            $offInfo = ['limit' => $limit, 'approved' => $approvedCount, 'remaining' => $remaining, 'month' => $month->format('Y-m')];
        }

        $approvers = collect([]);
        $roleStr = $this->getRoleString($user);
        if ($roleStr === 'EMPLOYEE') {
            $approvers = User::where('role', UserRole::SUPERVISOR)->where('id', '!=', $userId)->orderBy('name')->get();
        } elseif ($roleStr === 'SUPERVISOR' || $roleStr === 'HRD') {
            $approvers = User::whereIn('role', [UserRole::MANAGER])->where('id', '!=', $userId)->orderBy('name')->get();
        }

        return view('leave_requests.create', [
            'shiftEndTime' => $shiftEndTime,
            'canOffSpv' => $canOffSpv,
            'offSpvInfo' => $offInfo,
            'managers' => $approvers,
        ]);
    }

    public function edit(LeaveRequest $leave_request)
    {
        $user = Auth::user();

        if (! ($leave_request->user_id === $user->id || $user->isHR())) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengubah data pengajuan ini.');
        }

        if (! in_array($leave_request->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return redirect()->route('leave-requests.index')
                ->with('error', 'Pengajuan sudah diproses, tidak dapat diubah.');
        }

        $canOffSpv = $this->isSpvUser($user);
        $offInfo = null;
        if ($canOffSpv) {
            $month = now()->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($month);
            $approvedCount = $this->offSpvApprovedCountInMonth($user->id, $month);
            $remaining = max(0, $limit - $approvedCount);
            $offInfo = ['limit' => $limit, 'approved' => $approvedCount, 'remaining' => $remaining, 'month' => $month->format('Y-m')];
        }

        return view('leave_requests.edit', [
            'item' => $leave_request,
            'canOffSpv' => $canOffSpv,
            'offSpvInfo' => $offInfo,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id();

        // Rate Limiter
        $throttleKey = 'submit_izin_'.$userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $nextTime = Carbon::now()->addSeconds($seconds)->format('H:i');

            return redirect()->back()->withInput()->with('error', "Anda baru saja melakukan pengajuan. Mohon tunggu hingga pukul $nextTime.");
        }

        // Validasi Input
        $validated = $request->validate([
            'type' => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
            'substitute_pic' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'substitute_phone' => ['nullable', 'string', 'max:50', Rule::requiredIf(fn () => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'special_leave_detail' => ['nullable', 'string', Rule::requiredIf(fn () => $request->type === LeaveType::CUTI_KHUSUS->value)],
        ], [
            'photo.max' => 'Ukuran file bukti pendukung tidak boleh lebih dari 8 MB.',
            'photo.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak lebih dari 8 MB.',
        ]);

        $type = $validated['type'];

        // Cek overlap tanggal dengan pengajuan aktif lainnya
        $duplicates = $this->findOverlappingLeaveRequests(
            $userId,
            $type,
            $validated['start_date'],
            $validated['end_date']
        );

        if ($duplicates->isNotEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $this->formatOverlapMessage($duplicates));
        }

        // [VALIDASI] SALDO CUTI & MASA KERJA (ADJUSTED BY ROLE)
        if ($type === LeaveType::CUTI->value) {
            $joinDate = $user->profile?->tgl_bergabung ? Carbon::parse($user->profile->tgl_bergabung) : null;
            if ($joinDate && $joinDate->diffInYears(now()) < 1) {
                return redirect()->back()->withInput()->with('error', 'Maaf, masa kerja Anda belum 1 tahun. Belum berhak mengajukan Cuti Tahunan.');
            }

            $daysRequested = $this->leaveBalanceService->calculateEffectiveDaysForUser(
                $user,
                $validated['start_date'],
                $validated['end_date'],
            );

            if ($user->leave_balance < $daysRequested) {
                return redirect()->back()->withInput()->with('error', "Sisa cuti tidak mencukupi. (Sisa: {$user->leave_balance} hari, Pengajuan Efektif: {$daysRequested} hari).");
            }
        }

        // Logic Notes
        $notesParts = $this->buildLeaveNotes($user, $type, $validated);

        $isOffSpv = $type === LeaveType::OFF_SPV->value;
        if ($isOffSpv) {
            if (! $this->isSpvUser($user)) {
                return redirect()->back()->withInput()->with('error', 'Tipe OFF hanya untuk Supervisor.');
            }

            if (Carbon::parse($validated['start_date'])->format('Y-m') !== now()->format('Y-m')) {
                return redirect()->back()->withInput()->with('error', 'Pengajuan OFF SPV hanya dapat dilakukan untuk bulan ini (tidak bisa untuk bulan depan).');
            }

            $monthRef = Carbon::parse($validated['start_date'])->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($monthRef);
            $approvedCount = $this->offSpvApprovedCountInMonth($userId, $monthRef);
            if (($limit - $approvedCount) <= 0) {
                return redirect()->back()->withInput()->with('error', 'Kuota OFF Supervisor habis.');
            }

            $startDate = Carbon::parse($validated['start_date']);
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->addDays(6);
            $alreadyInWeek = LeaveRequest::query()->where('user_id', $userId)->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])->exists();
            if ($alreadyInWeek) {
                return redirect()->back()->withInput()->with('error', 'Pengajuan OFF SPV maksimal 1x seminggu.');
            }
            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $notes = ! empty($notesParts) ? implode("\n", $notesParts) : null;
        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal = $type === LeaveType::IZIN_PULANG_AWAL->value;
        $rawStartTime = $request->input('start_time');
        $rawEndTime = $request->input('end_time');

        if ($isIzinTelat && ! $rawStartTime) {
            return redirect()->back()->withInput()->with('error', 'Estimasi jam tiba wajib diisi.');
        }
        if ($isIzinTengahKerja && (! $rawStartTime || ! $rawEndTime)) {
            return redirect()->back()->withInput()->with('error', 'Jam mulai/selesai wajib diisi.');
        }
        if ($isIzinPulangAwal && ! $rawStartTime) {
            return redirect()->back()->withInput()->with('error', 'Jam pulang wajib diisi.');
        }

        $fullPath = null;
        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $fullPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $photoBasename = basename($fullPath);
        }

        // =====================================================================
        // ROLE-BASED INITIAL STATUS
        // =====================================================================
        // Flow approval berbeda tergantung role pemohon:
        // - EMPLOYEE  : SPV ack (jika ada ds/mg) → HR final
        // - SUPERVISOR: Manager ack (jika ada mg) → HR final
        // - MANAGER   : Langsung ke HR (HANYA HRD boleh approve)
        // - HR_STAFF  : Langsung ke HR (HANYA HRD boleh approve)
        // - HRD       : Manager ack (jika ada mg) → APPROVED, atau ke HR inbox
        // =====================================================================

        $applicantRole = $this->getRoleString($user);
        $hasValidSupervisor = ! empty($user->direct_supervisor_id);
        $hasValidManager = false;
        if (! empty($user->manager_id)) {
            $hasValidManager = User::where('id', $user->manager_id)->exists();
        }

        $initialStatus = LeaveRequest::PENDING_HR;

        switch ($applicantRole) {
            case 'EMPLOYEE':
                // SPV atau Manager mengetahui (jika ada), lalu HR final
                if ($hasValidSupervisor || $hasValidManager) {
                    $initialStatus = LeaveRequest::PENDING_SUPERVISOR;
                }
                break;

            case 'SUPERVISOR':
                // Manager mengetahui (jika ada), lalu HR final
                if ($hasValidManager) {
                    $initialStatus = LeaveRequest::PENDING_SUPERVISOR;
                }
                break;

            case 'MANAGER':
                // Langsung ke HR inbox
                $initialStatus = LeaveRequest::PENDING_HR;
                break;

            case 'HR_STAFF':
                // Langsung ke HR inbox (HANYA HRD boleh approve)
                $initialStatus = LeaveRequest::PENDING_HR;
                break;

            case 'HRD':
                // Manager_id approve langsung (jika ada), atau ke HR inbox
                if ($hasValidManager) {
                    $initialStatus = LeaveRequest::PENDING_SUPERVISOR;
                }
                break;

            default:
                $initialStatus = LeaveRequest::PENDING_HR;
        }

        if ($isOffSpv) {
            $initialStatus = LeaveRequest::PENDING_HR;
        }

        try {
            LeaveRequest::create([
                'user_id' => $userId,
                'type' => $type,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => ($isIzinTengahKerja || $isIzinPulangAwal || $isIzinTelat) ? $rawStartTime : null,
                'end_time' => $isIzinTengahKerja ? $rawEndTime : null,
                'reason' => $validated['reason'],
                'photo' => $photoBasename,
                'status' => $initialStatus,
                'notes' => $notes,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'accuracy_m' => $validated['accuracy_m'] ?? null,
                'location_captured_at' => $validated['location_captured_at'] ?? null,
                'substitute_pic' => $validated['substitute_pic'] ?? null,
                'substitute_phone' => $validated['substitute_phone'] ?? null,
                'special_leave_category' => $validated['special_leave_detail'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            if ($fullPath !== null) {
                Storage::disk('public')->delete($fullPath);
            }

            throw $exception;
        }

        RateLimiter::hit($throttleKey, 10);
        if ($isIzinTelat) {
            return redirect()->route('leave-requests.index')->with('success', 'Pengajuan izin terlambat berhasil dikirim ke HRD. Silakan menunggu proses pengecekan.');
        }

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function show(LeaveRequest $leave_request)
    {
        if ($leave_request->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak berhak melihat data pengajuan ini.');
        }

        return view('leave_requests.show', ['item' => $leave_request->load('user', 'approver')]);
    }

    public function supportingFile(LeaveRequest $leave_request): StreamedResponse|
\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $leave_request->loadMissing('user');

        $canView = $leave_request->user_id === $user->id
            || $user->isHR()
            || (int) $leave_request->user->direct_supervisor_id === (int) $user->id
            || (int) $leave_request->user->manager_id === (int) $user->id;

        if (! $canView) {
            return redirect()->back()->with('error', 'Anda tidak berhak melihat bukti pendukung ini.');
        }
        abort_unless($leave_request->photo, 404, 'Bukti pendukung tidak tersedia.');

        $filename = basename(str_replace('\\', '/', $leave_request->photo));
        $path = 'leave_photos/'.$filename;
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404, 'File bukti pendukung tidak ditemukan.');

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'heic' => 'image/heic',
            'heif' => 'image/heif',
            default => $disk->mimeType($path) ?: 'application/octet-stream',
        };

        return $disk->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function uploadPhoto(Request $request, LeaveRequest $leave_request)
    {
        $user = Auth::user();
        $isOwner = $user->id === $leave_request->user_id;
        $isHRD = $user->isHR();

        // [P0-01] Upload bukti hanya untuk pemilik atau HR.
        if (! $isOwner && ! $isHRD) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengunggah bukti untuk pengajuan ini.');
        }

        if (! in_array($leave_request->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return back()->with('error', 'Pengajuan sudah diproses, bukti pendukung tidak dapat diunggah.');
        }

        $validated = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
        ], [
            'photo.max' => 'Ukuran file bukti pendukung tidak boleh lebih dari 8 MB.',
            'photo.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak lebih dari 8 MB.',
        ]);

        $fullPath = $this->imageCompressor->compressAndStore($validated['photo'], 'photo', 'leave_photos', 'leave_');

        $updated = $this->stateMachine->perform(
            $leave_request,
            LeaveRequestStateMachine::EDIT_PENDING,
            function (LeaveRequest $lockedLeave) use ($fullPath) {
                if ($lockedLeave->photo) {
                    Storage::disk('public')->delete('leave_photos/'.$lockedLeave->photo);
                }

                return ['photo' => basename($fullPath)];
            }
        );

        if (! $updated) {
            Storage::disk('public')->delete($fullPath);

            return back()->with('error', 'Pengajuan sudah diproses, bukti pendukung tidak dapat diunggah.');
        }

        return back()->with('success', 'Bukti pendukung berhasil diunggah.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $leaveRequest->user_id;
        $isHRD = $user->isHR();

        // [P0-01] Endpoint umum hanya boleh diakses pemilik atau HR.
        // MANAGER/Supervisor tidak boleh mengubah pengajuan user lain di sini.
        if (! $isOwner && ! $isHRD) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengubah data ini.');
        }

        // Semua actor (owner maupun HR) hanya dapat mengubah pengajuan pending.
        if (! in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat diubah.');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:5000'],
            'substitute_pic' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'substitute_phone' => ['nullable', 'string', 'max:50', Rule::requiredIf(fn () => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'special_leave_detail' => ['nullable', 'string', Rule::requiredIf(fn () => $request->type === LeaveType::CUTI_KHUSUS->value)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
        ], [
            'photo.max' => 'Ukuran file bukti pendukung tidak boleh lebih dari 8 MB.',
            'photo.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak lebih dari 8 MB.',
        ]);

        if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
            $validated['special_leave_category'] = $validated['special_leave_detail'] ?? $leaveRequest->special_leave_category;
        } else {
            $validated['special_leave_category'] = null;
        }
        unset($validated['special_leave_detail']);

        $type = $validated['type'];
        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal = $type === LeaveType::IZIN_PULANG_AWAL->value;
        $isTimeBased = in_array($type, [
            LeaveType::IZIN_TELAT->value,
            LeaveType::IZIN_TENGAH_KERJA->value,
            LeaveType::IZIN_PULANG_AWAL->value,
            LeaveType::IZIN->value,
        ], true);
        if (! $isTimeBased) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $rawStartTime = $request->input('start_time');
        $rawEndTime = $request->input('end_time');

        if ($isIzinTelat && ! $rawStartTime) {
            return redirect()->back()->withInput()->with('error', 'Estimasi jam tiba wajib diisi.');
        }
        if ($isIzinTengahKerja && (! $rawStartTime || ! $rawEndTime)) {
            return redirect()->back()->withInput()->with('error', 'Jam mulai/selesai wajib diisi.');
        }
        if ($isIzinPulangAwal && ! $rawStartTime) {
            return redirect()->back()->withInput()->with('error', 'Jam pulang wajib diisi.');
        }

        $applicant = $leaveRequest->user ?? $user;

        // [VALIDASI] SALDO CUTI & MASA KERJA saat edit menjadi CUTI
        if ($type === LeaveType::CUTI->value) {
            $joinDate = $applicant->profile?->tgl_bergabung ? Carbon::parse($applicant->profile->tgl_bergabung) : null;
            if ($joinDate && $joinDate->diffInYears(now()) < 1) {
                return redirect()->back()->withInput()->with('error', 'Maaf, masa kerja pemohon belum 1 tahun. Belum berhak mengajukan Cuti Tahunan.');
            }

            $daysRequested = $this->leaveBalanceService->calculateEffectiveDaysForUser(
                $applicant,
                $validated['start_date'],
                $validated['end_date'],
            );

            if ($applicant->leave_balance < $daysRequested) {
                return redirect()->back()->withInput()->with('error', "Sisa cuti tidak mencukupi. (Sisa: {$applicant->leave_balance} hari, Pengajuan Efektif: {$daysRequested} hari).");
            }
        }

        // [VALIDASI] OFF_SPV
        $isOffSpv = $type === LeaveType::OFF_SPV->value;
        if ($isOffSpv) {
            if (! $this->isSpvUser($applicant)) {
                return redirect()->back()->withInput()->with('error', 'Tipe OFF hanya untuk Supervisor.');
            }

            if (Carbon::parse($validated['start_date'])->format('Y-m') !== now()->format('Y-m')) {
                return redirect()->back()->withInput()->with('error', 'Pengajuan OFF SPV hanya dapat dilakukan untuk bulan ini (tidak bisa untuk bulan depan).');
            }

            $monthRef = Carbon::parse($validated['start_date'])->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($monthRef);
            $approvedCount = $this->offSpvApprovedCountInMonth($applicant->id, $monthRef);
            if (($limit - $approvedCount) <= 0) {
                return redirect()->back()->withInput()->with('error', 'Kuota OFF Supervisor habis.');
            }

            $startDate = Carbon::parse($validated['start_date']);
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->addDays(6);
            $alreadyInWeek = LeaveRequest::query()->where('user_id', $applicant->id)->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])
                ->when($leaveRequest->id, fn ($q) => $q->whereKeyNot($leaveRequest->id))
                ->exists();
            if ($alreadyInWeek) {
                return redirect()->back()->withInput()->with('error', 'Pengajuan OFF SPV maksimal 1x seminggu.');
            }
            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        // Cek overlap tanggal dengan pengajuan aktif lainnya (kecuali dirinya sendiri)
        $duplicates = $this->findOverlappingLeaveRequests(
            $leaveRequest->user_id,
            $validated['type'],
            $validated['start_date'],
            $validated['end_date'],
            $leaveRequest->id
        );

        if ($duplicates->isNotEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $this->formatOverlapMessage($duplicates));
        }

        // Upload foto di luar transaction; jika terjadi race/rollback,
        // file yang baru diunggah akan dihapus agar tidak orphan.
        $uploadedPhotoPath = null;
        if ($request->hasFile('photo')) {
            $uploadedPhotoPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $validated['photo'] = basename($uploadedPhotoPath);
        }

        $updated = $this->stateMachine->perform(
            $leaveRequest,
            $isOffSpv ? LeaveRequestStateMachine::REVISE_FOR_HR : LeaveRequestStateMachine::EDIT_PENDING,
            function (LeaveRequest $lockedLeave) use ($request, $user, $validated) {
                if ($request->hasFile('photo')) {
                    if ($lockedLeave->photo) {
                        Storage::disk('public')->delete('leave_photos/'.$lockedLeave->photo);
                    }
                }

                $dataToUpdate = $validated;

                // [FIX] Jangan menimpa seluruh notes. Pertahankan catatan audit
                // (misal: system note dari edit HR) dan hanya refresh bagian
                // warning otomatis agar tidak stale.
                $existingNotes = $lockedLeave->notes ?? '';
                $preservedLines = collect(explode("\n", $existingNotes))
                    ->filter(fn ($line) => ! str_contains($line, 'melebihi batas maksimal') && ! str_contains($line, 'Dihitung Potong Uang Makan'))
                    ->values()
                    ->all();
                $preservedNotes = implode("\n", $preservedLines);

                $newWarnings = $this->buildLeaveNotesAsText($lockedLeave->user ?? $user, $validated['type'], [
                    ...$validated,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'special_leave_detail' => $validated['special_leave_category'] ?? null,
                ]);

                if ($newWarnings !== null && $newWarnings !== '') {
                    $dataToUpdate['notes'] = $preservedNotes !== '' ? $preservedNotes."\n".$newWarnings : $newWarnings;
                } else {
                    $dataToUpdate['notes'] = $preservedNotes !== '' ? $preservedNotes : null;
                }

                return $dataToUpdate;
            }
        );

        if (! $updated) {
            if ($uploadedPhotoPath !== null) {
                Storage::disk('public')->delete($uploadedPhotoPath);
            }

            return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat diubah.');
        }

        return redirect()->route('leave-requests.index')->with('success', 'Data pengajuan berhasil diperbarui sepenuhnya.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        if (! Gate::allows('delete', $leaveRequest)) {
            return redirect()->back()->with('error', 'Anda tidak berhak membatalkan pengajuan ini.');
        }

        $isOwner = $user->id === $leaveRequest->user_id;
        $isHRD = $user->isHR();

        // HR dapat membatalkan pengajuan pending atau APPROVED, termasuk miliknya sendiri.
        if ($isHRD) {
            if (! in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR, LeaveRequest::STATUS_APPROVED], true)) {
                return redirect()->back()->with('error', 'Pengajuan ini tidak dapat dibatalkan.');
            }
        } elseif ($isOwner) {
            // Owner non-HR hanya dapat membatalkan pengajuan pending.
            if (! in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
                return redirect()->route('leave-requests.index')->with('error', 'Hanya pengajuan yang masih pending yang bisa dibatalkan oleh pemohon.');
            }
        }

        $allowedSourceStatuses = $isHRD
            ? null
            : [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR];

        $cancelled = $this->workflowService->cancelLeaveRequest($leaveRequest, $user, null, $allowedSourceStatuses);

        if (! $cancelled) {
            return redirect()->back()->with('error', 'Pengajuan ini tidak dapat dibatalkan.');
        }

        if ($isHRD) {
            return redirect()->route('hr.leave.index')->with('success', 'Pengajuan berhasil dibatalkan dan saldo (jika ada) dikembalikan.');
        }

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    // --- Private Helpers ---
    private function findOverlappingLeaveRequests(
        int $userId,
        string $type,
        string $startDate,
        string $endDate,
        ?int $excludeLeaveId = null
    ): \Illuminate\Database\Eloquent\Collection {
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->when($excludeLeaveId, fn ($query) => $query->whereKeyNot($excludeLeaveId))
            ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            })
            ->orderBy('start_date')
            ->get(['id', 'type', 'start_date', 'end_date', 'status', 'created_at']);
    }

    private function formatOverlapMessage($duplicates): string
    {
        $first = $duplicates->first();

        if (! $first) {
            return 'Tanggal yang dipilih bertabrakan dengan pengajuan yang sudah ada.';
        }

        $typeLabel = $first->type instanceof LeaveType
            ? $first->type->label()
            : (LeaveType::tryFrom((string) $first->type)?->label() ?? (string) $first->type);

        $start = Carbon::parse($first->start_date)->locale('id')->translatedFormat('j F Y');
        $end = Carbon::parse($first->end_date)->locale('id')->translatedFormat('j F Y');

        $dateText = $start === $end
            ? $start
            : "{$start} - {$end}";

        return "Sudah ada pengajuan {$typeLabel} pada tanggal {$dateText}. Pengajuan dengan jenis yang sama tidak bisa dibuat di tanggal yang sama.";
    }

    private function getRoleString($user)
    {
        if (! $user) {
            return '';
        }

        return strtoupper((string) ($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role));
    }

    private function isSpvUser($user): bool
    {
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'isSupervisor')) {
            return $user->isSupervisor();
        }

        return $this->getRoleString($user) === 'SUPERVISOR';
    }

    private function offSpvMonthlyLimitByMonth(Carbon $monthStart): int
    {
        $fridayCount = $monthStart->copy()->startOfMonth()->daysUntil($monthStart->copy()->endOfMonth())
            ->filter(fn ($date) => $date->isFriday())
            ->count();

        return max(0, $fridayCount - 2);
    }

    private function offSpvApprovedCountInMonth(int $userId, Carbon $monthStart): int
    {
        $start = $monthStart->copy()->startOfMonth()->toDateString();
        $end = $monthStart->copy()->endOfMonth()->toDateString();

        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', LeaveType::OFF_SPV->value)
            ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])
            ->whereBetween('start_date', [$start, $end])
            ->count();
    }

    private function buildLeaveNotes($user, string $type, array $validated): array
    {
        $notesParts = [];

        if ($type === LeaveType::CUTI_KHUSUS->value) {
            $category = $validated['special_leave_detail'] ?? null;
            $limits = [
                'NIKAH_KARYAWAN' => 3,
                'ISTRI_MELAHIRKAN' => 2,
                'ISTRI_KEGUGURAN' => 2,
                'KHITANAN_ANAK' => 2,
                'PEMBAPTISAN_ANAK' => 2,
                'NIKAH_ANAK' => 2,
                'DEATH_EXTENDED' => 2,
                'DEATH_CORE' => 2,
                'DEATH_HOUSE' => 1,
                'HAJI' => 40,
                'UMROH' => 14,
            ];

            $maxDays = $limits[$category] ?? 0;
            $effectiveDays = $this->leaveBalanceService->calculateEffectiveDaysForUser(
                $user,
                $validated['start_date'],
                $validated['end_date'],
            );

            if ($maxDays > 0 && $effectiveDays > $maxDays) {
                $notesParts[] = "Durasi pengajuan {$effectiveDays} hari kerja melebihi batas maksimal {$maxDays} hari.";
            }
        }

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);
        if ($type === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) {
                $notesParts[] = "Pengajuan H-{$daysDiff} (kurang dari H-7). Dihitung Potong Uang Makan.";
            }
        }

        return $notesParts;
    }

    private function buildLeaveNotesAsText($user, string $type, array $validated): ?string
    {
        $notesParts = $this->buildLeaveNotes($user, $type, $validated);

        return ! empty($notesParts) ? implode("\n", $notesParts) : null;
    }

    /**
     * [AJAX] Hitung hari kerja efektif berdasarkan role user.
     */
    public function calculateEffectiveDays(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (! $startDate || ! $endDate) {
            return response()->json([
                'days' => 0,
                'label' => '0 hari',
            ]);
        }

        try {
            $breakdown = $this->leaveBalanceService->calculateEffectiveDayBreakdownForUser(
                $user,
                $startDate,
                $endDate
            );
            $days = $breakdown['total'];
            $leaveBalance = (float) $user->leave_balance;
            $shortage = max(0, $days - $leaveBalance);
            $formatted = rtrim(rtrim(number_format($days, 1), '0'), '.');
            $label = $formatted == 1 ? '1 hari kerja' : $formatted.' hari kerja';

            return response()->json([
                'days' => $days,
                'label' => $label,
                'breakdown' => $breakdown,
                'leave_balance' => $leaveBalance,
                'shortage' => $shortage,
                'exceeds_balance' => $shortage > 0,
                'is_five_day' => in_array($this->getRoleString($user), ['HRD', 'MANAGER'], true),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'days' => 0,
                'label' => '0 hari',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * [AJAX] Cari rekan aktif untuk PIC pengganti.
     * Menampilkan nama lengkap saja; saat dipilih otomatis mengisi nama + telepon.
     */
    public function searchSubstitute(Request $request)
    {
        $query = trim((string) $request->query('q'));

        if ($query === '') {
            return response()->json([]);
        }

        $status = $request->query('status', User::STATUS_ACTIVE);

        $users = User::where('status', $status)
            ->where('id', '!=', Auth::id())
            ->where('name', 'like', '%'.$query.'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($users);
    }

    /**
     * [AJAX] Cek pengajuan duplikat (tanggal overlap)
     */
    public function checkDuplicate(Request $request)
    {
        $userId = Auth::id();
        $type = $request->input('type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $excludeId = $request->input('exclude_id');

        // Validasi input
        if (! $type || ! in_array($type, LeaveType::values(), true) || ! $startDate || ! $endDate) {
            return response()->json([
                'has_duplicate' => false,
                'message' => 'Jenis pengajuan atau tanggal tidak valid',
            ]);
        }

        $duplicates = $this->findOverlappingLeaveRequests(
            $userId,
            $type,
            $startDate,
            $endDate,
            $excludeId ? (int) $excludeId : null
        );

        if ($duplicates->isNotEmpty()) {
            $duplicateData = $duplicates->map(function ($dup) {
                return [
                    'id' => $dup->id,
                    'type' => $dup->type instanceof LeaveType ? $dup->type->label() : (LeaveType::tryFrom((string) $dup->type)?->label() ?? (string) $dup->type),
                    'start_date' => Carbon::parse($dup->start_date)->locale('id')->translatedFormat('j F Y'),
                    'end_date' => Carbon::parse($dup->end_date)->locale('id')->translatedFormat('j F Y'),
                    'status' => $dup->status,
                    'status_label' => $dup->status_label,
                    'created_at' => $dup->created_at->format('d M Y H:i'),
                ];
            });

            return response()->json([
                'has_duplicate' => true,
                'message' => $this->formatOverlapMessage($duplicates),
                'duplicates' => $duplicateData,
            ]);
        }

        return response()->json([
            'has_duplicate' => false,
            'message' => 'Tanggal tersedia',
        ]);
    }
}
