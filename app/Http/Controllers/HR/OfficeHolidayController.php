<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeHolidayRequest;
use App\Models\OfficeHoliday;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeHolidayController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $status = (string) $request->query('status', 'active');

        $query = OfficeHoliday::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $items = $query->paginate(20)->withQueryString();

        $years = OfficeHoliday::query()
            ->orderBy('holiday_date')
            ->pluck('holiday_date')
            ->map(fn ($date): int => Carbon::parse($date)->year)
            ->push(now()->year)
            ->unique()
            ->sort()
            ->values();

        return view('hr.office_holidays.index', [
            'items' => $items,
            'year' => $year,
            'years' => $years,
            'status' => $status,
            'types' => OfficeHoliday::TYPES,
        ]);
    }

    public function store(OfficeHolidayRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        OfficeHoliday::create([
            ...$validated,
            'deducts_leave' => $request->boolean('deducts_leave'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('hr.office-holidays.index')
            ->with('success', 'Tanggal libur berhasil ditambahkan.');
    }

    public function edit(OfficeHoliday $officeHoliday): View
    {
        return view('hr.office_holidays.edit', [
            'item' => $officeHoliday,
            'types' => OfficeHoliday::TYPES,
        ]);
    }

    public function update(OfficeHolidayRequest $request, OfficeHoliday $officeHoliday): RedirectResponse
    {
        $officeHoliday->update([
            ...$request->validated(),
            'deducts_leave' => $request->boolean('deducts_leave'),
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('hr.office-holidays.index')
            ->with('success', 'Tanggal libur berhasil diperbarui.');
    }

    public function destroy(Request $request, OfficeHoliday $officeHoliday): RedirectResponse
    {
        $officeHoliday->update([
            'is_active' => false,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('hr.office-holidays.index')
            ->with('success', 'Tanggal libur berhasil dinonaktifkan.');
    }
}
