<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestAttachment;
use App\Services\OvertimeEvidenceStorage;
use App\Services\OvertimeApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\HeaderUtils;

class OvertimeRequestController extends Controller
{
    /**
     * Menampilkan daftar pengajuan lembur saya.
     */
    public function index()
    {
        $userId = Auth::id();
        
        $overtimes = OvertimeRequest::with(['attachments', 'approvalActions', 'user.overtimeApproverAssignment.supervisor', 'user.overtimeApproverAssignment.manager'])->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('overtime_requests.index', compact('overtimes'));
    }

    /**
     * Menampilkan detail pengajuan lembur milik pengguna yang sedang masuk.
     */
    public function show(OvertimeRequest $overtimeRequest)
    {
        if ((int) $overtimeRequest->user_id !== (int) Auth::id()) {
            return redirect()->route('overtime-requests.index')
                ->with('error', 'Anda tidak berhak melihat data pengajuan ini.');
        }

        $overtimeRequest->load([
            'attachments',
            'approvalActions',
            'user.overtimeApproverAssignment.supervisor',
            'user.overtimeApproverAssignment.manager',
        ]);

        return view('overtime_requests.show', ['overtime' => $overtimeRequest]);
    }

    /**
     * Form perubahan pengajuan lembur yang masih menunggu persetujuan SPV.
     */
    public function edit(OvertimeRequest $overtimeRequest)
    {
        if ((int) $overtimeRequest->user_id !== (int) Auth::id()) {
            return redirect()->route('overtime-requests.index')
                ->with('error', 'Anda tidak berhak mengubah data pengajuan ini.');
        }

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            return redirect()->route('overtime-requests.index')
                ->with('error', 'Pengajuan sudah diproses SPV, tidak dapat diubah.');
        }

        return view('overtime_requests.edit', ['overtime' => $overtimeRequest]);
    }

    /**
     * Form pengajuan lembur baru.
     */
    public function create()
    {
        return view('overtime_requests.create');
    }

    /**
     * Simpan pengajuan lembur.
     */
    public function store(Request $request, OvertimeEvidenceStorage $evidenceStorage)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'break_hours' => 'nullable|integer|min:0|max:23',
            'break_minutes' => 'nullable|integer|min:0|max:59',
            'description' => 'required|string|max:5000',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'file|mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx|max:8192',
        ], [
            'end_time.different' => 'Jam selesai lembur tidak boleh sama dengan jam mulai.',
            'photos.max' => 'Maksimal 3 bukti pendukung per pengajuan.',
            'photos.*.max' => 'Ukuran tiap file bukti pendukung tidak boleh lebih dari 8 MB.',
            'photos.*.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak lebih dari 8 MB.',
            'photos.*.mimes' => 'Format file bukti pendukung tidak didukung.',
            'photos.*.file' => 'File bukti pendukung tidak valid.',
        ]);

        $durationMinutes = $this->calculateDurationMinutes($validated['start_time'], $validated['end_time']);
        $breakMinutes = $this->calculateBreakMinutes($validated);
        $this->validateBreakDuration($breakMinutes, $durationMinutes);

        $files = [];
        try {
            foreach ($request->file('photos', []) as $file) {
                $files[] = $evidenceStorage->store($file);
            }

            DB::transaction(function () use ($user, $validated, $durationMinutes, $breakMinutes, $files) {
                $overtime = OvertimeRequest::create([
                    'user_id' => $user->id,
                    'overtime_date' => $validated['overtime_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'duration_minutes' => $durationMinutes,
                    'break_minutes' => $breakMinutes,
                    'description' => $validated['description'],
                    'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR,
                ]);
                foreach ($files as $file) {
                    $overtime->attachments()->create($file);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($files as $file) {
                try {
                    $evidenceStorage->delete($file['file_path']);
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()->withInput()->with('error', 'Pengajuan atau bukti lembur gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Pengajuan lembur berhasil dibuat.');
    }

    public function attachment(OvertimeRequest $overtimeRequest, OvertimeRequestAttachment $attachment, OvertimeEvidenceStorage $evidenceStorage)
    {
        abort_unless((int) $attachment->overtime_request_id === (int) $overtimeRequest->id, 404);
        abort_unless($overtimeRequest->canViewEvidence(Auth::user()), 403);
        abort_unless(Storage::disk('overtime_evidence')->exists($attachment->file_path), 404);

        $mime = in_array($attachment->mime_type, ['image/jpeg', 'application/pdf'], true)
            ? $attachment->mime_type : 'application/octet-stream';
        $name = basename(str_replace('\\', '/', $attachment->original_name)) ?: 'bukti';
        if ($mime === 'image/jpeg') {
            $name = pathinfo($name, PATHINFO_FILENAME).'.jpg';
        }
        $fallback = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?: 'bukti';
        $response = response($evidenceStorage->read($attachment->file_path), 200, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            $mime === 'application/octet-stream' ? 'attachment' : 'inline', $name, $fallback
        ));

        return $response;
    }
    public function update(Request $request, OvertimeRequest $overtimeRequest, OvertimeApprovalService $approvalService)
    {
        $user = Auth::user();
        $isOwner = (int) $user->id === (int) $overtimeRequest->user_id;

        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isHRD = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengubah data ini.');
        }

        if ($isOwner) {
            if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat diubah sendiri. Hubungi supervisor atau HRD untuk koreksi data.');
            }
        }

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'break_hours' => 'nullable|integer|min:0|max:23',
            'break_minutes' => 'nullable|integer|min:0|max:59',
            'description' => 'required|string|max:5000',
        ], [
            'end_time.different' => 'Jam selesai lembur tidak boleh sama dengan jam mulai.',
        ]);

        $durationMinutes = $this->calculateDurationMinutes($validated['start_time'], $validated['end_time']);
        $breakMinutes = $this->calculateBreakMinutes($validated);
        $this->validateBreakDuration($breakMinutes, $durationMinutes);

        try {
            $approvalService->locked($overtimeRequest, function ($locked) use ($isOwner, $user, $validated, $durationMinutes, $breakMinutes, $approvalService) {
                if ($isOwner && $locked->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                    throw new \DomainException('Pengajuan sudah diproses, tidak dapat diubah sendiri.');
                }
                $changed = $locked->overtime_date->format('Y-m-d') !== $validated['overtime_date']
                    || $locked->start_time->format('H:i') !== $validated['start_time']
                    || $locked->end_time->format('H:i') !== $validated['end_time']
                    || (int) ($locked->break_minutes ?? 0) !== $breakMinutes
                    || $locked->description !== $validated['description'];
                if ($changed && $locked->status === OvertimeRequest::STATUS_PENDING_SUPERVISOR && $locked->currentApprovalActions()->contains('action', 'APPROVE')) {
                    if ($isOwner) {
                        throw new \DomainException('Pengajuan tidak dapat diubah setelah ada persetujuan.');
                    }
                    $approvalService->resetIfApproved($locked, $user);
                }
                $locked->update([
                    'overtime_date' => $validated['overtime_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'duration_minutes' => $durationMinutes,
                    'break_minutes' => $breakMinutes,
                    'description' => $validated['description'],
                ]);
            });
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($isOwner) {
            return redirect()->route('overtime-requests.index')
                ->with('success', 'Data lembur berhasil diperbarui.');
        }

        return back()->with('success', 'Data lembur berhasil diperbarui.');
    }

    private function calculateDurationMinutes(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', '2000-01-01 '.$startTime);
        $end = Carbon::createFromFormat('Y-m-d H:i', '2000-01-01 '.$endTime);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }

    private function calculateBreakMinutes(array $validated): int
    {
        return ((int) ($validated['break_hours'] ?? 0) * 60) + (int) ($validated['break_minutes'] ?? 0);
    }

    private function validateBreakDuration(int $breakMinutes, int $durationMinutes): void
    {
        if ($breakMinutes >= $durationMinutes) {
            throw ValidationException::withMessages([
                'break_hours' => 'Istirahat harus lebih singkat dari durasi lembur.',
            ]);
        }
    }

    public function destroy(OvertimeRequest $overtimeRequest, OvertimeApprovalService $approvalService)
    {
        $user = Auth::user();
        $isOwner = (int) $user->id === (int) $overtimeRequest->user_id;
        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isHRD = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) {
            return redirect()->back()->with('error', 'Anda tidak berhak menghapus data ini.');
        }

        // Owner hanya bisa hapus jika status masih pending supervisor
        if ($isOwner) {
             if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                return back()->with('error', 'Hanya pengajuan yang belum diproses atasan yang bisa dibatalkan.');
            }
        }
        
        // Update status to CANCELLED instead of deleting
        try {
            $approvalService->locked($overtimeRequest, function ($locked) use ($isOwner) {
                if ($isOwner && $locked->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                    throw new \DomainException('Hanya pengajuan yang masih menunggu yang dapat dibatalkan.');
                }
                $locked->update(['status' => OvertimeRequest::STATUS_CANCELLED]);
            });
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($isHRD && !$isOwner) return redirect()->route('hr.overtime-requests.master')->with('success', 'Pengajuan lembur berhasil dibatalkan oleh HRD.');
        return redirect()->route('overtime-requests.index')->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }
}
