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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ProspectiveStudentController extends Controller
{

    public function index()
    {
        // Memuat calon siswa beserta relasi
        $students = ProspectiveStudent::with([
            'nationality',
            'specialNeed',
            'religion',
            'specialCondition',
            'transportationMode',
            'originSchools.schoolType',
            'originSchools.educationLevel',
            'parents.educationLevel',
            'parents.incomeRange',
            'documents.documentType',
            'contacts.type',
            'village.subDistrict.city.province'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseFormatter::success($students, 'List of Prospective Students retrieved successfully.');
    }


    public function show($id)
    {
        $student = ProspectiveStudent::with([
            'nationality',
            'specialNeed',
            'religion',
            'specialCondition',
            'transportationMode',
            'originSchools',
            'parents.educationLevel',
            'parents.incomeRange',
            'documents.documentType',
            'contacts.type',
            'village'
        ])
            ->findOrFail($id);

        return ResponseFormatter::success($student, 'Prospective Student details retrieved successfully.');
    }

    public function store(CreateProspectiveStudentRequest $request)
    {
        $data = $request->validated();
        $userId = $request->user()->id;
        $prospectiveStudentId = Str::uuid()->toString();

        DB::beginTransaction();
        try {
            $photoFilename = null;
            if (!empty($data['file'])) {
                $photoFilename = FileHelper::saveBase64File($data['file'], 'photos/prospective_students');
            }

            // Membuat model ProspectiveStudent
            ProspectiveStudent::create([
                'id' => $prospectiveStudentId,
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

            // Menyimpan data relasional (polymorphic)
            // Dokumen Siswa
            foreach ($data['documents'] ?? [] as $doc) {
                $docFilename = FileHelper::saveBase64File($doc['file'], 'documents/prospective_students');
                StudentDocument::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $prospectiveStudentId,
                    'aggregate_type' => ProspectiveStudent::class,
                    'created_by_id' => $userId,
                    'file_name' => $docFilename,
                    'name' => $doc['name'],
                    'document_type_id' => $doc['document_type']['id'] ?? null,
                ]);
            }

            // Data Orang Tua/Wali
            foreach ($data['parents'] ?? [] as $parentData) {
                StudentParent::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $prospectiveStudentId,
                    'aggregate_type' => ProspectiveStudent::class,
                    'parent_type' => $parentData['parent_type'],
                    'nik' => $parentData['nik'],
                    'full_name' => $parentData['full_name'],
                    'birth_year' => $parentData['birth_year'] ?? null,
                    'education_level_id' => $parentData['education_level']['id'] ?? null,
                    'occupation' => $parentData['occupation'],
                    'income_range_id' => $parentData['income_range']['id'] ?? null,
                    'phone' => $parentData['phone'],
                    'is_main_contact' => $parentData['is_main_contact'],
                    'is_emergency_contact' => $parentData['is_emergency_contact'],
                    'created_by_id' => $userId,
                ]);
            }

            // Data Asal Sekolah
            foreach ($data['origin_schools'] ?? [] as $schoolData) {
                StudentOriginSchool::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $prospectiveStudentId,
                    'aggregate_type' => ProspectiveStudent::class,
                    'education_level_id' => $schoolData['education_level']['id'] ?? null,
                    'school_type_id' => $schoolData['school_type']['id'] ?? null,
                    'school_name' => $schoolData['school_name'],
                    'npsn' => $schoolData['npsn'],
                    'graduation_year' => $schoolData['graduation_year'],
                    'created_by_id' => $userId,
                    'address_name' => $schoolData['address_name'],
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(
                ['id' => $prospectiveStudentId],
                'Prospective Student created successfully.',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Failed to create prospective student. Please try again later. Error: ' . $e->getMessage(),
                500
            );
        }
    }


    public function destroy($id)
    {
        $student = ProspectiveStudent::find($id);

        if (!$student) {
            return ResponseFormatter::error(null, 'Prospective Student not found.', 404);
        }

        $student->status = 'rejected';
        $student->save();

        return ResponseFormatter::success(
            ['id' => $id],
            'Prospective Student status updated to rejected.'
        );
    }

    public function update(UpdateProspectiveStudentRequest $request, $id)
    {
        $data = $request->validated();
        $student = ProspectiveStudent::find($id);

        if (!$student) {
            return ResponseFormatter::error(null, 'Prospective Student not found.', 404);
        }

        $userId = $request->user()->id;

        DB::beginTransaction();
        try {
            $currentPhotoFilename = $student->photo_filename;
            if (!empty($data['file'])) {
                if (strlen($data['file']) > 100) {
                    // Hapus foto lama jika ada dan file baru diunggah
                    if ($currentPhotoFilename) {
                        FileHelper::deleteFile('photos/prospective_students', $currentPhotoFilename);
                    }
                    $data['photo_filename'] = FileHelper::saveBase64File($data['file'], 'photos/prospective_students');
                } else {
                    $data['photo_filename'] = $currentPhotoFilename;
                }
            } else {
                // Jika 'file' tidak ada dalam request, pertahankan foto yang sudah ada.
                $data['photo_filename'] = $currentPhotoFilename;
            }


            // Memperbarui model ProspectiveStudent
            $student->update([
                'registration_code' => $data['registration_code'],
                'full_name' => $data['full_name'],
                'nickname' => $data['nickname'] ?? null,
                'religion_id' => $data['religion']['id'] ?? null,
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
                'additional_information' => $data['additional_information'] ?? null,
                'created_by_id' => $student->created_by_id,
                'updated_by_id' => $userId,
            ]);

            // Sinkronisasi Data Orang Tua/Wali
            $existingParentIds = [];
            foreach ($data['parents'] ?? [] as $parentData) {
                $parentId = $parentData['id'] ?? Str::uuid()->toString(); // Gunakan ID yang ada atau buat baru
                $parentModel = StudentParent::updateOrCreate(
                    ['id' => $parentId, 'aggregate_id' => $id, 'aggregate_type' => ProspectiveStudent::class], // Kunci untuk mencari/membuat
                    [
                        'parent_type' => $parentData['parent_type'],
                        'nik' => $parentData['nik'],
                        'full_name' => $parentData['full_name'],
                        'birth_year' => $parentData['birth_year'] ?? null,
                        'education_level_id' => $parentData['education_level']['id'] ?? null,
                        'occupation' => $parentData['occupation'],
                        'income_range_id' => $parentData['income_range']['id'] ?? null,
                        'phone' => $parentData['phone'],
                        'is_main_contact' => $parentData['is_main_contact'],
                        'is_emergency_contact' => $parentData['is_emergency_contact'],
                        'created_by_id' => $userId,
                        'updated_by_id' => $userId,
                    ]
                );
                $existingParentIds[] = $parentModel->id;
            }
            // Hapus data orang tua yang tidak ada dalam request (sinkronisasi)
            StudentParent::where('aggregate_id', $id)
                ->where('aggregate_type', ProspectiveStudent::class)
                ->whereNotIn('id', $existingParentIds)
                ->delete();

            // Sinkronisasi Data Asal Sekolah
            $existingSchoolIds = [];
            foreach ($data['origin_schools'] ?? [] as $schoolData) {
                $schoolId = $schoolData['id'] ?? Str::uuid()->toString();
                $schoolModel = StudentOriginSchool::updateOrCreate(
                    ['id' => $schoolId, 'aggregate_id' => $id, 'aggregate_type' => ProspectiveStudent::class],
                    [
                        'education_level_id' => $schoolData['education_level']['id'] ?? null,
                        'school_type_id' => $schoolData['school_type']['id'] ?? null,
                        'school_name' => $schoolData['school_name'],
                        'npsn' => $schoolData['npsn'],
                        'graduation_year' => $schoolData['graduation_year'],
                        'address_name' => $schoolData['address_name'],
                        'updated_by_id' => $userId,
                        'created_by_id' => $userId,
                    ]
                );
                $existingSchoolIds[] = $schoolModel->id;
            }
            StudentOriginSchool::where('aggregate_id', $id)
                ->where('aggregate_type', ProspectiveStudent::class)
                ->whereNotIn('id', $existingSchoolIds)
                ->delete();

            // Sinkronisasi Dokumen Siswa
            $existingDocumentIds = [];
            foreach ($data['documents'] ?? [] as $docData) {
                $docId = $docData['id'] ?? Str::uuid()->toString();
                $newFileName = null;

                // Cek apakah ada file baru yang diunggah untuk dokumen ini
                if (!empty($docData['file']) && strlen($docData['file']) > 100) { // Heuristik base64
                    // Hapus file lama jika ada sebelum menyimpan yang baru
                    if (!empty($docData['id'])) {
                        $existingDoc = StudentDocument::find($docData['id']);
                        if ($existingDoc && $existingDoc->file_name) {
                            FileHelper::deleteFile($existingDoc->file_name, 'documents/prospective_students');
                        }
                    }
                    $newFileName = FileHelper::saveBase64File($docData['file'], 'documents/prospective_students');
                }

                $documentModel = StudentDocument::updateOrCreate(
                    ['id' => $docId, 'aggregate_id' => $id, 'aggregate_type' => ProspectiveStudent::class],
                    [
                        'file_name' => $newFileName ?? ($docData['file_name'] ?? ($docData['id'] ? StudentDocument::find($docData['id'])->file_name : null)),
                        'name' => $docData['name'],
                        'document_type_id' => $docData['document_type']['id'] ?? null,
                        'updated_by_id' => $userId,
                        'created_by_id' => $userId,
                    ]
                );
                $existingDocumentIds[] = $documentModel->id;
            }
            StudentDocument::where('aggregate_id', $id)
                ->where('aggregate_type', ProspectiveStudent::class)
                ->whereNotIn('id', $existingDocumentIds)
                ->delete();

            DB::commit();

            return ResponseFormatter::success(
                ['id' => $id],
                'Prospective Student updated successfully.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Failed to update prospective student. Please try again later. Error: ' . $e->getMessage(), // Detail error untuk dev
                500
            );
        }
    }
}
