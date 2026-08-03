<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Models\AtkRequest;

class RequestController extends Controller
{
    public function index()
    {
        $requests = AtkRequest::query()
            ->forModule(AtkRequest::MODULE_OPS)
            ->where('user_id', auth()->id())
            ->when(request()->filled('status'), fn ($query) => $query->where('status', request()->string('status')))
            ->with('items')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('ops.requests.index', compact('requests'));
    }

    public function show(AtkRequest $atkRequest)
    {
        abort_unless($atkRequest->module === AtkRequest::MODULE_OPS, 404);
        abort_unless($atkRequest->user_id === auth()->id() || auth()->user()->canManageOps(), 403);

        $atkRequest->load('items.item');

        return view('ops.requests.show', compact('atkRequest'));
    }
}
