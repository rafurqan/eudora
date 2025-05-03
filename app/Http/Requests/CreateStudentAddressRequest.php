<?php
namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class CreateStudentAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'street' => 'required|string|max:100',
        ];
    }


    public function messages(): array
    {
        return [
            'street' => 'alamat wajib diisi.',
        ];
    }
}
