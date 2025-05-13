<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id'     => 'required|uuid',
            'child_ids'      => 'nullable|array',
            'program_id'     => 'nullable|uuid',
            'price'          => 'required|integer|min:0',
            'is_active'      => 'required|string|in:Y,N',
            'created_by_id'  => 'required|uuid',
        ];
    }
}
