<?php
namespace App\Http\Requests;

use App\Rules\Base64File;
use Illuminate\Foundation\Http\FormRequest;

class CreateProspectiveStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_code' => 'required|string|unique:students,registration_code|max:50',
            'full_name' => 'required|string|max:100',
            'nickname' => 'nullable|string|max:50',
            'religion.id' => 'nullable|uuid|exists:religions,id',
            'gender' => 'required|in:male,female',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'nisn' => 'nullable|string|max:20',
            'nationality.id' => 'nullable|uuid|exists:nationalities,id',
            'village.id' => 'nullable|exists:villages,id',
            'transportation_mode.id' => 'nullable|uuid|exists:transportation_modes,id',
            'child_order' => 'nullable|integer|min:1',
            'family_status' => 'nullable|string|max:50',
            'special_need.id' => 'nullable|uuid|exists:special_needs,id',
            'special_condition.id' => 'nullable|uuid|exists:special_conditions,id',
            'file' => ['nullable', 'string', new Base64File],
            'additional_information' => 'nullable|string',
            'health_condition' => 'nullable|string',
            'hobby' => 'nullable|string',
            'has_kip' => 'nullable|boolean',
            'has_kps' => 'nullable|boolean',
            'eligible_for_kip' => 'nullable|boolean',
            'street'=> 'required|string|max:100',
            // Student Origin School
            'origin_schools' => 'nullable|array',
            'origin_schools.*.education_level.id' => 'required|uuid|exists:education_levels,id',
            'origin_schools.*.school_type.id' => 'required|uuid|exists:school_types,id',
            'origin_schools.*.school_name' => 'required|string|max:100',
            'origin_schools.*.npsn' => 'required|string|max:100',
            'origin_schools.*.address_name' => 'required|string',
            'origin_schools.*.graduation_year' => 'required|string',
            // Student Documents
            'documents' => 'nullable|array',
            'documents.*.document_type.id' => 'required|uuid|exists:document_types,id',
            'documents.*.name' => 'required|string',
            'documents.*.file' => ['required', 'string', new Base64File],


            // Student Parents
            'parents' => 'nullable|array',
            'parents.*.parent_type' => 'required|in:father,mother,guardian,other',
            'parents.*.full_name' => 'required|string|max:100',
            'parents.*.nik' => 'nullable|string|max:20',
            'parents.*.birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'parents.*.education_level.id' => 'nullable|uuid|exists:education_levels,id',
            'parents.*.occupation' => 'nullable|string',
            'parents.*.income_range.id' => 'nullable|uuid|exists:income_ranges,id',
            'parents.*.phone' => 'nullable|string|max:20',
            'parents.*.is_main_contact' => 'nullable|boolean',
            'parents.*.is_emergency_contact' => 'nullable|boolean',
            'parents.*.email' => 'nullable|string',
        ];
    }


    public function messages(): array
    {
        return [
            'registration_code.required' => 'Kode registrasi wajib diisi.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'gender.in' => 'Jenis kelamin harus salah satu dari male atau female.',

            'addresses.*.street.required' => 'alamat wajib diisi.',
            'parents.*.full_name.required' => 'Nama orang tua wajib diisi.',
            'parents.*.parent_type.in' => 'Tipe orang tua tidak valid.',
            'documents.*.document_type_id.required' => 'Jenis dokumen wajib diisi.',
            'documents.*.file.required' => 'File dokumen wajib diisi.',

            'contacts.*.value.required' => 'contact diisi.',
            'contacts.*.contact_type_id.required' => 'tipe contact wajib diisi.',
        ];
    }
}
