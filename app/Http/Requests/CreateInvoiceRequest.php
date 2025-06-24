<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
            'entity_type' => 'required|string|in:student,prospective_student',
            'entity_id' => 'required|uuid',

            // 'invoice_number' => 'required|string|unique:invoice,code',
            'student_name' => 'required|string',
            // 'student_type' => 'required|string',
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
            'entity_type.in' => 'Jenis entitas harus student atau prospective_student.',
            'entity_id.required' => 'ID entitas wajib diisi.',
            'selected_items.*.rate_id.required' => 'Setiap item harus memiliki rate_id.',
        ];
    }
}