<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaVehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:30'], 'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])]]);
        $filters = ['q' => trim($input['q'] ?? ''), 'status' => $input['status'] ?? 'all'];
        $vehicles = InvPesonaVehicle::query()->withExists('requests')
            ->when($filters['q'] !== '', fn ($query) => $query->where('nopol', 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] !== 'all', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('nopol')->orderBy('id')->paginate(20)->appends($filters);
        $hasVehicles = $vehicles->total() > 0 || (($filters['q'] !== '' || $filters['status'] !== 'all') && InvPesonaVehicle::query()->exists());

        return view('inventory.pesona.admin.vehicles.index', compact('vehicles', 'filters', 'hasVehicles'));
    }

    public function create(): View
    {
        return view('inventory.pesona.admin.vehicles.form', ['vehicle' => new InvPesonaVehicle(['is_active' => true])]);
    }

    public function edit(InvPesonaVehicle $vehicle): View
    {
        return view('inventory.pesona.admin.vehicles.form', compact('vehicle'));
    }

    private function validated(Request $request, ?InvPesonaVehicle $vehicle = null): array
    {
        if (is_string($request->input('nopol'))) {
            $request->merge(['nopol' => mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $request->input('nopol'))))]);
        }

        return $request->validate([
            'nopol' => ['required', 'string', 'max:30', Rule::unique('inv_pesona_vehicles', 'nopol')->ignore($vehicle)],
            'is_active' => ['required', 'boolean'],
        ], ['nopol.unique' => 'Nopol sudah terdaftar.']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        try {
            InvPesonaVehicle::create($data + ['created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        } catch (QueryException $exception) {
            $this->duplicate($exception);
        }

        return redirect()->route('inventory.pesona.admin.vehicles.index')->with('success', 'Nopol berhasil ditambahkan.');
    }

    public function update(Request $request, InvPesonaVehicle $vehicle): RedirectResponse
    {
        $data = $this->validated($request, $vehicle);
        try {
            $vehicle->update($data + ['updated_by' => $request->user()->id]);
        } catch (QueryException $exception) {
            $this->duplicate($exception);
        }

        return redirect()->route('inventory.pesona.admin.vehicles.index')->with('success', 'Nopol berhasil diperbarui.');
    }

    private function duplicate(QueryException $exception): never
    {
        if (($exception->errorInfo[1] ?? null) === 1062 && str_contains($exception->getMessage(), 'inv_pesona_vehicles_nopol_unique')) {
            throw ValidationException::withMessages(['nopol' => 'Nopol sudah terdaftar.']);
        }
        throw $exception;
    }

    public function destroy(InvPesonaVehicle $vehicle): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($vehicle): bool {
                $current = InvPesonaVehicle::query()->lockForUpdate()->findOrFail($vehicle->id);
                if ($current->requests()->exists()) {
                    return false;
                }
                $current->delete();

                return true;
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1451 || ! str_contains($exception->getMessage(), 'inv_pesona_requests_vehicle_foreign')) {
                throw $exception;
            }
            $deleted = false;
        }

        return redirect()->route('inventory.pesona.admin.vehicles.index')->with($deleted ? 'success' : 'warning', $deleted ? 'Nopol berhasil dihapus.' : 'Nopol sudah digunakan. Silakan nonaktifkan kendaraan.');
    }
}
