<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkNeedRequest;
use Illuminate\Http\Request;

class NeedRequestController extends Controller
{
    public function index()
    {
        $needRequests = AtkNeedRequest::latest()->paginate(20);

        return view('atk.admin.need_requests.index', compact('needRequests'));
    }

    public function process(Request $request, AtkNeedRequest $needRequest)
    {
        // Guard dobel-proses: hanya need-request PENDING yang boleh diproses.
        if ($needRequest->status !== AtkNeedRequest::STATUS_PENDING) {
            return redirect()
                ->route('v2.atk.admin.need-requests.index')
                ->with('warning', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:'.AtkNeedRequest::STATUS_DONE.','.AtkNeedRequest::STATUS_REJECTED],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $needRequest->update([
            'status' => $validated['status'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        $message = $validated['status'] === AtkNeedRequest::STATUS_DONE
            ? 'Pengajuan barang diselesaikan.'
            : 'Pengajuan barang ditolak.';

        return redirect()
            ->route('v2.atk.admin.need-requests.index')
            ->with('success', $message);
    }
}
