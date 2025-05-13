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
            'service_id'     => 'required|uuid|exists:services,id',
            'child_ids'      => 'nullable|array',
            'child_ids.*'    => 'nullable|uuid',
            'program_id'     => 'nullable|uuid|exists:program_school,id',
            'price'          => 'required|integer|min:0',
            'is_active'      => 'required|string|in:Y,N',
        ];
    }
}
