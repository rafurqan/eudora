<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGrantRequest extends FormRequest
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
            'donor_id' => 'required|uuid|exists:donors,id',
            'donation_type_id' => 'required|uuid|exists:donation_types,id',
            'is_active' => 'required|string|size:1',
            'description' => 'required|string',
            'total_funds' => 'required|numeric|min:0|max:999999999999.99',
            'grant_expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'updated_by_id' => 'nullable|uuid',
        ];
    }
}
