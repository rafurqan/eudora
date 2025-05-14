<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\FileHelper;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentRequest;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentOriginSchool;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StudentController extends Controller
{
    public function index()
    {
        $student = Student::with(['nationality', 'specialNeed', 'religion', 'specialCondition', 'transportationMode', 'addresses', 'originSchools', 'parents.educationLevel', 'parents.incomeRange', 'documents', 'contacts.type'])->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($student, 'List Student');
    }

    public function show($id)
    {
        $student = Student::with(['nationality', 'specialNeed', 'religion', 'specialCondition', 'transportationMode', 'addresses', 'originSchools', 'parents.educationLevel', 'parents.incomeRange', 'documents', 'contacts.type'])->findOrFail($id);
        if (!$student) {
            return ResponseFormatter::error(null, 'Data Not Found', 404);
        }
        return ResponseFormatter::success($student, 'View Student');
    }

    public function store(CreateStudentRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $userId = $request->user()->id;

        DB::beginTransaction();
        try {

            $photoFilename = null;
            if (!empty($data['photo'])) {
                $photoFilename = FileHelper::saveBase64File($data['photo'], 'photos');
            }

            // Simpan student
            Student::create([
                'id' => $id,
                'registration_code' => $data['registration_code'],
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'religion_id' => $data['religion_id'],
                'status' => 'waiting',
                'child_order' => $data['child_order'] ?? null,
                'health_condition' => $data['health_condition'] ?? null,
                'hobby' => $data['hobby'] ?? null,
                'gender' => $data['gender'],
                'birth_place' => $data['birth_place'],
                'transportation_mode_id' => $data['transportation_mode_id'],
                'birth_date' => $data['birth_date'],
                'nisn' => $data['nisn'] ?? null,
                'nationality_id' => $data['nationality_id'],
                'family_position' => $data['child_order'] ?? null,
                'family_status' => $data['family_status'] ?? null,
                'special_need_id' => $data['special_need_id'] ?? null,
                'special_condition_id' => $data['special_condition_id'] ?? null,
                'photo_filename' => $photoFilename,
                'additional_information' => $data['additional_information'] ?? null,
                'created_by_id' => $userId,
            ]);

            // Simpan dokumen jika ada
            foreach ($data['documents'] ?? [] as $doc) {
                $docFilename = FileHelper::saveBase64File($data['photo'], 'student-documents');

                StudentDocument::create([
                    'id' => Str::uuid(),
                    'student_id' => $id,
                    'created_by_id' => $userId,
                    'document_type_id' => $doc['document_type_id'],
                    'filename' => $docFilename,
                ]);
            }

            // Simpan dokumen jika ada
            foreach ($data['contacts'] ?? [] as $doc) {
                StudentContact::create([
                    'id' => Str::uuid(),
                    'student_id' => $id,
                    'value' => $doc['value'],
                    'created_by_id' => $userId,
                    'contact_type_id' => $doc['contact_type_id'],
                ]);
            }

            // Simpan address jika ada
            foreach ($data['addresses'] ?? [] as $doc) {
                StudentAddress::create([
                    'id' => Str::uuid(),
                    'student_id' => $id,
                    'created_by_id' => $userId,
                    'document_type_id' => $doc['document_type_id'],
                    'street' => $doc['street'],
                ]);
            }

            foreach ($data['parents'] ?? [] as $doc) {
                StudentParent::create([
                    'id' => Str::uuid(),
                    'student_id' => $id,
                    'parent_type' => $doc['parent_type'],
                    'nik' => $doc['nik'],
                    'full_name' => $doc['full_name'],
                    'birth_year' => $doc['birth_year'],
                    'education_level_id' => $doc['education_level_id'],
                    'occupation' => $doc['occupation'],
                    'income_range_id' => $doc['income_range_id'],
                    'phone' => $doc['phone'],
                    'created_by_id' => $userId,
                    'is_guardian' => $doc['is_guardian'],
                ]);
            }

            foreach ($data['origin_schools'] ?? [] as $doc) {
                StudentOriginSchool::create([
                    'id' => Str::uuid(),
                    'student_id' => $id,
                    'education_level_id' => $doc['education_level_id'],
                    'school_type_id' => $doc['school_type_id'],
                    'school_name' => $doc['school_name'],
                    'npsn' => $doc['npsn'],
                    'created_by_id' => $userId,
                    'address_name' => $doc['address_name'],

                ]);
            }

            DB::commit();

            return ResponseFormatter::success(['id' => $id], 'Success create Student');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error($e, 'Failed to store student', 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        $student = Student::find($id);

        if ($student) {
            $student->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(CreateStudentRequest $request, $id)
    {
        $request->validated();

        $student = Student::find($id);

        if (!$student) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['updated_by_id'] = $request->user()->id;
        $data['file_name'] = '';

        $student->update($data);

        return ResponseFormatter::success([
            'id' => $student->id
        ], 'Success update Student');
    }

}
