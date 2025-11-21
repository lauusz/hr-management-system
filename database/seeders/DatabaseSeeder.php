<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $divisionIds = Division::query()->pluck('id')->all();

        if (empty($divisionIds)) {
            $divisionIds = Division::insertGetId(['name' => 'General']);
            $divisionIds = is_array($divisionIds) ? $divisionIds : [$divisionIds];
        }

        // Akun HRD
        $hrd = User::create([
            'name'          => 'HRD Tes',
            'username'      => 'hrd',
            'password'      => '1234',
            'phone'         => '081234567890',
            'role'          => 'HRD',
            'division_id'   => $divisionIds[array_rand($divisionIds)],
            'status'        => 'ACTIVE',
            'last_login_at' => now(),
        ]);

        // Akun Supervisor
        $supervisor = User::create([
            'name'          => 'Supervisor Tes',
            'username'      => 'supervisor',
            'password'      => '1234',
            'phone'         => '081234567891',
            'role'          => 'SUPERVISOR',
            'division_id'   => $divisionIds[array_rand($divisionIds)],
            'status'        => 'ACTIVE',
            'last_login_at' => now(),
        ]);

        // Update division untuk punya supervisor_id
        Division::where('id', $supervisor->division_id)->update([
            'supervisor_id' => $supervisor->id,
        ]);

        // Akun Karyawan
        $employee = User::create([
            'name'          => 'Karyawan Tes',
            'username'      => 'karyawan',
            'password'      => '1234',
            'phone'         => '081234567892',
            'role'          => 'EMPLOYEE',
            'division_id'   => $supervisor->division_id, // ikut supervisor tadi
            'status'        => 'ACTIVE',
            'last_login_at' => now(),
        ]);


        // shift karyawan
        $shift = Shift::create([
            'name'       => 'Kantor Pagi',
            'start_time' => '08:30:00',
            'end_time'   => '17:00:00',
        ]);

        // attendance location
        $location = AttendanceLocation::create([
            'name'       => 'Kantor Triguna',
            'address'    => 'Jl. Tanjung Batu No.15Q, Perak Bar., Kec. Krembangan, Surabaya, Jawa Timur 60177',
            'latitude'   => '-7.22020',
            'longitude'  => '112.72942',
            'radius_meters'     => 30, // dalam meter
            'is_active'  => true,
        ]);
    }
}
