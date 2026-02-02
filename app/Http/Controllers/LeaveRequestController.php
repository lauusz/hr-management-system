<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\EmployeeShift;
use App\Models\ShiftDay;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter; 
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    // Inject ImageCompressor
    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

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
                    if ($from->gt($to)) { $temp = $from; $from = $to; $to = $temp; }
                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {}
        }

        $items = $query->paginate(100)->appends([
            'type'            => $typeFilter,
            'submitted_range' => $submittedRange,
        ]);

        return view('leave_requests.index', [
            'items'          => $items,
            'typeFilter'     => $typeFilter,
            'typeOptions'    => LeaveType::cases(),
            'submittedRange' => $submittedRange,
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
                try { $shiftEndTime = Carbon::parse($shiftDay->end_time)->format('H:i'); } catch (\Throwable $e) {}
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
        } elseif ($roleStr === 'SUPERVISOR') {
            $approvers = User::whereIn('role', [UserRole::MANAGER])->where('id', '!=', $userId)->orderBy('name')->get();
        }

        return view('leave_requests.create', [
            'shiftEndTime' => $shiftEndTime, 'canOffSpv' => $canOffSpv,
            'offSpvInfo' => $offInfo, 'managers' => $approvers, 
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id();

        // Rate Limiter
        $throttleKey = 'submit_izin_' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $nextTime = Carbon::now()->addSeconds($seconds)->format('H:i');
            return redirect()->back()->withInput()->withErrors(['error' => "Anda baru saja melakukan pengajuan. Mohon tunggu hingga pukul $nextTime."]);
        }

        // Validasi Input
        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['required', 'string'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
            'manager_id' => ['nullable', 'exists:users,id'], 
            'substitute_pic' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn() => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'substitute_phone' => ['nullable', 'string', 'max:50', Rule::requiredIf(fn() => in_array($request->type, [LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value, LeaveType::SAKIT->value]))],
            'special_leave_detail' => ['nullable', 'string', Rule::requiredIf(fn() => $request->type === LeaveType::CUTI_KHUSUS->value)],
        ]);

        $type = $validated['type'];

        // Cek Duplikasi
        $isDuplicate = LeaveRequest::where('user_id', $userId)
            ->where('type', $type)
            ->whereDate('start_date', $validated['start_date'])
            ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, 'BATAL'])
            ->exists();

        if ($isDuplicate) return redirect()->back()->withInput()->withErrors(['error' => 'Anda sudah memiliki pengajuan aktif pada tanggal tersebut.']);

        // [VALIDASI] SALDO CUTI & MASA KERJA (ADJUSTED BY ROLE)
        if ($type === LeaveType::CUTI->value) {
            $joinDate = $user->profile?->tgl_bergabung ? Carbon::parse($user->profile->tgl_bergabung) : null;
            if ($joinDate && $joinDate->diffInYears(now()) < 1) {
                return back()->withInput()->withErrors(['error' => 'Maaf, masa kerja Anda belum 1 tahun. Belum berhak mengajukan Cuti Tahunan.']);
            }

            $startDate = Carbon::parse($validated['start_date']);
            $endDate   = Carbon::parse($validated['end_date']);
            
            // --- LOGIC HITUNG HARI KERJA (BERDASARKAN ROLE) ---
            $period = CarbonPeriod::create($startDate, $endDate);
            $daysRequested = 0;

            $roleStr = $this->getRoleString($user);
            $fiveDayWorkWeekRoles = ['HRD', 'HR STAFF', 'MANAGER'];
            $isFiveDayWorkWeek = in_array($roleStr, $fiveDayWorkWeekRoles);

            foreach ($period as $date) {
                if ($isFiveDayWorkWeek) {
                    if ($date->isSaturday() || $date->isSunday()) continue;
                } else {
                    if ($date->isSunday()) continue;
                }
                $daysRequested++;
            }

            if ($user->leave_balance < $daysRequested) {
                return back()->withInput()->withErrors(['error' => "Sisa cuti tidak mencukupi. (Sisa: {$user->leave_balance} hari, Pengajuan Efektif: {$daysRequested} hari)."]);
            }
        }

        // Logic Notes
        $notesParts = [];
        if ($type === LeaveType::CUTI_KHUSUS->value) {
            $category = $validated['special_leave_detail'];
            $limits = ['NIKAH_KARYAWAN'=>4,'ISTRI_MELAHIRKAN'=>2,'ISTRI_KEGUGURAN'=>2,'KHITANAN_ANAK'=>2,'PEMBAPTISAN_ANAK'=>2,'NIKAH_ANAK'=>2,'DEATH_EXTENDED'=>2,'DEATH_CORE'=>2,'DEATH_HOUSE'=>1,'HAJI'=>40,'UMROH'=>14];
            $maxDays = $limits[$category] ?? 0;
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $diffDays = $startDate->diffInDays($endDate) + 1; 
            if ($maxDays > 0 && $diffDays > $maxDays) $notesParts[] = "Durasi pengajuan {$diffDays} hari melebihi batas maksimal {$maxDays} hari.";
        }

        $isOffSpv = $type === LeaveType::OFF_SPV->value;
        if ($isOffSpv) {
            if (!$this->isSpvUser($user)) return back()->withErrors('Tipe OFF hanya untuk Supervisor.')->withInput();
            $monthRef = Carbon::parse($validated['start_date'])->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($monthRef);
            $approvedCount = $this->offSpvApprovedCountInMonth($userId, $monthRef);
            if (($limit - $approvedCount) <= 0) return back()->withErrors('Kuota OFF Supervisor habis.')->withInput();
            
            $startDate = Carbon::parse($validated['start_date']);
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->addDays(6);
            $alreadyInWeek = LeaveRequest::query()->where('user_id', $userId)->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, 'BATAL'])->exists();
            if ($alreadyInWeek) return back()->withErrors('Pengajuan OFF SPV maksimal 1x seminggu.')->withInput();
            $validated['end_date'] = $validated['start_date']; $validated['start_time'] = null; $validated['end_time'] = null;
        }

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);
        if ($type === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) $notesParts[] = "Pengajuan H-{$daysDiff} (kurang dari H-7). Termasuk Potong Uang Makan.";
        }

        $notes = !empty($notesParts) ? implode("\n", $notesParts) : null;
        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal  = $type === LeaveType::IZIN_PULANG_AWAL->value;
        $rawStartTime = $request->input('start_time');
        $rawEndTime   = $request->input('end_time');

        if ($isIzinTelat && !$rawStartTime) return back()->withErrors('Estimasi jam tiba wajib diisi.')->withInput();
        if ($isIzinTengahKerja && (!$rawStartTime || !$rawEndTime)) return back()->withErrors('Jam mulai/selesai wajib diisi.')->withInput();
        if ($isIzinPulangAwal && !$rawStartTime) return back()->withErrors('Jam pulang wajib diisi.')->withInput();

        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $fullPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $photoBasename = basename($fullPath);
        }

        $inputApproverId = $request->input('manager_id'); 
        $roleStr = $this->getRoleString($user);
        $initialStatus = LeaveRequest::PENDING_HR; 
        
        if ($roleStr === 'EMPLOYEE') {
            if (!empty($inputApproverId)) { $user->update(['direct_supervisor_id' => $inputApproverId]); }
            if (!empty($user->direct_supervisor_id)) { $initialStatus = LeaveRequest::PENDING_SUPERVISOR; }
        } elseif ($roleStr === 'SUPERVISOR') {
            if (!empty($inputApproverId)) { $user->update(['manager_id' => $inputApproverId]); }
            if (!empty($user->manager_id)) { $initialStatus = LeaveRequest::PENDING_SUPERVISOR; }
        }

        LeaveRequest::create([
            'user_id' => $userId, 'type' => $type, 'start_date' => $validated['start_date'], 'end_date' => $validated['end_date'],
            'start_time' => ($isIzinTengahKerja || $isIzinPulangAwal || $isIzinTelat) ? $rawStartTime : null,
            'end_time' => $isIzinTengahKerja ? $rawEndTime : null,
            'reason' => $validated['reason'], 'photo' => $photoBasename, 'status' => $initialStatus, 'notes' => $notes,
            'latitude' => $validated['latitude'] ?? null, 'longitude' => $validated['longitude'] ?? null, 'accuracy_m' => $validated['accuracy_m'] ?? null, 'location_captured_at' => $validated['location_captured_at'] ?? null,
            'substitute_pic' => $validated['substitute_pic'] ?? null, 'substitute_phone' => $validated['substitute_phone'] ?? null,
            'special_leave_category' => $validated['special_leave_detail'] ?? null,
        ]);

        RateLimiter::hit($throttleKey, 10);
        if ($isIzinTelat) return redirect()->route('leave-requests.create')->with('show_izin_telat_popup', true);
        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function show(LeaveRequest $leave_request)
    {
        return view('leave_requests.show', ['item' => $leave_request->load('user', 'approver')]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $leaveRequest->user_id;
        $roleStr = $this->getRoleString($user);
        $isHRD   = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) abort(403, 'Anda tidak berhak mengubah data ini.');

        if ($isOwner && !$isHRD) {
            if (!in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR])) {
                return back()->withErrors('Pengajuan sudah diproses, tidak dapat diubah sendiri. Hubungi atasan.');
            }
        }

        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['nullable', 'string', 'max:5000'],
            'substitute_pic' => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
            'special_leave_detail' => ['nullable', 'string'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
        ]);

        if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
            $validated['special_leave_category'] = $validated['special_leave_detail'] ?? $leaveRequest->special_leave_category;
        } else {
            $validated['special_leave_category'] = null;
        }
        unset($validated['special_leave_detail']); 

        $isTimeBased = in_array($validated['type'], [
            LeaveType::IZIN_TELAT->value, LeaveType::IZIN_TENGAH_KERJA->value, LeaveType::IZIN_PULANG_AWAL->value, LeaveType::IZIN->value 
        ]);
        if (!$isTimeBased) { $validated['start_time'] = null; $validated['end_time'] = null; }

        if ($request->hasFile('photo')) {
            if ($leaveRequest->photo) Storage::disk('public')->delete('leave_photos/' . $leaveRequest->photo);
            $fullPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', 'leave_photos', 'leave_');
            $validated['photo'] = basename($fullPath);
        }

        $leaveRequest->update($validated);
        return back()->with('success', 'Data pengajuan berhasil diperbarui sepenuhnya.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $leaveRequest->user_id;
        $roleStr = $this->getRoleString($user);
        $isHRD   = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if ($isOwner && !$isHRD) {
            if (!in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
                return back()->with('error', 'Hanya pengajuan yang masih pending yang bisa dibatalkan oleh pemohon.');
            }
        }
        
        $leaveTypeValue = $leaveRequest->type instanceof LeaveType ? $leaveRequest->type->value : $leaveRequest->type;
        $targetValue = LeaveType::CUTI->value;

        // REFUND SALDO LOGIC (ROLE BASED)
        if ($leaveRequest->status === LeaveRequest::STATUS_APPROVED && $leaveTypeValue === $targetValue) {
            $start = Carbon::parse($leaveRequest->start_date);
            $end   = Carbon::parse($leaveRequest->end_date);
            $period = CarbonPeriod::create($start, $end);
            
            $leaveUser = $leaveRequest->user; 
            $userRoleStr = $this->getRoleString($leaveUser);
            $fiveDayWorkWeekRoles = ['HRD', 'HR STAFF', 'MANAGER'];
            $isFiveDay = in_array($userRoleStr, $fiveDayWorkWeekRoles);

            $daysToRefund = 0;
            foreach ($period as $date) {
                if ($isFiveDay) {
                    if ($date->isSaturday() || $date->isSunday()) continue;
                } else {
                    if ($date->isSunday()) continue;
                }
                $daysToRefund++;
            }

            if ($daysToRefund > 0) {
                $leaveUser->increment('leave_balance', $daysToRefund);
            }
        }

        $leaveRequest->update(['status' => 'BATAL']);

        if ($isHRD && !$isOwner) return redirect()->route('hr.leave.index')->with('success', 'Pengajuan berhasil dibatalkan dan saldo (jika ada) dikembalikan.');
        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    // --- Private Helpers ---
    private function getRoleString($user) {
        if (!$user) return '';
        return strtoupper((string) ($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role));
    }
    private function isSpvUser($user): bool {
        if (!$user) return false;
        if (method_exists($user, 'isSupervisor')) return $user->isSupervisor();
        return $this->getRoleString($user) === 'SUPERVISOR';
    }
    private function offSpvMonthlyLimitByMonth(Carbon $monthStart): int {
        $start = $monthStart->copy()->startOfMonth()->startOfDay();
        $end = $monthStart->copy()->endOfMonth()->startOfDay();
        $fridayCount = 0; $cursor = $start->copy();
        while ($cursor->lte($end)) { if ((int) $cursor->dayOfWeekIso === 5) $fridayCount++; $cursor->addDay(); }
        return max(0, $fridayCount - 2);
    }
    private function offSpvApprovedCountInMonth(int $userId, Carbon $monthStart): int {
        $start = $monthStart->copy()->startOfMonth()->toDateString();
        $end = $monthStart->copy()->endOfMonth()->toDateString();
        return LeaveRequest::query()->where('user_id', $userId)->where('type', LeaveType::OFF_SPV->value)
            ->where('status', LeaveRequest::STATUS_APPROVED)->whereBetween('start_date', [$start, $end])->count();
    }
}