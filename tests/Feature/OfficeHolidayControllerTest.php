<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    if (! Schema::hasTable('office_holidays')) {
        Schema::create('office_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('name');
            $table->string('type')->default('NATIONAL');
            $table->boolean('deducts_leave')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    DB::table('office_holidays')->delete();
});

it('allows HRD to view and create office holidays', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd, 'web');

    $this->get('/hr/office-holidays')->assertOk();

    $this->post('/hr/office-holidays', [
        'holiday_date' => '2026-06-16',
        'name' => 'Libur Kantor',
        'type' => 'COMPANY',
        'deducts_leave' => '0',
        'is_active' => '1',
        'notes' => 'Berlaku untuk seluruh PT.',
    ])->assertRedirect('/hr/office-holidays')
        ->assertSessionHasNoErrors();

    $holiday = DB::table('office_holidays')->where('name', 'Libur Kantor')->first();

    expect($holiday)->not->toBeNull()
        ->and((int) $holiday->created_by)->toBe($hrd->id);
});

it('allows HR Staff to update an office holiday', function () {
    $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
    $holidayId = DB::table('office_holidays')->insertGetId([
        'holiday_date' => '2026-06-16',
        'name' => 'Nama Lama',
        'type' => 'COMPANY',
        'deducts_leave' => false,
        'is_active' => true,
    ]);

    actingAs($hrStaff, 'web');

    $this->put("/hr/office-holidays/{$holidayId}", [
        'holiday_date' => '2026-06-17',
        'name' => 'Nama Baru',
        'type' => 'NATIONAL',
        'deducts_leave' => '0',
        'is_active' => '1',
    ])->assertRedirect('/hr/office-holidays');

    $holiday = DB::table('office_holidays')->find($holidayId);

    expect(substr($holiday->holiday_date, 0, 10))->toBe('2026-06-17')
        ->and($holiday->name)->toBe('Nama Baru')
        ->and((int) $holiday->updated_by)->toBe($hrStaff->id);
});

it('forbids employees from managing office holidays', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    actingAs($employee, 'web');

    $this->get('/hr/office-holidays')->assertForbidden();
    $this->post('/hr/office-holidays', [
        'holiday_date' => '2026-06-16',
        'name' => 'Libur Kantor',
        'type' => 'COMPANY',
    ])->assertForbidden();
});

it('rejects duplicate office holiday dates', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    DB::table('office_holidays')->insert([
        'holiday_date' => '2026-06-16',
        'name' => 'Libur Pertama',
        'type' => 'COMPANY',
        'deducts_leave' => false,
        'is_active' => true,
    ]);

    actingAs($hrd, 'web');

    $this->from('/hr/office-holidays')->post('/hr/office-holidays', [
        'holiday_date' => '2026-06-16',
        'name' => 'Libur Duplikat',
        'type' => 'NATIONAL',
    ])->assertRedirect('/hr/office-holidays')
        ->assertSessionHasErrors('holiday_date');
});
