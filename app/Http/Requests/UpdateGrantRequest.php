<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrantRequest extends FormRequest
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
            'donor_name' => 'nullable|string',
            'grants_name' => 'nullable|string',
            'donation_type' => 'nullable|string',
            'is_active' => 'nullable|string|size:1',
            'description' => 'nullable|string',
            'total_funds' => 'nullable|numeric|min:0|max:999999999999.99',
            'grant_expiration_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'updated_by_id' => 'nullable|uuid',
            'code' => 'nullable|string',
        ];
    }
}
