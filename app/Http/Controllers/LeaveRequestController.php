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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter; // Wajib import ini
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

        // [MODIFIKASI] Load Direct Supervisor & Manager untuk Fallback di View
        // Agar kita bisa menampilkan: "Menunggu: Nama Manager" jika SPV kosong
        $query = LeaveRequest::with(['user.directSupervisor', 'user.manager', 'approver'])
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
        $user = Auth::user(); 
        
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

        // Dropdown Approver
        $approvers = collect([]);
        $roleStr = $this->getRoleString($user);

        if ($roleStr === 'EMPLOYEE') {
            $approvers = User::where('role', UserRole::SUPERVISOR)
                ->where('id', '!=', $userId)
                ->orderBy('name')
                ->get();
        } elseif ($roleStr === 'SUPERVISOR') {
            $approvers = User::whereIn('role', [UserRole::MANAGER])
                ->where('id', '!=', $userId)
                ->orderBy('name')
                ->get();
        }

        return view('leave_requests.create', [
            'shiftEndTime' => $shiftEndTime,
            'canOffSpv'    => $canOffSpv,
            'offSpvInfo'   => $offInfo,
            'managers'     => $approvers, 
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id();

        // =========================================================
        // [MODIFIKASI 1] RATE LIMITER (JEDA 30 MENIT)
        // =========================================================
        $throttleKey = 'submit_izin_' . $userId;
        
        // Cek apakah user sedang dalam masa tunggu?
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $nextTime = Carbon::now()->addSeconds($seconds)->format('H:i');
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => "Anda baru saja melakukan pengajuan. Untuk mencegah data ganda, mohon tunggu 30 menit. Silakan coba lagi pada pukul $nextTime."]);
        }

        // =========================================================
        // [STANDARD] VALIDASI INPUT
        // =========================================================
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

            'substitute_pic' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::requiredIf(fn() => in_array($request->type, [
                    LeaveType::CUTI->value, 
                    LeaveType::CUTI_KHUSUS->value, 
                    LeaveType::SAKIT->value
                ]))
            ],
            'substitute_phone' => [
                'nullable', 
                'string', 
                'max:50',
                Rule::requiredIf(fn() => in_array($request->type, [
                    LeaveType::CUTI->value, 
                    LeaveType::CUTI_KHUSUS->value, 
                    LeaveType::SAKIT->value
                ]))
            ],
            'special_leave_detail' => [
                'nullable',
                'string',
                Rule::requiredIf(fn() => $request->type === LeaveType::CUTI_KHUSUS->value)
            ],
        ]);

        $type = $validated['type'];

        // =========================================================
        // [MODIFIKASI 2] CEK DUPLIKASI DATA (LAPIS KE-2)
        // =========================================================
        // Mencegah input tanggal yang sama persis jika user berhasil bypass timer
        $isDuplicate = LeaveRequest::where('user_id', $userId)
            ->where('type', $type)
            ->whereDate('start_date', $validated['start_date']) // Cek start date sama
            ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, 'BATAL']) // Abaikan yg sudah ditolak/batal
            ->exists();

        if ($isDuplicate) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Anda sudah memiliki pengajuan aktif (Pending/Disetujui) pada tanggal mulai tersebut. Cek Riwayat Pengajuan Anda.']);
        }

        // =========================================================
        // LOGIC NOTES & CUTI KHUSUS
        // =========================================================
        $notesParts = [];

        if ($type === LeaveType::CUTI_KHUSUS->value) {
            $category = $validated['special_leave_detail'];
            
            $limits = [
                'NIKAH_KARYAWAN'   => 4,
                'ISTRI_MELAHIRKAN' => 2,
                'ISTRI_KEGUGURAN'  => 2,
                'KHITANAN_ANAK'    => 2,
                'PEMBAPTISAN_ANAK' => 2,
                'NIKAH_ANAK'       => 2,
                'DEATH_EXTENDED'   => 2,
                'DEATH_CORE'       => 2,
                'DEATH_HOUSE'      => 1,
                'HAJI'             => 40,
                'UMROH'            => 14,
            ];

            $labels = [
                'NIKAH_KARYAWAN' => 'Menikah',
                'ISTRI_MELAHIRKAN' => 'Istri Melahirkan',
            ];

            $maxDays = $limits[$category] ?? 0;
            $catName = $labels[$category] ?? $category;
            
            $startDate = Carbon::parse($validated['start_date']);
            $endDate   = Carbon::parse($validated['end_date']);
            $diffDays  = $startDate->diffInDays($endDate) + 1; 

            if ($maxDays > 0 && $diffDays > $maxDays) {
                $notesParts[] = "Durasi pengajuan {$diffDays} hari melebihi batas maksimal {$maxDays} hari untuk kategori {$catName}.";
            }
        }

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

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            $alreadyInWeek = LeaveRequest::query()
                ->where('user_id', $userId)
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', '!=', LeaveRequest::STATUS_REJECTED)
                ->where('status', '!=', 'BATAL')
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

        if ($isIzinTelat && !$rawStartTime) {
            return back()->withErrors('Estimasi jam tiba wajib diisi.')->withInput();
        }

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

        // --- PENENTUAN ATASAN ---
        $inputApproverId = $request->input('manager_id'); 
        $roleStr = $this->getRoleString($user);
        
        $finalApproverId = null;
        $initialStatus = LeaveRequest::PENDING_HR; 

        if ($roleStr === 'EMPLOYEE') {
            if (!empty($inputApproverId)) {
                $finalApproverId = $inputApproverId;
                $user->update(['direct_supervisor_id' => $finalApproverId]); 
            } elseif (!empty($user->direct_supervisor_id)) {
                $finalApproverId = $user->direct_supervisor_id;
            }
            if ($finalApproverId) $initialStatus = LeaveRequest::PENDING_SUPERVISOR; 

        } elseif ($roleStr === 'SUPERVISOR') {
            if (!empty($inputApproverId)) {
                $finalApproverId = $inputApproverId;
                $user->update(['manager_id' => $finalApproverId]); 
            } elseif (!empty($user->manager_id)) {
                $finalApproverId = $user->manager_id;
            }
            if ($finalApproverId) $initialStatus = LeaveRequest::PENDING_SUPERVISOR; 
        }

        // --- CREATE DATA ---
        LeaveRequest::create([
            'user_id'    => $userId,
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'start_time' => ($isIzinTengahKerja || $isIzinPulangAwal || $isIzinTelat) ? $rawStartTime : null,
            'end_time'   => $isIzinTengahKerja ? $rawEndTime : null,
            'reason'     => $validated['reason'],
            'photo'      => $photoBasename,
            'status'     => $initialStatus,
            'notes'      => $notes, 
            'latitude'   => $validated['latitude'] ?? null,
            'longitude'  => $validated['longitude'] ?? null,
            'accuracy_m' => $validated['accuracy_m'] ?? null,
            'location_captured_at' => $validated['location_captured_at'] ?? null,
            'substitute_pic'   => $validated['substitute_pic'] ?? null,
            'substitute_phone' => $validated['substitute_phone'] ?? null,
            'special_leave_category' => $validated['special_leave_detail'] ?? null,
        ]);

        // =========================================================
        // [MODIFIKASI 3] AKTIFKAN TIMER (LOCK 30 MENIT)
        // =========================================================
        // Data sukses tersimpan, kunci user ini selama 1800 detik (30 menit)
        RateLimiter::hit($throttleKey, 1800);

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

    // [MODIFIKASI BESAR] GOD MODE UPDATE
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        // 1. Cek Hak Akses
        $isOwner = $user->id === $leaveRequest->user_id;
        $roleStr = $this->getRoleString($user);
        $isHRD   = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) {
            abort(403, 'Anda tidak berhak mengubah data ini.');
        }

        if ($isOwner && !$isHRD) {
            if (!in_array($leaveRequest->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR])) {
                return back()->withErrors('Pengajuan sudah diproses, tidak dapat diubah sendiri. Hubungi atasan.');
            }
        }

        // 2. Validasi Lengkap (Bisa Edit Semua Field)
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
        ]);

        // 3. Logic Pembersihan Data saat Ganti Tipe
        if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
            // Ambil dari input baru, atau pakai yang lama jika input baru kosong (fallback)
            $validated['special_leave_category'] = $validated['special_leave_detail'] ?? $leaveRequest->special_leave_category;
        } else {
            // Jika bukan cuti khusus, kosongkan kategori
            $validated['special_leave_category'] = null;
        }
        unset($validated['special_leave_detail']); 

        // Handle Jam jika ganti ke tipe non-waktu (misal dari Telat ke Sakit)
        $isTimeBased = in_array($validated['type'], [
            LeaveType::IZIN_TELAT->value,
            LeaveType::IZIN_TENGAH_KERJA->value,
            LeaveType::IZIN_PULANG_AWAL->value,
            LeaveType::IZIN->value 
        ]);

        if (!$isTimeBased) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        // Handle File Foto (Opsional, jika ada upload baru di edit)
        if ($request->hasFile('photo')) {
            if ($leaveRequest->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leaveRequest->photo);
            }
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

        $leaveRequest->update(['status' => 'BATAL']);

        if ($isHRD && !$isOwner) {
            return redirect()->route('hr.leave.index')->with('success', 'Pengajuan berhasil diubah status menjadi BATAL.');
        }

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    // --- Private Helpers ---

    private function getRoleString($user) {
        if (!$user) return '';
        return strtoupper((string) ($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role));
    }

    private function isSpvUser($user): bool
    {
        if (!$user) return false;
        if (method_exists($user, 'isSupervisor')) {
            return $user->isSupervisor();
        }
        $roleStr = $this->getRoleString($user);
        return $roleStr === 'SUPERVISOR';
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