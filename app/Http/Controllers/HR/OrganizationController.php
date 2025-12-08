<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;

class OrganizationController extends Controller
{
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.organization.index', [
            'divisions' => $divisions,
            'positions' => $positions,
        ]);
    }
}
