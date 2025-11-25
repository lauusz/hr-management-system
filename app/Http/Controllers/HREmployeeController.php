<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Position;
use App\Models\EmployeeProfile;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class HREmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['division', 'position', 'profile'])
            ->orderBy('name');

        if ($search = $request->get('q')) {
            $query->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('hr.employees.index', compact('items', 'search'));
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $roles = UserRole::cases();

        return view('hr.employees.create', [
            'divisions' => $divisions,
            'positions' => $positions,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:255', 'unique:users,username'],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],

            'pt' => ['nullable', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'work_email' => ['nullable', 'email', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:50'],
            'no_kartu_keluarga' => ['nullable', 'string', 'max:50'],
            'no_ktp' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'pendidikan' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'tgl_lahir' => ['nullable', 'date'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'alamat1' => ['nullable', 'string'],
            'alamat2' => ['nullable', 'string'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kab_kota' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'ptkp' => ['nullable', 'string', 'max:50'],
            'no_npwp' => ['nullable', 'string', 'max:50'],
            'bpjs_tk' => ['nullable', 'string', 'max:50'],
            'no_bpjs_kesehatan' => ['nullable', 'string', 'max:50'],
            'kelas_bpjs' => ['nullable', 'string', 'max:50'],
            'masa_kerja' => ['nullable', 'string', 'max:50'],
            'tgl_bergabung' => ['nullable', 'date'],
            'tgl_berakhir_percobaan' => ['nullable', 'date'],
        ]);

        $userData = Arr::only($validated, [
            'name','username','phone','role','division_id','position_id'
        ]);

        $userData['status'] = 'ACTIVE';
        $userData['password'] = Hash::make('123456');

        $user = User::create($userData);

        $profileData = Arr::except($validated, [
            'name','username','phone','role','division_id','position_id'
        ]);

        $profileData['user_id'] = $user->id;

        EmployeeProfile::create($profileData);

        return redirect()->route('hr.employees.index')
            ->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $roles = UserRole::cases();

        return view('hr.employees.edit', [
            'item' => $employee,
            'divisions' => $divisions,
            'positions' => $positions,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($employee->id)],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],

            'pt' => ['nullable','string','max:150'],
            'kategori' => ['nullable','string','max:50'],
            'nik' => ['nullable','string','max:50'],
            'work_email' => ['nullable','email','max:150'],
            'kewarganegaraan' => ['nullable','string','max:50'],
            'agama' => ['nullable','string','max:50'],
            'no_kartu_keluarga' => ['nullable','string','max:50'],
            'no_ktp' => ['nullable','string','max:50'],
            'nama_bank' => ['nullable','string','max:100'],
            'no_rekening' => ['nullable','string','max:50'],
            'pendidikan' => ['nullable','string','max:100'],
            'jenis_kelamin' => ['nullable','string','max:20'],
            'tgl_lahir' => ['nullable','date'],
            'tempat_lahir' => ['nullable','string','max:100'],
            'alamat1' => ['nullable','string'],
            'alamat2' => ['nullable','string'],
            'provinsi' => ['nullable','string','max:100'],
            'kab_kota' => ['nullable','string','max:100'],
            'kecamatan' => ['nullable','string','max:100'],
            'desa_kelurahan' => ['nullable','string','max:100'],
            'kode_pos' => ['nullable','string','max:10'],
            'ptkp' => ['nullable','string','max:50'],
            'no_npwp' => ['nullable','string','max:50'],
            'bpjs_tk' => ['nullable','string','max:50'],
            'no_bpjs_kesehatan' => ['nullable','string','max:50'],
            'kelas_bpjs' => ['nullable','string','max:50'],
            'masa_kerja' => ['nullable','string','max:50'],
            'tgl_bergabung' => ['nullable','date'],
            'tgl_berakhir_percobaan' => ['nullable','date'],
        ]);

        $userData = Arr::only($validated, [
            'name','username','phone','role','division_id','position_id'
        ]);

        $employee->update($userData);

        $profileData = Arr::except($validated, [
            'name','username','phone','role','division_id','position_id'
        ]);

        if ($employee->profile) {
            $employee->profile->update($profileData);
        } else {
            $profileData['user_id'] = $employee->id;
            EmployeeProfile::create($profileData);
        }

        return redirect()->route('hr.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->update([
            'status' => 'INACTIVE',
        ]);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Karyawan berhasil dinonaktifkan.');
    }
}
