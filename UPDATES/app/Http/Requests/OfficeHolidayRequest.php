<?php

namespace App\Http\Requests;

use App\Models\OfficeHoliday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isHR();
    }

    public function rules(): array
    {
        /** @var OfficeHoliday|null $officeHoliday */
        $officeHoliday = $this->route('officeHoliday');

        return [
            'holiday_date' => [
                'required',
                'date',
                Rule::unique('office_holidays', 'holiday_date')->ignore($officeHoliday?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(OfficeHoliday::TYPES))],
            'deducts_leave' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'holiday_date.required' => 'Tanggal libur wajib diisi.',
            'holiday_date.unique' => 'Tanggal tersebut sudah terdaftar di Kalender Kantor.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
        ];
    }
}
