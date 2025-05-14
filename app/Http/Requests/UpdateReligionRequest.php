<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class UpdateReligionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }
}
