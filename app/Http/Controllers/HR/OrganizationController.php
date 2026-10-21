<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::with('supervisor')->orderBy('name')->get();
        $positions = Position::with('division')->orderBy('name')->get();

        $requestedTab = strtolower((string) $request->query('tab', ''));
        $activeTab = in_array($requestedTab, ['positions', 'jabatan'], true) ? 'jabatan' : 'divisi';

        return view('hr.organization.index', [
            'divisions' => $divisions,
            'positions' => $positions,
            'activeTab' => $activeTab,
        ]);
    }
}
