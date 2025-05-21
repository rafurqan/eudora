<?php

namespace App\Http\Requests;

use App\Rules\Base64File;
use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class CreateStudentClassRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'part' => 'required',
            'capacity' => 'required',
            'academic_year' => 'required',
            'teacher.id' => 'required|uuid|exists:teachers,id',
            'program.id' => 'required|uuid|exists:programs,id',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas wajib diisi',
            'part.required' => 'Nama part wajib diisi.',
            'capacity.required' => 'Jumlah kapasitas wajib diisi',
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
            'teacher.id.required' => 'Guru wajib diisi.',
            'program.id.required' => 'Program wajib diisi.',
            'status.in' => 'Status wajib diisi.',
        ];
    }
}
