<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class CreateTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'nip' => 'required|unique:teachers,nip',
            'birth_place' => 'nullable',
            'birth_date' => 'nullable|date',
            'graduated_from' => 'nullable',
        ];
    }
}
