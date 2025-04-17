<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class CreateEducationLevelRequest extends FormRequest
{
    public function authorize()
    {
        return true; // atau cek role/permission di sini
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'level' => 'required',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
