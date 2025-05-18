<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class UpdateTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'nip' => [
                'required',
                Rule::unique('teachers', 'nip')->ignore($this->route('teacher')),
            ],
            'birth_place' => 'nullable',
            'birth_date' => 'nullable|date',
            'graduated_from' => 'nullable',
            'education_level.id' => 'required|uuid|exists:education_levels,id'
        ];
    }
}
