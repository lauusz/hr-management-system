<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
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
    protected ImageCompressor $imageCompressor;

    public function __construct(ImageCompressor $imageCompressor)
    {
        $this->imageCompressor = $imageCompressor;
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
        $this->authorize('create', LeaveRequest::class);

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
        ]);

        $user = Auth::user();
        $userId = Auth::id();
        $type = $validated['type'];

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
                ->exists();

            if ($alreadyInWeek) {
                return back()->withErrors('Pengajuan OFF Supervisor maksimal 1 kali dalam 1 minggu.')->withInput();
            }

            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);

        $notesParts = [];

        if ($type === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) {
                $notesParts[] = "Pengajuan dilakukan {$daysDiff} hari sebelum tanggal mulai cuti (kurang dari H-7). Pengajuan tetap bisa diproses, namun akan ada potongan sesuai kebijakan perusahaan.";
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

        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal  = $type === LeaveType::IZIN_PULANG_AWAL->value;

        $rawStartTime = $request->input('start_time');
        $rawEndTime   = $request->input('end_time');

        if ($isIzinTengahKerja) {
            if (!$rawStartTime || !$rawEndTime) {
                return back()->withErrors('Jam mulai dan jam selesai wajib diisi untuk izin tengah kerja.')->withInput();
            }

            try {
                $startTimeObj = Carbon::createFromFormat('H:i', $rawStartTime);
                $endTimeObj   = Carbon::createFromFormat('H:i', $rawEndTime);
            } catch (\Exception $e) {
                return back()->withErrors('Format jam tidak valid.')->withInput();
            }

            if ($endTimeObj->lessThanOrEqualTo($startTimeObj)) {
                return back()->withErrors('Jam selesai harus lebih besar dari jam mulai.')->withInput();
            }
        }

        if ($isIzinPulangAwal) {
            if (!$rawStartTime) {
                return back()->withErrors('Jam pulang wajib diisi untuk izin pulang awal.')->withInput();
            }

            $izinDate = Carbon::parse($validated['start_date']);
            $dayOfWeek = (int) $izinDate->dayOfWeekIso;

            $employeeShift = EmployeeShift::where('user_id', $userId)->first();

            $shiftEndRaw = null;

            if ($employeeShift && $employeeShift->shift_id) {
                $shiftDay = ShiftDay::where('shift_id', $employeeShift->shift_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_holiday', false)
                    ->first();

                if ($shiftDay && $shiftDay->end_time) {
                    $shiftEndRaw = $shiftDay->end_time;
                }
            }

            if (!$shiftEndRaw) {
                return back()->withErrors('Konfigurasi jam pulang shift tidak valid, hubungi HRD.')->withInput();
            }

            try {
                $reqTimeObj = Carbon::createFromFormat('H:i', $rawStartTime);

                if ($shiftEndRaw instanceof Carbon) {
                    $shiftTimeObj = $shiftEndRaw->copy();
                } else {
                    $format = strlen($shiftEndRaw) === 5 ? 'H:i' : 'H:i:s';
                    $shiftTimeObj = Carbon::createFromFormat($format, $shiftEndRaw);
                }

                $reqTimeObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);
                $shiftTimeObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);

                $diffMinutes = $reqTimeObj->diffInMinutes($shiftTimeObj, false);
            } catch (\Throwable $e) {
                return back()->withErrors('Format jam pulang shift tidak valid, hubungi HRD.')->withInput();
            }

            if ($diffMinutes <= 0) {
                return back()->withErrors('Jam pulang izin harus sebelum jam pulang shift.')->withInput();
            }

            if ($diffMinutes > 60) {
                return back()->withErrors('Waktu izin pulang awal maksimal 1 jam sebelum jam pulang shift.')->withInput();
            }
        }

        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $photoBasename = $this->imageCompressor
                ->storeLeaveSupportingFile($request->file('photo'), $isIzinTelat);
        }

        $initialStatus = match ($type) {
            LeaveType::CUTI->value,
            LeaveType::CUTI_KHUSUS->value => LeaveRequest::PENDING_SUPERVISOR,
            default => LeaveRequest::PENDING_HR,
        };

        LeaveRequest::create([
            'user_id'    => $userId,
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'start_time' => ($isIzinTengahKerja || $isIzinPulangAwal) ? $rawStartTime : null,
            'end_time'   => $isIzinTengahKerja ? $rawEndTime : null,
            'reason'     => $validated['reason'],
            'photo'      => $photoBasename,
            'status'     => $initialStatus,
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
        $this->authorize('view', $leave_request);
        return view('leave_requests.show', ['item' => $leave_request->load('user', 'approver')]);
    }

    public function destroy(LeaveRequest $leave_request)
    {
        $this->authorize('delete', $leave_request);

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

        $type = $validated['type'];
        $isOffSpv = $type === LeaveType::OFF_SPV->value;

        if ($isOffSpv) {
            $user = Auth::user();
            if (!$this->isSpvUser($user)) {
                return back()->withErrors('Tipe pengajuan OFF hanya tersedia untuk Supervisor.')->withInput();
            }

            $monthRef = Carbon::parse($validated['start_date'])->startOfMonth();
            $limit = $this->offSpvMonthlyLimitByMonth($monthRef);
            $approvedCount = $this->offSpvApprovedCountInMonth((int) $leaveRequest->user_id, $monthRef);
            $remaining = max(0, $limit - $approvedCount);

            if ($leaveRequest->status !== LeaveRequest::STATUS_APPROVED && $remaining <= 0) {
                return back()->withErrors('Kuota OFF Supervisor bulan ini sudah habis.')->withInput();
            }

            $validated['end_date'] = $validated['start_date'];
            $validated['start_time'] = null;
            $validated['end_time'] = null;

            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            $existsOther = LeaveRequest::query()
                ->where('id', '!=', $leaveRequest->id)
                ->where('user_id', $leaveRequest->user_id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', '!=', LeaveRequest::STATUS_REJECTED)
                ->exists();

            if ($existsOther) {
                return back()->withErrors('Pengajuan OFF Supervisor maksimal 1 kali dalam 1 minggu.')->withInput();
            }
        }

        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        $isIzinTengahKerja = $type === LeaveType::IZIN_TENGAH_KERJA->value;
        $isIzinPulangAwal  = $type === LeaveType::IZIN_PULANG_AWAL->value;

        $rawStartTime = $request->input('start_time');
        $rawEndTime   = $request->input('end_time');

        if ($isIzinTengahKerja) {
            if (!$rawStartTime || !$rawEndTime) {
                return back()->withErrors('Jam mulai dan jam selesai wajib diisi untuk izin tengah kerja.')->withInput();
            }

            try {
                $startTimeObj = Carbon::createFromFormat('H:i', $rawStartTime);
                $endTimeObj   = Carbon::createFromFormat('H:i', $rawEndTime);
            } catch (\Exception $e) {
                return back()->withErrors('Format jam tidak valid.')->withInput();
            }

            if ($endTimeObj->lessThanOrEqualTo($startTimeObj)) {
                return back()->withErrors('Jam selesai harus lebih besar dari jam mulai.')->withInput();
            }

            $validated['start_time'] = $rawStartTime;
            $validated['end_time']   = $rawEndTime;
        } elseif ($isIzinPulangAwal) {
            if (!$rawStartTime) {
                return back()->withErrors('Jam pulang wajib diisi untuk izin pulang awal.')->withInput();
            }

            $izinDate = Carbon::parse($validated['start_date']);
            $dayOfWeek = (int) $izinDate->dayOfWeekIso;

            $employeeShift = EmployeeShift::where('user_id', $leaveRequest->user_id)->first();

            $shiftEndRaw = null;

            if ($employeeShift && $employeeShift->shift_id) {
                $shiftDay = ShiftDay::where('shift_id', $employeeShift->shift_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_holiday', false)
                    ->first();

                if ($shiftDay && $shiftDay->end_time) {
                    $shiftEndRaw = $shiftDay->end_time;
                }
            }

            if (!$shiftEndRaw) {
                return back()->withErrors('Konfigurasi jam pulang shift tidak valid, hubungi HRD.')->withInput();
            }

            try {
                $reqTimeObj = Carbon::createFromFormat('H:i', $rawStartTime);

                if ($shiftEndRaw instanceof Carbon) {
                    $shiftTimeObj = $shiftEndRaw->copy();
                } else {
                    $format = strlen($shiftEndRaw) === 5 ? 'H:i' : 'H:i:s';
                    $shiftTimeObj = Carbon::createFromFormat($format, $shiftEndRaw);
                }

                $reqTimeObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);
                $shiftTimeObj->setDate($izinDate->year, $izinDate->month, $izinDate->day);

                $diffMinutes = $reqTimeObj->diffInMinutes($shiftTimeObj, false);
            } catch (\Throwable $e) {
                return back()->withErrors('Format jam pulang shift tidak valid, hubungi HRD.')->withInput();
            }

            if ($diffMinutes <= 0) {
                return back()->withErrors('Jam pulang izin harus sebelum jam pulang shift.')->withInput();
            }

            if ($diffMinutes > 60) {
                return back()->withErrors('Waktu izin pulang awal maksimal 1 jam sebelum jam pulang shift.')->withInput();
            }

            $validated['start_time'] = $rawStartTime;
            $validated['end_time']   = null;
        } else {
            $validated['start_time'] = null;
            $validated['end_time']   = null;
        }

        if ($request->hasFile('photo')) {
            if ($leaveRequest->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leaveRequest->photo);
            }
            $validated['photo'] = $this->imageCompressor
                ->storeLeaveSupportingFile($request->file('photo'), $isIzinTelat);
        }

        $leaveRequest->update($validated);

        return back()->with('success', 'Pengajuan diperbarui');
    }

    public function approve(LeaveRequest $leave_request)
    {
        $this->authorize('approve', $leave_request);

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
        $this->authorize('approve', $leave_request);

        $leave_request->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('ok', 'Pengajuan ditolak.');
    }

    private function isSpvUser($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSupervisor')) {
            try {
                return (bool) $user->isSupervisor();
            } catch (\Throwable $e) {
            }
        }

        if (method_exists($user, 'isSpv')) {
            try {
                return (bool) $user->isSpv();
            } catch (\Throwable $e) {
            }
        }

        $role = null;
        if (isset($user->role)) {
            $role = is_string($user->role) ? $user->role : (string) $user->role;
        }

        $role = strtoupper((string) $role);

        return in_array($role, ['SUPERVISOR', 'SPV'], true);
    }

    private function offSpvMonthlyLimitByMonth(Carbon $monthStart): int
    {
        $start = $monthStart->copy()->startOfMonth()->startOfDay();
        $end = $monthStart->copy()->endOfMonth()->startOfDay();

        $fridayCount = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ((int) $cursor->dayOfWeekIso === 5) {
                $fridayCount++;
            }
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
