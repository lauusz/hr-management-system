<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\EmployeeShift;
use App\Models\ShiftDay;
use App\Services\Image\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    // Inject ImageCompressor
    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = LeaveRequest::with(['user', 'approver'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        } else {
            $typeFilter = null;
        }

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
                        $from  = $to;
                        $to    = $temp;
                    }

                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {
                $submittedRange = null;
            }
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
        $shiftEndTime = null;

        $employeeShift = EmployeeShift::where('user_id', $userId)->first();

        if ($employeeShift && $employeeShift->shift_id) {
            $today = now();
            $dayOfWeek = (int) $today->dayOfWeekIso;

            $shiftDay = ShiftDay::where('shift_id', $employeeShift->shift_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_holiday', false)
                ->first();

            if ($shiftDay && $shiftDay->end_time) {
                try {
                    $shiftEndTime = Carbon::parse($shiftDay->end_time)->format('H:i');
                } catch (\Throwable $e) {
                    $shiftEndTime = null;
                }
            }
        }

        $user = Auth::user();
        $canOffSpv = $this->isSpvUser($user);

        $offInfo = null;
        if ($canOffSpv) {
            $month = now()->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($month);
            $approvedCount = $this->offSpvApprovedCountInMonth($userId, $month);
            $remaining = max(0, $limit - $approvedCount);

            $offInfo = [
                'limit' => $limit,
                'approved' => $approvedCount,
                'remaining' => $remaining,
                'month' => $month->format('Y-m'),
            ];
        }

        return view('leave_requests.create', [
            'shiftEndTime' => $shiftEndTime,
            'canOffSpv' => $canOffSpv,
            'offSpvInfo' => $offInfo,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Termasuk supervisor_id dari dropdown)
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
            // Validasi dropdown atasan (Boleh null jika pilih langsung HRD)
            'supervisor_id' => ['nullable', 'exists:users,id'], 
        ]);

        $user = Auth::user();
        $userId = Auth::id();
        $type = $validated['type'];

        // --- VALIDASI OFF SPV ---
        $isOffSpv = $type === LeaveType::OFF_SPV->value;

        if ($isOffSpv) {
            if (!$this->isSpvUser($user)) {
                return back()->withErrors('Tipe pengajuan OFF hanya tersedia untuk Supervisor.')->withInput();
            }

            $monthRef = Carbon::parse($validated['start_date'])->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($monthRef);
            $approvedCount = $this->offSpvApprovedCountInMonth($userId, $monthRef);
            $remaining = max(0, $limit - $approvedCount);

            if ($remaining <= 0) {
                return back()->withErrors('Kuota OFF Supervisor bulan ini sudah habis.')->withInput();
            }

            // Cek mingguan
            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            $alreadyInWeek = LeaveRequest::query()
                ->where('user_id', $userId)
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', '!=', LeaveRequest::STATUS_REJECTED)
                ->exists();

            if ($alreadyInWeek) {
                return back()->withErrors('Pengajuan OFF Supervisor maksimal 1 kali dalam 1 minggu.')->withInput();
            }

            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        // --- VALIDASI NOTES / CUTI ---
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);

        $notesParts = [];

        if ($type === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) {
                $notesParts[] = "Pengajuan dilakukan {$daysDiff} hari sebelum tanggal mulai cuti (kurang dari H-7).";
            }

            $profile = $user?->profile;
            if ($profile && $profile->tgl_bergabung) {
                $joinStart = Carbon::parse($profile->tgl_bergabung)->startOfDay();
                $tenureYears = $joinStart->diffInYears($today);
                if ($tenureYears < 1) {
                    $notesParts[] = 'Kurang dari 1 tahun kerja — pengajuan cuti akan dipotong gaji.';
                }
            }
        }

        $notes = null;
        if (!empty($notesParts)) {
            $notes = implode("\n", $notesParts);
        }

        // --- VALIDASI IZIN JAM ---
        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal  = $type === LeaveType::IZIN_PULANG_AWAL->value;

        $rawStartTime = $request->input('start_time');
        $rawEndTime   = $request->input('end_time');

        if ($isIzinTengahKerja) {
            if (!$rawStartTime || !$rawEndTime) {
                return back()->withErrors('Jam mulai dan jam selesai wajib diisi.')->withInput();
            }
            if ($rawEndTime <= $rawStartTime) {
                return back()->withErrors('Jam selesai harus lebih besar dari jam mulai.')->withInput();
            }
        }

        if ($isIzinPulangAwal) {
            if (!$rawStartTime) {
                return back()->withErrors('Jam pulang wajib diisi.')->withInput();
            }
            
            $employeeShift = EmployeeShift::where('user_id', $userId)->first();
            if ($employeeShift && $employeeShift->shift_id) {
                $izinDate = Carbon::parse($validated['start_date']);
                $dayOfWeek = (int) $izinDate->dayOfWeekIso;
                
                $shiftDay = ShiftDay::where('shift_id', $employeeShift->shift_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_holiday', false)
                    ->first();

                if ($shiftDay && $shiftDay->end_time) {
                    try {
                        $shiftEndObj = Carbon::createFromFormat('H:i:s', $shiftDay->end_time);
                        $shiftEndObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);
                        
                        $reqTimeObj = Carbon::createFromFormat('H:i', $rawStartTime);
                        $reqTimeObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);

                        $diffMinutes = $reqTimeObj->diffInMinutes($shiftEndObj, false);

                        if ($diffMinutes <= 0) {
                            return back()->withErrors('Jam pulang izin harus sebelum jam pulang shift.')->withInput();
                        }
                        if ($diffMinutes > 60) {
                            return back()->withErrors('Waktu izin pulang awal maksimal 1 jam sebelum jam pulang shift.')->withInput();
                        }
                    } catch (\Throwable $e) {
                    }
                }
            }
        }

        // --- UPLOAD FOTO ---
        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $fullPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 
                'photo', 
                'leave_photos', 
                'leave_'
            );
            $photoBasename = basename($fullPath);
        }

        // =========================================================
        // [LOGIC STATUS & BYPASS HRD]
        // =========================================================
        
        $selectedSupervisorId = $request->input('supervisor_id');
        $initialStatus = LeaveRequest::PENDING_HR; // Default Safe Fallback

        // 1. Jika User adalah SPV/Manager/HRD -> Biasanya bypass supervisor
        if (in_array($user->role, [UserRole::SUPERVISOR, UserRole::MANAGER, UserRole::HRD])) {
            $initialStatus = LeaveRequest::PENDING_HR;
        }
        // 2. Jika User MEMILIH atasan di dropdown
        elseif (!empty($selectedSupervisorId)) {
            $initialStatus = LeaveRequest::PENDING_SUPERVISOR;
            
            // UPDATE user supervisor_id agar ApprovalController (Gharin) bisa melihat data ini.
            // Tanpa update ini, query "where('supervisor_id', $me->id)" di controller lain akan gagal.
            $user->update(['direct_supervisor_id' => $selectedSupervisorId]);
        }
        // 3. Jika User MEMILIH "Langsung ke HRD" (Empty dropdown)
        else {
            $initialStatus = LeaveRequest::PENDING_HR;
            // Tidak perlu update user, biarkan statusnya PENDING_HR 
            // sehingga masuk ke dashboard HRLeaveController, bukan ApprovalController
        }

        LeaveRequest::create([
            'user_id'    => $userId,
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'start_time' => ($isIzinTengahKerja || $isIzinPulangAwal) ? $rawStartTime : null,
            'end_time'   => $isIzinTengahKerja ? $rawEndTime : null,
            'reason'     => $validated['reason'],
            'photo'      => $photoBasename,
            'status'     => $initialStatus, // Status dinamis
            'notes'      => $notes,
            'latitude'   => $validated['latitude'] ?? null,
            'longitude'  => $validated['longitude'] ?? null,
            'accuracy_m' => $validated['accuracy_m'] ?? null,
            'location_captured_at' => $validated['location_captured_at'] ?? null,
        ]);

        if ($isIzinTelat) {
            return redirect()
                ->route('leave-requests.create')
                ->with('show_izin_telat_popup', true);
        }

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function show(LeaveRequest $leave_request)
    {
        return view('leave_requests.show', ['item' => $leave_request->load('user', 'approver')]);
    }

    public function destroy(LeaveRequest $leave_request)
    {
        if (!in_array($leave_request->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return back()->with('ok', 'Hanya pengajuan yang masih pending yang bisa dihapus.');
        }

        if ($leave_request->photo) {
            Storage::disk('public')->delete('leave_photos/' . $leave_request->photo);
        }

        $leave_request->delete();
        return back()->with('ok', 'Pengajuan dihapus.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['nullable', 'string', 'max:5000'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'status'     => ['nullable', Rule::in([
                LeaveRequest::PENDING_SUPERVISOR,
                LeaveRequest::PENDING_HR,
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_REJECTED,
            ])],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
        ]);

        $user = Auth::user();
        $type = $validated['type'];

        // Logic Off SPV Update
        $isOffSpv = $type === LeaveType::OFF_SPV->value;
        if ($isOffSpv) {
            if (!$this->isSpvUser($user)) {
                return back()->withErrors('Tipe pengajuan OFF hanya tersedia untuk Supervisor.')->withInput();
            }
            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        // Update photo
        if ($request->hasFile('photo')) {
            if ($leaveRequest->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leaveRequest->photo);
            }
            
            $fullPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 
                'photo', 
                'leave_photos', 
                'leave_'
            );
            $validated['photo'] = basename($fullPath);
        }

        $leaveRequest->update($validated);

        return back()->with('success', 'Pengajuan diperbarui');
    }

    public function approve(LeaveRequest $leave_request)
    {
        $type = $leave_request->type instanceof LeaveType ? $leave_request->type->value : (string) $leave_request->type;

        if ($type === LeaveType::OFF_SPV->value) {
            $month = Carbon::parse($leave_request->start_date)->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($month);

            $approvedCount = LeaveRequest::query()
                ->where('user_id', $leave_request->user_id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->whereBetween('start_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->count();

            if ($approvedCount >= $limit) {
                return back()->withErrors("Kuota OFF Supervisor bulan {$month->format('F Y')} sudah habis.")->withInput();
            }
        }

        $leave_request->update([
            'status'      => LeaveRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('ok', 'Pengajuan disetujui.');
    }

    public function reject(LeaveRequest $leave_request)
    {
        $leave_request->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('ok', 'Pengajuan ditolak.');
    }

    // --- PRIVATE HELPERS ---

    private function isSpvUser($user): bool
    {
        if (!$user) return false;
        if (method_exists($user, 'isSupervisor')) {
            return $user->isSupervisor();
        }
        if ($user->role instanceof UserRole) {
            return $user->role === UserRole::SUPERVISOR;
        }
        $roleValue = $user->role instanceof UserRole ? $user->role->value : $user->role;
        return strtoupper((string) $roleValue) === 'SUPERVISOR';
    }

    private function offSpvMonthlyLimitByMonth(Carbon $monthStart): int
    {
        $start = $monthStart->copy()->startOfMonth()->startOfDay();
        $end = $monthStart->copy()->endOfMonth()->startOfDay();
        $fridayCount = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ((int) $cursor->dayOfWeekIso === 5) $fridayCount++;
            $cursor->addDay();
        }
        return max(0, $fridayCount - 2);
    }

    private function offSpvApprovedCountInMonth(int $userId, Carbon $monthStart): int
    {
        $start = $monthStart->copy()->startOfMonth()->toDateString();
        $end = $monthStart->copy()->endOfMonth()->toDateString();
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', LeaveType::OFF_SPV->value)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereBetween('start_date', [$start, $end])
            ->count();
    }
}