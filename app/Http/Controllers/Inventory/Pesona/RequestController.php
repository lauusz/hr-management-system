<?php

namespace App\Http\Controllers\Inventory\Pesona;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function index(Request $request): View
    {
        if (is_string($request->input('q'))) {
            $request->merge(['q' => trim($request->input('q'))]);
        }
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = $validated['q'] ?? '';
        $status = $request->query('status');
        $status = in_array($status, ['PENDING', 'APPROVED', 'REJECTED', 'PARTIAL'], true) ? $status : '';

        $requests = InvPesonaRequest::query()->where('user_id', $request->user()->id)
            ->select('id', 'user_id', 'request_number', 'pt_name_snapshot', 'nopol_snapshot', 'created_at', 'status')
            ->with(['items:id,inv_pesona_request_id,item_name_snapshot,qty,unit_name_snapshot'])
            ->withCount('items')->withMin('items', 'item_name_snapshot')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where('request_number', 'like', '%'.$search.'%'))
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(15)
            ->appends(['q' => $search, 'status' => $status]);
        $hasRequests = $requests->total() > 0
            || (($search !== '' || $status !== '') && InvPesonaRequest::query()->where('user_id', $request->user()->id)->exists());

        return view('inventory.pesona.requests.index', compact('requests', 'search', 'status', 'hasRequests'));
    }

    public function show(Request $request, InvPesonaRequest $pesonaRequest): View
    {
        abort_unless((string) $pesonaRequest->user_id === (string) $request->user()->id, 404);
        $pesonaRequest->load(['approvedBy:id,name', 'rejectedBy:id,name', 'items.reviewedBy:id,name']);

        return view('inventory.pesona.requests.show', compact('pesonaRequest'));
    }
}
