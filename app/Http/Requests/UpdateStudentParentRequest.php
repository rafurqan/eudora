<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_type' => 'required|in:father,mother,guardian,other',
            'full_name' => 'required|string|max:100',
            'nik' => 'nullable|string|max:20',
            'birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'education_level_id' => 'nullable|uuid|exists:education_levels,id',
            'occupation' => 'nullable|string',
            'income_range_id' => 'nullable|uuid|exists:income_ranges,id',
            'phone' => 'nullable|string|max:20',
            'is_guardian' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'parents.*.parent_type.in' => 'Tipe orang tua tidak valid.',
        ];
    }
}
