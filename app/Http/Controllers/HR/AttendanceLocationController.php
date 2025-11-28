<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use Illuminate\Http\Request;

class AttendanceLocationController extends Controller
{
    public function index()
    {
        $items = AttendanceLocation::orderBy('name')->paginate(100);

        return view('hr.locations.index', compact('items'));
    }

    public function create()
    {
        return view('hr.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'latitude'      => ['required', 'numeric'],
            'longitude'     => ['required', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:1'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        AttendanceLocation::create($data);

        return redirect()
            ->route('hr.locations.index')
            ->with('success', 'Lokasi presensi berhasil ditambahkan.');
    }

    public function edit(AttendanceLocation $location)
    {
        return view('hr.locations.edit', compact('location'));
    }

    public function update(Request $request, AttendanceLocation $location)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'latitude'      => ['required', 'numeric'],
            'longitude'     => ['required', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:1'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        $location->update($data);

        return redirect()
            ->route('hr.locations.index')
            ->with('success', 'Lokasi presensi berhasil diperbarui.');
    }

    public function destroy(AttendanceLocation $location)
    {
        $location->delete();

        return redirect()
            ->route('hr.locations.index')
            ->with('success', 'Lokasi presensi berhasil dihapus.');
    }
}
