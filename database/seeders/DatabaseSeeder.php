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
        // $this->call(ImportEmployeesFromCsvSeeder::class);
        
        // User::create([
        //     'name' => 'admin',
        //     'username' => 'admin',
        //     'email' => 'admin@local.test',
        //     'phone' => null,
        //     'role' => 'HRD',
        //     'division_id' => null,
        //     'position_id' => null,
        //     'status' => 'ACTIVE',
        //     'password' => bcrypt('admin123'),
        // ]);
    }
}
