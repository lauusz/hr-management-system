<?php

namespace Database\Factories;

use App\Models\EmployeeProfile;
use App\Models\User;
use App\Models\Pt;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeProfileFactory extends Factory
{
    protected $model = EmployeeProfile::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'pt_id'        => null,
            'kategori'     => 'KONTRAK',
            'nik'          => 'NIK' . $this->faker->unique()->numerify('######'),
            'email'        => $this->faker->safeEmail(),
            'jabatan'      => 'Staff',
            'kewarganegaraan' => 'Indonesia',
            'agama'        => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'tgl_bergabung' => now()->subYear()->toDateString(),
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'tgl_lahir'    => $this->faker->date(),
            'tempat_lahir' => $this->faker->city(),
            'alamat1'      => $this->faker->address(),
            'lokasi_kerja' => 'Kantor Pusat',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }

    public function joinedYearsAgo(int $years): static
    {
        return $this->state(fn() => ['tgl_bergabung' => now()->subYears($years)->toDateString()]);
    }

    public function joinedLessThanOneYear(): static
    {
        return $this->state(fn() => ['tgl_bergabung' => now()->subMonths(6)->toDateString()]);
    }

    public function withPt(Pt $pt): static
    {
        return $this->state(fn() => ['pt_id' => $pt->id]);
    }
}
