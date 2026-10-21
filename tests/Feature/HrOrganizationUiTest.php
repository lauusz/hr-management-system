<?php

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Pt;
use App\Models\User;

it('renders /hr/organization with leave master layout for both Divisi and Jabatan tabs', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create([
        'name' => 'Budi Supervisor',
        'role' => UserRole::SUPERVISOR,
    ]);

    $divA = Division::create([
        'name' => 'Divisi Operasional',
        'supervisor_id' => $supervisor->id,
    ]);

    Division::create([
        'name' => 'Divisi Keuangan',
        'supervisor_id' => null,
    ]);

    Position::create([
        'name' => 'Staff Operasional',
        'division_id' => $divA->id,
        'is_active' => true,
    ]);

    Position::create([
        'name' => 'Auditor Internal',
        'division_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.organization'))
        ->assertOk()
        ->assertSee('Divisi & Jabatan')
        ->assertSee('Total Divisi')
        ->assertSee('Memiliki Supervisor')
        ->assertSee('Tanpa Supervisor')
        ->assertSee('Total Jabatan')
        ->assertSee('Terhubung Divisi')
        ->assertSee('Tanpa Divisi')
        ->assertSee('Divisi Operasional')
        ->assertSee('Budi Supervisor')
        ->assertSee('Divisi Keuangan')
        ->assertSee('Staff Operasional')
        ->assertSee('Auditor Internal')
        ->assertSee('data-org-search="tab-divisi"', false)
        ->assertSee('data-org-search="tab-jabatan"', false)
        ->assertSee('lm-cta-bar--split', false);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.organization', ['tab' => 'positions']))
        ->assertOk()
        ->assertSee('id="tab-jabatan" class="lm-tab-panel org-tab-content active"', false);
});

it('renders /hr/divisions/{division}/edit with consistent detail card and fixed action dock layout', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create([
        'name' => 'Siti Supervisor',
        'role' => UserRole::SUPERVISOR,
    ]);

    $division = Division::create([
        'name' => 'Divisi Marketing',
        'supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.divisions.edit', $division))
        ->assertOk()
        ->assertSee('Edit Divisi')
        ->assertSee('Divisi Organisasi')
        ->assertSee('Divisi Marketing')
        ->assertSee('Siti Supervisor')
        ->assertSee('Memiliki Supervisor')
        ->assertSee('Informasi & Pengaturan Divisi')
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Simpan Perubahan');
});

it('renders /hr/pts with leave master layout, stats, search, and employee status filter', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $ptWithEmployee = Pt::create(['name' => 'PT Maju Bersama']);
    $ptEmpty = Pt::create(['name' => 'PT Sinar Baru']);

    EmployeeProfile::create([
        'user_id' => $employee->id,
        'pt_id' => $ptWithEmployee->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.pts.index'))
        ->assertOk()
        ->assertSee('Master PT')
        ->assertSee('Total PT')
        ->assertSee('Memiliki Karyawan')
        ->assertSee('Belum Ada Karyawan')
        ->assertSee('PT Maju Bersama')
        ->assertSee('1 Karyawan')
        ->assertSee('PT Sinar Baru')
        ->assertSee('data-pt-live-search', false);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.pts.index', ['status' => 'with_employees']))
        ->assertOk()
        ->assertSee('PT Maju Bersama')
        ->assertDontSee('PT Sinar Baru');

    $this->actingAs($hrd, 'web')
        ->get(route('hr.pts.index', ['q' => 'Sinar']))
        ->assertOk()
        ->assertSee('PT Sinar Baru')
        ->assertDontSee('PT Maju Bersama');
});

it('renders /hr/pts/{pt}/edit with consistent detail card and fixed action dock layout', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $pt = Pt::create(['name' => 'PT Triguna Samudratrans']);

    EmployeeProfile::create([
        'user_id' => $employee->id,
        'pt_id' => $pt->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.pts.edit', $pt))
        ->assertOk()
        ->assertSee('Edit Perusahaan (PT)')
        ->assertSee('Entitas Perusahaan')
        ->assertSee('PT Triguna Samudratrans')
        ->assertSee('1 Karyawan Terdaftar')
        ->assertSee('Memiliki Karyawan')
        ->assertSee('Informasi Perusahaan (PT)')
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Simpan Perubahan');
});

it('renders /hr/pts/create, /hr/divisions/create, and /hr/positions/create with consistent step card and fixed action dock layout', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create([
        'name' => 'Rina Supervisor',
        'role' => UserRole::SUPERVISOR,
    ]);
    $division = Division::create([
        'name' => 'Divisi Logistik',
        'supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.pts.create'))
        ->assertOk()
        ->assertSee('Tambah PT Baru')
        ->assertSee('Informasi Perusahaan')
        ->assertSee('mlc-banner', false)
        ->assertSee('mlc-step', false)
        ->assertSee('apv-action-dock', false)
        ->assertSee('Simpan PT');

    $this->actingAs($hrd, 'web')
        ->get(route('hr.divisions.create'))
        ->assertOk()
        ->assertSee('Tambah Divisi Baru')
        ->assertSee('Informasi Divisi')
        ->assertSee('Rina Supervisor')
        ->assertSee('mlc-banner', false)
        ->assertSee('mlc-step', false)
        ->assertSee('apv-action-dock', false)
        ->assertSee('Simpan Divisi');

    $this->actingAs($hrd, 'web')
        ->get(route('hr.positions.create'))
        ->assertOk()
        ->assertSee('Tambah Jabatan Baru')
        ->assertSee('Informasi Jabatan')
        ->assertSee($division->name)
        ->assertSee('mlc-banner', false)
        ->assertSee('mlc-step', false)
        ->assertSee('apv-action-dock', false)
        ->assertSee('Simpan Jabatan');
});

it('renders /hr/positions/{position}/edit with consistent detail card and fixed action dock layout', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $division = Division::create(['name' => 'Divisi IT']);
    $position = Position::create([
        'name' => 'Lead Developer',
        'division_id' => $division->id,
        'is_active' => true,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.positions.edit', $position))
        ->assertOk()
        ->assertSee('Edit Jabatan')
        ->assertSee('Jabatan Organisasi')
        ->assertSee('Lead Developer')
        ->assertSee('Divisi: Divisi IT')
        ->assertSee('Jabatan Aktif')
        ->assertSee('Informasi & Pengaturan Jabatan')
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Simpan Perubahan');
});
