<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ClassMembership;
use App\Http\Requests\CreateClassMembershipRequest;
use App\Models\ProspectiveStudent;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ClassMembershipController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 10);

        $classMemberships = $this->getClassMemberships($keyword, $perPage);

        return ResponseFormatter::success($classMemberships, 'List Class Membership');
    }

    public function store(CreateClassMembershipRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();

        if (!(isset($data['student_id']) xor isset($data['prospective_student_id']))) {
            return ResponseFormatter::error(
                data: null,
                message: 'Isi salah satu: student_id atau prospective_student_id, bukan keduanya.',
                code: 422
            );
        }

        DB::beginTransaction();

        try {
            $userId = $request->user()->id;

            $studentClass = StudentClass::find($data['student_class_id']);
            if (!$studentClass) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Kelas tidak ditemukan.', 404);
            }

            $capacity = $studentClass->capacity;

            $activeMembershipsCount = ClassMembership::where('student_class_id', $data['student_class_id'])
                ->whereNull('end_at')
                ->count();

            if ($activeMembershipsCount >= $capacity) {
                DB::rollBack();
                return ResponseFormatter::error(
                    data: null,
                    message: 'Kapasitas kelas sudah penuh (' . $activeMembershipsCount . '/' . $capacity . '). Tidak dapat menambahkan anggota baru.',
                    code: 422
                );
            }

            $this->updateExistingMembership($data);

            $membership = $this->createMembership($data, $id, $userId);

            DB::commit();

            return ResponseFormatter::success([
                'id' => $membership->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return ResponseFormatter::error(
                data: null,
                message: 'Gagal menyimpan membership: ' . $e->getMessage(),
                code: 500
            );
        }
    }

    public function getAllUniqueStudentsWithActiveClass()
    {
        $studentClassId = request()->get('student_class_id');
        $keyword = request()->get('keyword');
        $perPage = (int) request()->get('per_page', 10);
        $page = (int) request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $students = $this->getFilteredStudents($studentClassId, $keyword, $perPage, $offset);
        $total = $this->getTotalFilteredStudents($studentClassId, $keyword);

        return ResponseFormatter::success(
            $students,
            'Filtered and paginated students and prospective students',
            $total,
            $page,
            $perPage
        );
    }

    // Function to get Class Memberships with optional keyword filtering and pagination
    private function getClassMemberships($keyword, $perPage)
    {
        $query = ClassMembership::with(['studentClass', 'student', 'prospectiveStudent'])
            ->orderBy('created_at', 'desc');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('student', function ($q2) use ($keyword) {
                    $q2->where('name', 'ilike', '%' . $keyword . '%');
                })->orWhereHas('prospectiveStudent', function ($q2) use ($keyword) {
                    $q2->where('name', 'ilike', '%' . $keyword . '%');
                });
            });
        }

        return $query->paginate($perPage);
    }

    private function updateExistingMembership($data)
    {
        $query = ClassMembership::whereNull('end_at');

        if (isset($data['student_id'])) {
            $query->where('student_id', $data['student_id']);
        } else {
            $query->where('prospective_student_id', $data['prospective_student_id']);
        }

        $query->update([
            'end_at' => $data['start_at'],
        ]);
    }

    private function createMembership($data, $id, $userId)
    {
        return ClassMembership::create([
            'id' => $id,
            'student_class_id' => $data['student_class_id'],
            'student_id' => $data['student_id'] ?? null,
            'prospective_student_id' => $data['prospective_student_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'] ?? null,
            'created_by_id' => $userId,
            'created_at' => now(),
        ]);
    }

    private function getFilteredStudents($studentClassId, $keyword, $perPage, $offset)
    {
        // Menyusun filter keyword untuk pencarian
        $whereKeyword = $this->buildKeywordFilter($keyword);

        // Menyusun query SQL berdasarkan apakah student_class_id ada atau tidak
        $joinType = $studentClassId ? 'INNER JOIN' : 'LEFT JOIN';

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
        {$joinType} class_memberships cm
            ON (
                (app.person_type = 'student' AND cm.student_id = app.person_id)
                OR
                (app.person_type = 'prospective_student' AND cm.prospective_student_id = app.person_id)
            )
            AND cm.end_at IS NULL
        ";

        // Jika student_class_id ada, tambahkan filter untuk student_class_id
        if ($studentClassId) {
            $sql .= " WHERE cm.student_class_id = '{$studentClassId}'";
        }

        // Batasi hasil berdasarkan per_page dan offset untuk paginasi
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        // Menjalankan query
        $students = DB::select($sql);

        // Menyusun ID siswa untuk query eloquent
        $studentIds = collect($students)->where('type', 'student')->pluck('id')->toArray();
        $prospectiveIds = collect($students)->where('type', 'prospective_student')->pluck('id')->toArray();

        // Mendapatkan data siswa dengan relasi class
        $studentsEloquent = Student::with('activeClassMembership.studentClass')
            ->whereIn('id', $studentIds)
            ->get();

        $prospectivesEloquent = ProspectiveStudent::with('activeClassMembership.studentClass')
            ->whereIn('id', $prospectiveIds)
            ->get();

        // Menggabungkan data dengan eloquent hasil query
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

    private function getTotalFilteredStudents($studentClassId, $keyword)
    {
        // Menyusun filter keyword untuk pencarian
        $whereKeyword = $this->buildKeywordFilter($keyword);

        // Menentukan join type berdasarkan kondisi student_class_id
        $joinType = $studentClassId ? 'INNER JOIN' : 'LEFT JOIN';

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
        {$joinType} class_memberships cm
            ON (
                (app.person_type = 'student' AND cm.student_id = app.person_id)
                OR
                (app.person_type = 'prospective_student' AND cm.prospective_student_id = app.person_id)
            )
            AND cm.end_at IS NULL
    ";

        // Jika student_class_id ada, tambahkan filter untuk student_class_id
        if ($studentClassId) {
            $countSql .= " WHERE cm.student_class_id = '{$studentClassId}'";
        }

        // Menjalankan query untuk menghitung total data siswa
        return DB::selectOne($countSql)->total_count;
    }

}
