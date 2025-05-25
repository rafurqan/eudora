<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sesuaikan dengan izin pengguna jika perlu
    }

    public function rules(): array
    {
        return [
            'student_class_id' => 'required|uuid|exists:student_classes,id',
            'student_id' => 'nullable|uuid|exists:students,id',
            'prospective_student_id' => 'nullable|uuid|exists:prospective_students,id',
            'reason' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ];
    }

    public function messages(): array
    {
        return [
            'student_class_id.required' => 'Kelas harus dipilih.',
            'student_class_id.exists' => 'Kelas tidak ditemukan.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'prospective_student_id.exists' => 'Calon siswa tidak ditemukan.',
            'start_at.required' => 'Tanggal mulai harus diisi.',
            'start_at.date' => 'Tanggal mulai tidak valid.',
            'end_at.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai atau sama.',
        ];
    }
}
