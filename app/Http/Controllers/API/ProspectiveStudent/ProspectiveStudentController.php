<?php

namespace App\Http\Controllers\API\ProspectiveStudent;

use App\Helpers\FileHelper;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProspectiveStudentRequest;
use App\Http\Requests\UpdateProspectiveStudentRequest;
use App\Models\ProspectiveStudent;
use App\Models\StudentDocument;
use App\Models\StudentOriginSchool;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ProspectiveStudentController extends Controller
{
    public function index()
    {
        $student = ProspectiveStudent::with(['nationality', 'specialNeed', 'religion', 'specialCondition', 'transportationMode', 'originSchools.schoolType', 'originSchools.educationLevel', 'parents.educationLevel', 'parents.incomeRange', 'documents.documentType', 'contacts.type', 'village.subDistrict.city.province'])->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($student, 'List Prospective Student');
    }

    public function show($id)
    {
        $student = ProspectiveStudent::with(['nationality', 'specialNeed', 'religion', 'specialCondition', 'transportationMode', 'originSchools', 'parents.educationLevel', 'parents.incomeRange', 'documents.documentType', 'contacts.type', 'village'])->findOrFail($id);
        if (!$student) {
            return ResponseFormatter::error(null, 'Data Not Found', 404);
        }
        return ResponseFormatter::success($student, 'View Prospective Student');
    }

    public function store(CreateProspectiveStudentRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $userId = $request->user()->id;

        DB::beginTransaction();
        try {

            $photoFilename = null;
            if (!empty($data['file'])) {
                $photoFilename = FileHelper::saveBase64File($data['file'], 'photos');
            }

            // Simpan calon siswa
            ProspectiveStudent::create([
                'id' => $id,
                'registration_code' => $data['registration_code'],
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'religion_id' => $data['religion']['id'] ?? null,
                'status' => 'waiting',
                'has_kip' => $data['has_kip'] ?? false,
                'has_kps' => $data['has_kps'] ?? false,
                'eligible_for_kip' => $data['eligible_for_kip'] ?? false,
                'child_order' => $data['child_order'] ?? null,
                'health_condition' => $data['health_condition'] ?? null,
                'hobby' => $data['hobby'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'],
                'birth_place' => $data['birth_place'],
                'transportation_mode_id' => $data['transportation_mode']['id'] ?? null,
                'birth_date' => $data['birth_date'],
                'nisn' => $data['nisn'] ?? null,
                'street' => $data['street'] ?? null,
                'nationality_id' => $data['nationality']['id'] ?? null,
                'village_id' => $data['village']['id'] ?? null,
                'family_position' => $data['family_position'] ?? null,
                'family_status' => $data['family_status'] ?? null,
                'special_need_id' => $data['special_need']['id'] ?? null,
                'special_condition_id' => $data['special_condition']['id'] ?? null,
                'photo_filename' => $photoFilename,
                'additional_information' => $data['additional_information'] ?? null,
                'created_by_id' => $userId,
            ]);

            // Simpan dokumen jika ada
            foreach ($data['documents'] ?? [] as $doc) {
                $docFilename = FileHelper::saveBase64File($doc['file'], 'student-documents');
                StudentDocument::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => ProspectiveStudent::class,
                    'created_by_id' => $userId,
                    'file_name' => $docFilename,
                    'name' => $doc['name'],
                    'document_type_id' => $doc['document_type']['id'] ?? null,

                ]);
            }



            // Simpan parent jika ada
            foreach ($data['parents'] ?? [] as $doc) {
                StudentParent::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => ProspectiveStudent::class,
                    'parent_type' => $doc['parent_type'],
                    'nik' => $doc['nik'],
                    'full_name' => $doc['full_name'],
                    'birth_year' => $doc['birth_year'] ?? null,
                    'education_level_id' => $doc['education_level']['id'] ?? null,
                    'occupation' => $doc['occupation'],
                    'income_range_id' => $doc['income_range']['id'] ?? null,
                    'phone' => $doc['phone'],
                    'is_main_contact' => $doc['is_main_contact'],
                    'is_emergency_contact' => $doc['is_emergency_contact'],
                    'created_by_id' => $userId,
                ]);
            }

            // Simpan asal sekolah jika ada
            foreach ($data['origin_schools'] ?? [] as $doc) {
                StudentOriginSchool::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => ProspectiveStudent::class,
                    'education_level_id' => $doc['education_level']['id'] ?? null,
                    'school_type_id' => $doc['school_type']['id'] ?? null,
                    'school_name' => $doc['school_name'],
                    'npsn' => $doc['npsn'],
                    'graduation_year' => $doc['graduation_year'],
                    'created_by_id' => $userId,
                    'address_name' => $doc['address_name'],

                ]);
            }

            DB::commit();

            return ResponseFormatter::success(['id' => $id], 'Success create Prospective Student');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error($e, 'Failed to store teacher', 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        $student = ProspectiveStudent::find($id);

        if ($student) {
            $student->status = 'rejected';
            $student->save();

            return ResponseFormatter::success(
                data: ['id' => $id],
                message: 'Prospective Student status updated to rejected'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }



    public function update(UpdateProspectiveStudentRequest $request, $id)
    {
        $data = $request->validated();
        $student = ProspectiveStudent::find($id);

        if (!$student) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $userId = $request->user()->id;
        $data['updated_by_id'] = $userId;

        DB::beginTransaction();
        try {
            if (!empty($data['file']) && strlen($data['file']) > 100) {
                $data['photo_filename'] = FileHelper::saveBase64File($data['file'], 'photos');
            }

            $student->update([
                'registration_code' => $data['registration_code'],
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'religion_id' => $data['religion']['id'] ?? null,
                'status' => 'waiting',
                'has_kip' => $data['has_kip'] ?? false,
                'has_kps' => $data['has_kps'] ?? false,
                'eligible_for_kip' => $data['eligible_for_kip'] ?? false,
                'child_order' => $data['child_order'] ?? null,
                'health_condition' => $data['health_condition'] ?? null,
                'hobby' => $data['hobby'] ?? null,
                'street' => $data['street'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'],
                'birth_place' => $data['birth_place'],
                'transportation_mode_id' => $data['transportation_mode']['id'] ?? null,
                'birth_date' => $data['birth_date'],
                'nisn' => $data['nisn'] ?? null,
                'nationality_id' => $data['nationality']['id'] ?? null,
                'village_id' => $data['village']['id'] ?? null,
                'family_position' => $data['child_order'] ?? null,
                'family_status' => $data['family_status'] ?? null,
                'special_need_id' => $data['special_need']['id'] ?? null,
                'special_condition_id' => $data['special_condition']['id'] ?? null,
                'photo_filename' => $data['photo_filename'] ?? $student->photo_filename,
                'additional_information' => $data['additional_information'] ?? null,
                'updated_by_id' => $userId,
            ]);

            // Update parents jika ada
            $existingParentIds = [];
            foreach ($data['parents'] ?? [] as $parent) {
                $parentId = $parent['id'] ?? Str::uuid();

                $model = StudentParent::updateOrCreate(
                    ['id' => $parentId],
                    [
                        'aggregate_id' => $id,
                        'aggregate_type' => ProspectiveStudent::class,
                        'parent_type' => $parent['parent_type'],
                        'nik' => $parent['nik'],
                        'full_name' => $parent['full_name'],
                        'birth_year' => $parent['birth_year'] ?? null,
                        'education_level_id' => $parent['education_level']['id'] ?? null,
                        'occupation' => $parent['occupation'],
                        'income_range_id' => $parent['income_range']['id'] ?? null,
                        'phone' => $parent['phone'],
                        'is_main_contact' => $parent['is_main_contact'],
                        'is_emergency_contact' => $parent['is_emergency_contact'],
                        'created_by_id' => $userId,
                        'updated_by_id' => $userId,
                    ]
                );

                $existingParentIds[] = $model->id;
            }

            StudentParent::where('aggregate_id', $id)
                ->whereNotIn('id', $existingParentIds)
                ->delete();

            // Update origin schools jika ada
            $existingSchoolIds = [];
            foreach ($data['origin_schools'] ?? [] as $school) {
                $schoolId = $school['id'] ?? Str::uuid();

                $model = StudentOriginSchool::updateOrCreate(
                    ['id' => $schoolId],
                    [
                        'aggregate_id' => $id,
                        'aggregate_type' => ProspectiveStudent::class,
                        'education_level_id' => $school['education_level']['id'] ?? null,
                        'school_type_id' => $school['school_type']['id'] ?? null,
                        'school_name' => $school['school_name'],
                        'npsn' => $school['npsn'],
                        'graduation_year' => $school['graduation_year'],
                        'address_name' => $school['address_name'],
                        'created_by_id' => $userId,
                        'updated_by_id' => $userId,
                    ]
                );

                $existingSchoolIds[] = $model->id;
            }

            StudentOriginSchool::where('aggregate_id', $id)
                ->whereNotIn('id', $existingSchoolIds)
                ->delete();

            // Update documents jika ada
            $existingDocumentIds = [];
            foreach ($data['documents'] ?? [] as $doc) {
                $docId = $doc['id'] ?? Str::uuid();

                $fileName = $doc['file'] ?? null;
                if ($fileName && strlen($fileName) > 100) {
                    $fileName = FileHelper::saveBase64File($fileName, 'student-documents');
                }

                $model = StudentDocument::updateOrCreate(
                    ['id' => $docId],
                    [
                        'aggregate_id' => $id,
                        'aggregate_type' => ProspectiveStudent::class,
                        'file_name' => $fileName ?? $doc['file_name'] ?? null,
                        'name' => $doc['name'],
                        'document_type_id' => $doc['document_type']['id'] ?? null,
                        'created_by_id' => $userId,
                        'updated_by_id' => $userId,
                    ]
                );

                $existingDocumentIds[] = $model->id;
            }

            StudentDocument::where('aggregate_id', $id)
                ->whereNotIn('id', $existingDocumentIds)
                ->delete();

            DB::commit();

            return ResponseFormatter::success(['id' => $id], 'Success update Prospective Student');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error($e->getMessage(), 'Failed to store student', 500);
        }
    }

}
