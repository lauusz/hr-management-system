<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkNeedRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NeedRequestController extends Controller
{
    public function index()
    {
        $needRequests = AtkNeedRequest::with('item')
            ->forModule(AtkNeedRequest::MODULE_OPS)
            ->latest()
            ->paginate(20);

        return view('ops.admin.need_requests.index', compact('needRequests'));
    }

    public function create(Request $request)
    {
        return view('ops.admin.need_requests.create', [
            'item' => $request->filled('item')
                ? AtkItem::forModule(AtkItem::MODULE_OPS)->available()->find($request->integer('item'))
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'atk_item_id' => ['nullable', Rule::exists('atk_items', 'id')->where('module', AtkItem::MODULE_OPS)->whereNull('deleted_at')],
            'requested_item_name' => ['required', 'string', 'max:150'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_name' => ['required', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $pt = $user->pt;

        AtkNeedRequest::create($validated + [
            'module' => AtkNeedRequest::MODULE_OPS,
            'user_id' => $user->id,
            'user_name_snapshot' => $user->name,
            'pt_id' => $pt?->id,
            'pt_name_snapshot' => $pt?->name,
            'status' => AtkNeedRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('v2.ops.admin.need-requests.index')
            ->with('success', 'Request barang berhasil dikirim ke Admin ATK.');
    }
}
