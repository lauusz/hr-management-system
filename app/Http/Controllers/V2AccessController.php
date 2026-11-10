<?php

namespace App\Http\Controllers;

use App\Models\InvPesonaAccessUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class V2AccessController extends Controller
{
    public function __invoke(Request $request)
    {
        $hasPesonaAccess = Schema::hasTable('inv_pesona_access_users')
            && InvPesonaAccessUser::query()->where('user_id', $request->user()->id)->exists();

        return view('v2.access', compact('hasPesonaAccess'));
    }
}
