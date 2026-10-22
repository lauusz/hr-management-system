<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AttendanceLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLocation::orderBy('name');

        $status = $request->query('status');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } else {
            $status = null;
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            });
        }

        $items = $query->get();

        $totalCount = $items->count();
        $activeCount = $items->where('is_active', true)->count();
        $inactiveCount = $items->where('is_active', false)->count();

        return view('hr.locations.index', [
            'items' => $items,
            'q' => $q,
            'status' => $status,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'locationUsers' => $this->locationUsers($items),
        ]);
    }

    public function create()
    {
        return view('hr.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
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
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
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

    /**
     * @param  Collection<int, AttendanceLocation>  $locations
     * @return Collection<int, Collection<int, User>>
     */
    private function locationUsers(Collection $locations): Collection
    {
        $locationIds = $locations->pluck('id');
        $usersByLocation = $locations->mapWithKeys(fn (AttendanceLocation $location) => [$location->id => collect()]);

        if ($locationIds->isEmpty()) {
            return $usersByLocation;
        }

        $withActiveUser = fn ($query) => $query
            ->select('id', 'name')
            ->where('status', User::STATUS_ACTIVE);

        $addUser = function (int $locationId, ?User $user) use ($usersByLocation): void {
            if ($user && $usersByLocation->has($locationId)) {
                $usersByLocation->get($locationId)->put($user->id, $user);
            }
        };

        EmployeeShift::query()
            ->with(['user' => $withActiveUser])
            ->whereIn('location_id', $locationIds)
            ->get()
            ->each(fn (EmployeeShift $schedule) => $addUser($schedule->location_id, $schedule->user));

        $activeLocationIds = $locations->where('is_active', true)->pluck('id');
        if ($activeLocationIds->isNotEmpty()) {
            EmployeeShift::query()
                ->with(['user' => $withActiveUser])
                ->where('is_all_locations', true)
                ->get()
                ->each(function (EmployeeShift $schedule) use ($activeLocationIds, $addUser): void {
                    $activeLocationIds->each(fn (int $locationId) => $addUser($locationId, $schedule->user));
                });
        }

        if (Schema::hasTable('employee_shift_locations')) {
            EmployeeShift::query()
                ->with(['user' => $withActiveUser, 'locations:id'])
                ->whereHas('locations', fn ($query) => $query->whereIn('attendance_locations.id', $locationIds))
                ->get()
                ->each(function (EmployeeShift $schedule) use ($locationIds, $addUser): void {
                    $schedule->locations
                        ->whereIn('id', $locationIds)
                        ->each(fn (AttendanceLocation $location) => $addUser($location->id, $schedule->user));
                });
        }

        return $usersByLocation->map(fn (Collection $users) => $users
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values());
    }
}
