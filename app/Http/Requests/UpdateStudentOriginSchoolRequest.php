<?php
namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentOriginSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'education_level_id' => 'required|uuid|exists:education_levels,id',
            'school_type_id' => 'required|uuid|exists:school_types,id',
            'school_name' => 'required|string|max:100',
            'npsn' => 'required|string|max:100',
            'address_name' => 'required|string',
        ];
    }


    public function messages(): array
    {
        return [
            'education_level_id.required' => 'pendidikan terakhir wajib diisi.',
            'school_type_id.required' => 'tipe sekolah wajib diisi.',
            'school_name.required' => 'nama sekolah wajib diisi.',
        ];
    }
}
