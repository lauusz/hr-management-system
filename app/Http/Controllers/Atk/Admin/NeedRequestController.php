<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkNeedRequest;
use Illuminate\Http\Request;

class NeedRequestController extends Controller
{
    public function index(Request $request)
    {
        $needRequests = AtkNeedRequest::with('item')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->where(function ($sub) use ($keyword): void {
                    $sub->where('requested_item_name', 'like', $keyword)
                        ->orWhere('user_name_snapshot', 'like', $keyword)
                        ->orWhere('pt_name_snapshot', 'like', $keyword)
                        ->orWhere('reason', 'like', $keyword);
                });
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [AtkNeedRequest::STATUS_PENDING])
            ->latest()
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

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
