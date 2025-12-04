<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Position;
use App\Models\EmployeeProfile;
use App\Models\Pt;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HREmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $ptFilter = $request->get('pt_id');
        $positionFilter = $request->get('position_id');
        $categoryFilter = $request->get('kategori');
        $nearExpiry = $request->boolean('near_expiry');

        $query = User::with(['division', 'position', 'profile.pt']);

        if ($search) {
            $query->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($ptFilter) {
            $query->whereHas('profile', function ($q) use ($ptFilter) {
                $q->where('pt_id', $ptFilter);
            });
        }

        if ($positionFilter) {
            $query->where('position_id', $positionFilter);
        }

        if ($categoryFilter) {
            $query->whereHas('profile', function ($q) use ($categoryFilter) {
                $q->where('kategori', $categoryFilter);
            });
        }

        if ($nearExpiry) {
            $query->whereHas('profile', function ($q) {
                $q->whereNotNull('tgl_akhir_percobaan');
            });

            $query->orderBy(
                EmployeeProfile::select('tgl_akhir_percobaan')
                    ->whereColumn('employee_profiles.user_id', 'users.id')
            );
        } else {
            $query->orderBy('name');
        }

        $totalEmployees = (clone $query)->count();

        $items = $query->paginate(100)->withQueryString();

        $items->getCollection()->transform(function ($user) {
            $profile = $user->profile;

            if ($profile && $profile->tgl_bergabung) {
                $user->join_date_label = Carbon::parse($profile->tgl_bergabung)->format('d-m-Y');
            } else {
                $user->join_date_label = null;
            }

            if ($profile && $profile->tgl_akhir_percobaan) {
                $user->probation_end_label = Carbon::parse($profile->tgl_akhir_percobaan)->format('d-m-Y');
            } else {
                $user->probation_end_label = null;
            }

            if ($profile && $profile->masa_kerja) {
                $user->masa_kerja_label = $profile->masa_kerja;
            } elseif ($profile && $profile->tgl_bergabung) {
                $start = Carbon::parse($profile->tgl_bergabung);
                $diff = $start->diff(Carbon::now());

                $parts = [];

                if ($diff->y) {
                    $parts[] = $diff->y . ' th';
                }

                if ($diff->m) {
                    $parts[] = $diff->m . ' bln';
                }

                $user->masa_kerja_label = $parts ? implode(' ', $parts) : null;
            } else {
                $user->masa_kerja_label = null;
            }

            return $user;
        });

        $ptOptions = Pt::orderBy('name')->get();
        $positionOptions = Position::orderBy('name')->get();
        $categoryOptions = ['TETAP', 'KONTRAK'];

        return view('hr.employees.index', [
            'items' => $items,
            'search' => $search,
            'ptId' => $ptFilter,
            'pt' => $ptFilter,
            'ptOptions' => $ptOptions,
            'positionId' => $positionFilter,
            'positionOptions' => $positionOptions,
            'kategori' => $categoryFilter,
            'categoryOptions' => $categoryOptions,
            'totalEmployees' => $totalEmployees,
            'nearExpiry' => $nearExpiry,
        ]);
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $roles = UserRole::cases();
        $ptOptions = Pt::orderBy('name')->get();

        return view('hr.employees.create', [
            'divisions' => $divisions,
            'positions' => $positions,
            'roles' => $roles,
            'ptOptions' => $ptOptions,
        ]);
    }

    public function store(Request $request)
    {
        $roleValues = array_map(fn(UserRole $r) => $r->value, UserRole::cases());

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'pt_id' => ['nullable', 'exists:pts,id'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'work_email' => ['nullable', 'email', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:50'],
            'path_kartu_keluarga' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'path_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'tgl_akhir_percobaan' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date'],
            'exit_reason_code' => ['nullable', 'string', 'max:50'],
            'exit_reason_note' => ['nullable', 'string'],
        ]);

        $userData = Arr::only($validated, [
            'name',
            'username',
            'phone',
            'role',
            'division_id',
            'position_id',
        ]);

        $userData['status'] = 'ACTIVE';
        $userData['password'] = Hash::make('123456');

        $user = User::create($userData);

        $profileData = Arr::except($validated, [
            'name',
            'username',
            'phone',
            'role',
            'division_id',
            'position_id',
            'path_kartu_keluarga',
            'path_ktp',
        ]);

        $kkPath = null;
        $ktpPath = null;

        if ($request->hasFile('path_kartu_keluarga')) {
            $file = $request->file('path_kartu_keluarga');
            $filename = 'kk_' . Str::slug($user->name ?: 'employee') . '_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $kkPath = $file->storeAs('employee_docs', $filename, 'public');
        }

        if ($request->hasFile('path_ktp')) {
            $file = $request->file('path_ktp');
            $filename = 'ktp_' . Str::slug($user->name ?: 'employee') . '_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $ktpPath = $file->storeAs('employee_docs', $filename, 'public');
        }

        if ($kkPath) {
            $profileData['path_kartu_keluarga'] = $kkPath;
        }

        if ($ktpPath) {
            $profileData['path_ktp'] = $ktpPath;
        }

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
        $ptOptions = Pt::orderBy('name')->get();

        return view('hr.employees.edit', [
            'item' => $employee,
            'divisions' => $divisions,
            'positions' => $positions,
            'roles' => $roles,
            'ptOptions' => $ptOptions,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $roleValues = array_map(fn(UserRole $r) => $r->value, UserRole::cases());

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($employee->id)],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'pt_id' => ['nullable', 'exists:pts,id'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'work_email' => ['nullable', 'email', 'max:150'],
            'kewarganegaraan' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:50'],
            'path_kartu_keluarga' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'path_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'tgl_akhir_percobaan' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date'],
            'exit_reason_code' => ['nullable', 'string', 'max:50'],
            'exit_reason_note' => ['nullable', 'string'],
        ]);

        $userData = Arr::only($validated, [
            'name',
            'username',
            'phone',
            'role',
            'division_id',
            'position_id',
        ]);

        $employee->update($userData);

        $profileData = Arr::except($validated, [
            'name',
            'username',
            'phone',
            'role',
            'division_id',
            'position_id',
            'path_kartu_keluarga',
            'path_ktp',
        ]);

        $kkPath = null;
        $ktpPath = null;

        if ($request->hasFile('path_kartu_keluarga')) {
            $file = $request->file('path_kartu_keluarga');
            $filename = 'kk_' . Str::slug($employee->name ?: 'employee') . '_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $kkPath = $file->storeAs('employee_docs', $filename, 'public');
        }

        if ($request->hasFile('path_ktp')) {
            $file = $request->file('path_ktp');
            $filename = 'ktp_' . Str::slug($employee->name ?: 'employee') . '_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $ktpPath = $file->storeAs('employee_docs', $filename, 'public');
        }

        if ($kkPath) {
            $profileData['path_kartu_keluarga'] = $kkPath;
        }

        if ($ktpPath) {
            $profileData['path_ktp'] = $ktpPath;
        }

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

    public function exit(Request $request, User $employee)
    {
        $validated = $request->validate([
            'exit_date' => ['required', 'date'],
            'exit_reason_code' => ['nullable', 'string', 'max:50'],
            'exit_reason_note' => ['nullable', 'string'],
        ]);

        $employee->update([
            'status' => 'INACTIVE',
        ]);

        $profile = $employee->profile;

        if ($profile) {
            $profile->update([
                'exit_date' => $validated['exit_date'],
                'exit_reason_code' => $validated['exit_reason_code'] ?? null,
                'exit_reason_note' => $validated['exit_reason_note'] ?? null,
            ]);
        } else {
            EmployeeProfile::create([
                'user_id' => $employee->id,
                'exit_date' => $validated['exit_date'],
                'exit_reason_code' => $validated['exit_reason_code'] ?? null,
                'exit_reason_note' => $validated['exit_reason_note'] ?? null,
            ]);
        }

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Karyawan berhasil dinonaktifkan dan alasan keluar telah disimpan.');
    }
}
