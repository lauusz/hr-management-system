<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkNeedRequest;
use Illuminate\Http\Request;

class NeedRequestController extends Controller
{
    public function index()
    {
        $needRequests = AtkNeedRequest::with('item')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('atk.need_requests.index', compact('needRequests'));
    }

    public function create(Request $request)
    {
        return view('atk.need_requests.create', [
            'item' => $request->filled('item') ? AtkItem::find($request->integer('item')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'atk_item_id' => ['nullable', 'exists:atk_items,id'],
            'requested_item_name' => ['required', 'string', 'max:150'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_name' => ['required', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $pt = $user->pt;

        AtkNeedRequest::create($validated + [
            'user_id' => $user->id,
            'user_name_snapshot' => $user->name,
            'pt_id' => $pt?->id,
            'pt_name_snapshot' => $pt?->name,
            'status' => 'PENDING',
        ]);

        return redirect()->route('v2.atk.catalog')->with('success', 'Pengajuan kebutuhan barang berhasil dikirim.');
    }
}
