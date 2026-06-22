<?php

use App\Enums\UserRole;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('EmployeeDocumentController', function () {
    it('allows HRD to delete employee document', function () {
        Storage::fake('public');

        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $document = EmployeeDocument::create([
            'user_id' => $employee->id,
            'type' => EmployeeDocument::TYPE_SK,
            'title' => 'SK Test',
            'file_path' => 'employee_documents/test-file.pdf',
            'created_by' => $hrd->id,
        ]);
        Storage::disk('public')->put('employee_documents/test-file.pdf', 'file-content');

        actingAs($hrd, 'web');

        $response = $this->delete(route('hr.employee_documents.destroy', $document));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('employee_documents', ['id' => $document->id]);
        Storage::disk('public')->assertMissing('employee_documents/test-file.pdf');
    });

    it('allows HR STAFF to delete employee document', function () {
        Storage::fake('public');

        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $document = EmployeeDocument::create([
            'user_id' => $employee->id,
            'type' => EmployeeDocument::TYPE_SK,
            'title' => 'SK Test',
            'file_path' => 'employee_documents/test-file.pdf',
            'created_by' => $hrStaff->id,
        ]);

        actingAs($hrStaff, 'web');

        $response = $this->delete(route('hr.employee_documents.destroy', $document));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('employee_documents', ['id' => $document->id]);
    });

    it('prevents non-HR employee from deleting employee document', function () {
        Storage::fake('public');

        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $otherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $document = EmployeeDocument::create([
            'user_id' => $otherEmployee->id,
            'type' => EmployeeDocument::TYPE_SK,
            'title' => 'SK Test',
            'file_path' => 'employee_documents/test-file.pdf',
            'created_by' => $employee->id,
        ]);

        actingAs($employee, 'web');

        $this->delete(route('hr.employee_documents.destroy', $document))
            ->assertForbidden();

        $this->assertDatabaseHas('employee_documents', ['id' => $document->id]);
    });

    it('allows HRD to store employee document', function () {
        Storage::fake('public');

        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.employees.documents.store', $employee), [
            'type' => EmployeeDocument::TYPE_SK,
            'title' => 'SK Test',
            'file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employee_documents', [
            'user_id' => $employee->id,
            'type' => EmployeeDocument::TYPE_SK,
            'title' => 'SK Test',
            'created_by' => $hrd->id,
        ]);
    });
});
