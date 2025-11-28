<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
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

        $submittedDate = $request->query('submitted_date');
        if ($submittedDate) {
            try {
                $start = Carbon::parse($submittedDate)->startOfDay();
                $end = (clone $start)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } catch (\Exception $e) {
                $submittedDate = null;
            }
        }

        $items = $query->paginate(100)->appends([
            'type' => $typeFilter,
            'submitted_date' => $submittedDate,
        ]);

        return view('leave_requests.index', [
            'items'         => $items,
            'typeFilter'    => $typeFilter,
            'typeOptions'   => LeaveType::cases(),
            'submittedDate' => $submittedDate,
        ]);
    }

    public function create()
    {
        $this->authorize('create', LeaveRequest::class);
        return view('leave_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['required', 'string'],
            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);

        $notes = null;

        if ($validated['type'] === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) {
                $notes = "Pengajuan dilakukan {$daysDiff} hari sebelum tanggal mulai cuti (kurang dari H-7). Pengajuan tetap bisa diproses, namun akan ada potongan sesuai kebijakan perusahaan.";
            }
        }

        $isIzinTelat = $validated['type'] === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $stored = $request->file('photo')->store('leave_photos', 'public');
            $photoBasename = basename($stored);
        }

        $type = $validated['type'];
        $initialStatus = match ($type) {
            LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value => LeaveRequest::PENDING_SUPERVISOR,
            default => LeaveRequest::PENDING_HR,
        };

        LeaveRequest::create([
            'user_id'    => Auth::id(),
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'reason'     => $validated['reason'],
            'photo'      => $photoBasename,
            'status'     => $initialStatus,
            'notes'      => $notes,
            'latitude'   => $validated['latitude'] ?? null,
            'longitude'  => $validated['longitude'] ?? null,
            'accuracy_m' => $validated['accuracy_m'] ?? null,
            'location_captured_at' => $validated['location_captured_at'] ?? now(),
        ]);

        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;

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
            'reason'     => ['nullable', 'string', 'max:5000'],
            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
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

        $isIzinTelat = $validated['type'] === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        if ($request->hasFile('photo')) {
            $stored = $request->file('photo')->store('leave_photos', 'public');
            $validated['photo'] = basename($stored);
        }

        $leaveRequest->update($validated);

        return back()->with('success', 'Pengajuan diperbarui');
    }

    public function approve(LeaveRequest $leave_request)
    {
        $this->authorize('approve', $leave_request);

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
}
