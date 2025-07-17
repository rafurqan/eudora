<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\FileHelper;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\ProspectiveStudent;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentOriginSchool;
use App\Models\StudentParent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StudentController extends Controller
{

    public function index(Request $request)
    {

        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 10);

        $paginated = $this->getStudent($keyword, $perPage);
        $summary = $this->getStudentSummary($keyword);

        return ResponseFormatter::success(
            $paginated->items(),
            'List Student',
            $paginated->total(),
            $paginated->currentPage(),
            $paginated->perPage(),
            [
                'summary' => $summary,
            ]
        );
    }

    private function getStudent($keyword, $perPage)
    {
        $query = Student::with([
            'nationality',
            'specialNeed',
            'religion',
            'specialCondition',
            'transportationMode',
            'originSchools.schoolType',
            'originSchools.educationLevel',
            'parents.educationLevel',
            'parents.incomeRange',
            'parents.parentType',
            'documents.documentType',
            'village.subDistrict.city.province',
            'invoices.items'
        ])
            ->orderBy('created_at', 'desc');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'ilike', '%' . $keyword . '%')
                    ->orWhere('nickname', 'ilike', '%' . $keyword . '%')
                    ->orWhere('registration_code', 'ilike', '%' . $keyword . '%');
            });
        }

        return $query->paginate($perPage);
    }

    private function getStudentSummary($keyword)
    {
        $query = Student::with(['specialCondition', 'specialNeed']);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'ilike', '%' . $keyword . '%')
                    ->orWhere('nickname', 'ilike', '%' . $keyword . '%')
                    ->orWhere('registration_code', 'ilike', '%' . $keyword . '%');
            });
        }

        $students = $query->get();

        $total = $students->count();

        $totalApproved = $students->where('status', 'active')->count();

        $activeStudents = $students->where('status', 'active');

        $totalYatim = $activeStudents->filter(function ($student) {
            return $student->specialCondition && stripos($student->specialCondition->name, 'yatim') !== false;
        })->count();

        $totalAnakGuru = $activeStudents->filter(function ($student) {
            return $student->specialCondition && stripos($student->specialCondition->name, 'anak guru') !== false;
        })->count();

        $totalSpecialNeed = $activeStudents->filter(function ($student) {
            return $student->specialNeed !== null;
        })->count();
        return [
            'total' => $total,
            'approved' => $totalApproved,
            'orphan' => $totalYatim,
            'teacher_child' => $totalAnakGuru,
            'special_needs' => $totalSpecialNeed,
        ];
    }

    public function getAllStudent()
    {
        $keyword = request()->get('keyword');
        $perPage = (int) request()->get('per_page', 10);
        $page = (int) request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $students = $this->getFilteredStudents($keyword, $perPage, $offset);
        $total = $this->getTotalFilteredStudents($keyword);

        return ResponseFormatter::success(
            $students,
            'Filtered and paginated students and prospective students',
            $total,
            $page,
            $perPage
        );
    }


    private function getFilteredStudents($keyword, $perPage, $offset)
    {
        // Menyusun filter keyword untuk pencarian
        $whereKeyword = $this->buildKeywordFilter($keyword);

        // Query SQL
        $sql = "
        WITH AllPossiblePersons AS (
            SELECT
                s.id AS person_id,
                s.full_name,
                s.gender,
                s.nisn,
                'student' AS person_type
            FROM students s
            WHERE 1=1 {$whereKeyword['student']}

            UNION ALL

            SELECT
                ps.id AS person_id,
                ps.full_name,
                ps.gender,
                ps.nisn,
                'prospective_student' AS person_type
            FROM prospective_students ps
            WHERE ps.id NOT IN (
                SELECT prospective_student_id FROM students WHERE prospective_student_id IS NOT NULL
            )
            {$whereKeyword['prospective_student']}
        )
        SELECT
            app.person_id AS id,
            app.full_name,
            app.gender,
            app.nisn,
            app.person_type AS type,
            cm.student_class_id
        FROM AllPossiblePersons app
        LEFT JOIN class_memberships cm
            ON (
                (app.person_type = 'student' AND cm.student_id = app.person_id)
                OR
                (app.person_type = 'prospective_student' AND cm.prospective_student_id = app.person_id)
            )
            AND cm.end_at IS NULL
        ";

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $students = DB::select($sql);

        $studentIds = collect($students)->where('type', 'student')->pluck('id')->toArray();
        $prospectiveIds = collect($students)->where('type', 'prospective_student')->pluck('id')->toArray();

        $studentsEloquent = Student::with('activeClassMembership.studentClass')
            ->whereIn('id', $studentIds)
            ->get();

        $prospectivesEloquent = ProspectiveStudent::with('activeClassMembership.studentClass')
            ->whereIn('id', $prospectiveIds)
            ->get();

        foreach ($students as $key => $item) {
            $eloquent = $item->type === 'student'
                ? $studentsEloquent->firstWhere('id', $item->id)
                : $prospectivesEloquent->firstWhere('id', $item->id);

            $students[$key]->active_class = $eloquent->activeClassMembership?->studentClass;
        }

        return $students;
    }


    private function buildKeywordFilter($keyword)
    {
        $filterKeyword = [
            'student' => $keyword ? "AND (LOWER(s.full_name) LIKE LOWER('%{$keyword}%') OR LOWER(s.nisn) LIKE LOWER('%{$keyword}%') OR LOWER(s.gender) LIKE LOWER('%{$keyword}%'))" : '',
            'prospective_student' => $keyword ? "AND (LOWER(ps.full_name) LIKE LOWER('%{$keyword}%') OR LOWER(ps.nisn) LIKE LOWER('%{$keyword}%') OR LOWER(ps.gender) LIKE LOWER('%{$keyword}%'))" : ''
        ];

        return $filterKeyword;
    }

    private function getTotalFilteredStudents($keyword)
    {
        // Menyusun filter keyword untuk pencarian
        $whereKeyword = $this->buildKeywordFilter($keyword);

        // Query untuk menghitung total data siswa
        $countSql = "
        WITH AllPossiblePersons AS (
            SELECT
                s.id AS person_id,
                s.full_name,
                s.gender,
                s.nisn,
                'student' AS person_type
            FROM students s
            WHERE 1=1 {$whereKeyword['student']}

            UNION ALL

            SELECT
                ps.id AS person_id,
                ps.full_name,
                ps.gender,
                ps.nisn,
                'prospective_student' AS person_type
            FROM prospective_students ps
            WHERE ps.id NOT IN (
                SELECT prospective_student_id FROM students WHERE prospective_student_id IS NOT NULL
            )
            {$whereKeyword['prospective_student']}
        )
        SELECT COUNT(*) AS total_count
        FROM AllPossiblePersons app
        LEFT JOIN class_memberships cm
            ON (
                (app.person_type = 'student' AND cm.student_id = app.person_id)
                OR
                (app.person_type = 'prospective_student' AND cm.prospective_student_id = app.person_id)
            )
            AND cm.end_at IS NULL
    ";


        // Menjalankan query untuk menghitung total data siswa
        return DB::selectOne($countSql)->total_count;
    }

    public function show($id)
    {
        $student = Student::with([
            'nationality',
            'specialNeed',
            'religion',
            'specialCondition',
            'transportationMode',
            'originSchools.schoolType',
            'originSchools.educationLevel',
            'parents.educationLevel',
            'parents.incomeRange',
            'parents.parentType',
            'classMemberships.studentClass.teacher',
            'documents.documentType',
            'village.subDistrict.city.province',
            'invoices.items'
        ])
            ->findOrFail($id);

        return ResponseFormatter::success($student, 'Student details retrieved successfully.');
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
                'religion_id' => $data['religion']['id'] ?? null,
                'status' => 'waiting',
                'has_kip' => $data['has_kip'] ?? false,
                'has_kps' => $data['has_kps'] ?? false,
                'eligible_for_kip' => $data['eligible_for_kip'] ?? false,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'street' => $data['street'] ?? null,
                'child_order' => $data['child_order'] ?? null,
                'health_condition' => $data['health_condition'] ?? null,
                'hobby' => $data['hobby'] ?? null,
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
                'photo_filename' => $photoFilename,
                'additional_information' => $data['additional_information'] ?? null,
                'created_by_id' => $userId,
            ]);

            // Simpan dokumen jika ada
            foreach ($data['documents'] ?? [] as $doc) {
                $docFilename = FileHelper::saveBase64File($data['photo'], 'student-documents');

                StudentDocument::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => Student::class,
                    'created_by_id' => $userId,
                    'document_type_id' => $doc['document_type']['id'] ?? null,
                    'filename' => $docFilename,
                ]);
            }

            // Simpan dokumen jika ada
            foreach ($data['contacts'] ?? [] as $doc) {
                StudentContact::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => Student::class,
                    'value' => $doc['value'],
                    'created_by_id' => $userId,
                    'contact_type_id' => $doc['contact_type']['id'] ?? null,
                ]);
            }


            foreach ($data['parents'] ?? [] as $doc) {
                StudentParent::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => Student::class,
                    'parent_type_id' => $doc['parent_type']['id'] ?? null,
                    'nik' => $doc['nik'],
                    'full_name' => $doc['full_name'],
                    'birth_year' => $doc['birth_year'] ?? null,
                    'education_level_id' => $doc['education_level']['id'] ?? null,
                    'occupation' => $doc['occupation'],
                    'income_range_id' => $doc['income_range']['id'] ?? null,
                    'phone' => $doc['phone'],
                    'created_by_id' => $userId,
                ]);
            }

            foreach ($data['origin_schools'] ?? [] as $doc) {
                StudentOriginSchool::create([
                    'id' => Str::uuid(),
                    'aggregate_id' => $id,
                    'aggregate_type' => Student::class,
                    'education_level_id' => $doc['education_level']['id'] ?? null,
                    'school_type_id' => $doc['school_type']['id'] ?? null,
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


    public function update(UpdateStudentRequest $request, $id)
    {
        $data = $request->validated();
        $student = Student::find($id);

        if (!$student) {
            return ResponseFormatter::error(null, 'Student not found.', 404);
        }

        $userId = $request->user()->id;

        DB::beginTransaction();
        try {
            $currentPhotoFilename = $student->photo_filename;

            if (!empty($data['file'])) {
                if (strlen($data['file']) > 100) {

                    if ($currentPhotoFilename) {
                        FileHelper::deleteFile('photos', $currentPhotoFilename);
                    }
                    $data['photo_filename'] = FileHelper::saveBase64File($data['file'], 'photos');
                } else {
                    $data['photo_filename'] = $currentPhotoFilename;
                }
            } else {
                $data['photo_filename'] = $currentPhotoFilename;
            }


            // Memperbarui model Student
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
                'photo_filename' => $data['photo_filename'] ?? null,
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
                    ['id' => $parentId, 'aggregate_id' => $id, 'aggregate_type' => Student::class], // Kunci untuk mencari/membuat
                    [
                        'parent_type_id' => $parentData['parent_type']['id'] ?? null,
                        'nik' => $parentData['nik'],
                        'full_name' => $parentData['full_name'],
                        'birth_year' => $parentData['birth_year'] ?? null,
                        'education_level_id' => $parentData['education_level']['id'] ?? null,
                        'occupation' => $parentData['occupation'],
                        'email' => $parentData['email'] ?? null,
                        'income_range_id' => $parentData['income_range']['id'] ?? null,
                        'phone' => $parentData['phone'],
                        'address' => $parentData['address'] ?? null,
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
                ->where('aggregate_type', Student::class)
                ->whereNotIn('id', $existingParentIds)
                ->delete();

            // Sinkronisasi Data Asal Sekolah
            $existingSchoolIds = [];
            foreach ($data['origin_schools'] ?? [] as $schoolData) {
                $schoolId = $schoolData['id'] ?? Str::uuid()->toString();
                $schoolModel = StudentOriginSchool::updateOrCreate(
                    ['id' => $schoolId, 'aggregate_id' => $id, 'aggregate_type' => Student::class],
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
                ->where('aggregate_type', Student::class)
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
                    ['id' => $docId, 'aggregate_id' => $id, 'aggregate_type' => Student::class],
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
                ->where('aggregate_type', Student::class)
                ->whereNotIn('id', $existingDocumentIds)
                ->delete();

            DB::commit();

            return ResponseFormatter::success(
                ['id' => $id],
                'Student updated successfully.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(
                null,
                'Failed to update student. Please try again later. Error: ' . $e->getMessage(), // Detail error untuk dev
                500
            );
        }
    }

    public function changeStatus($id, Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,waiting,active,inactive',
        ]);
        try {
            $student = Student::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error(null, 'siswa tidak ditemukan.', 404);
        }

        DB::beginTransaction();
        try {
            $student->status = $validated['status'];
            $student->save();
            DB::commit();

            return ResponseFormatter::success([
                'id' => $student->id,
                'status' => $student->status,
            ], 'Status siswa berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal mengubah status siswa. Error: ' . $e->getMessage(), 500);
        }
    }
}
