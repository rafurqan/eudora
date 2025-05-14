<?php

namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Master/StoreEducationLevelRequest.php
class UpdateStudentDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'document_type_id' => 'required',
            'file' => ['required', 'string', new Base64Image],
        ];
    }
}
