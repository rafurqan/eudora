<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (is_array($this->selected_items)) {
            $this->merge([
                'selected_items' => collect($this->selected_items)->map(function ($item) {
                    $item['rate_id'] = $item['id'] ?? null;
                    return $item;
                })->toArray(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'student_name' => 'required|string',
            'student_type' => 'required|string',
            'class' => 'required|uuid',
            'class_name' => 'nullable|string',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'invoice_type' => 'required|string|in:1,2,3',
            'notes' => 'nullable|string',
            'selected_items' => 'required|array|min:1',
            'selected_items.*.rate_id' => 'required|uuid',
            'selected_items.*.service_id' => 'required|uuid',
            'selected_items.*.price' => 'required|numeric|min:0',
            'selected_items.*.frequency' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'selected_items.*.rate_id.required' => 'Setiap item harus memiliki rate_id.',
        ];
    }
}
