<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
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
            'invoice_id' => 'required|uuid|exists:invoice,id',
            'payment_method' => 'required|string|max:100',
            'payment_date' => 'required|date',

            'bank_name' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'nominal_payment' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',

            'id_log_grant' => 'nullable|uuid',
            'grant_id' => 'nullable|uuid',
            'id_grant' => 'nullable|uuid',
            'grant_amount' => 'nullable|integer|min:0',
            'created_by_id' => 'nullable|uuid',
            'updated_by_id' => 'nullable|uuid',
        ];
    }

    // CreatePaymentRequest.php
    public function prepareForValidation()
    {
        if ($this->has('bank_details') && is_array($this->bank_details)) {
            $this->merge([
                'bank_name' => $this->bank_details['bank_name'] ?? null,
                'account_name' => $this->bank_details['account_holder'] ?? null,
                'account_number' => $this->bank_details['account_number'] ?? null,
                'reference_number' => $this->bank_details['reference_number'] ?? null,
            ]);
        }

        if ($this->has('total_payment')) {
            $this->merge([
                'nominal_payment' => $this->total_payment,
            ]);
        }

        if ($this->has('grant_id')) {
            $this->merge([
                'id_grant' => $this->grant_id,
            ]);
        }

        if ($this->has('grant_amount')) {
            $this->merge([
                'grant_amount' => $this->grant_amount,
            ]);
        }

    }

}
