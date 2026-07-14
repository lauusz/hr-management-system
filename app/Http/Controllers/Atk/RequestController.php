<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkRequest;

class RequestController extends Controller
{
    public function index()
    {
        $requests = AtkRequest::where('user_id', auth()->id())
            ->when(request()->filled('status'), fn ($query) => $query->where('status', request()->string('status')))
            ->with('items')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('atk.requests.index', compact('requests'));
    }

    public function show(AtkRequest $atkRequest)
    {
        abort_unless($atkRequest->user_id === auth()->id() || auth()->user()->canManageAtk(), 403);

        $atkRequest->load('items.item');

        return view('atk.requests.show', compact('atkRequest'));
    }
}
