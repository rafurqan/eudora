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

        $query = ClassMembership::with(['studentClass', 'student', 'prospectiveStudent'])
            ->orderBy('created_at', 'desc');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('student', function ($q2) use ($keyword) {
                    $q2->where('name', 'ilike', '%' . $keyword . '%'); // PostgreSQL case-insensitive
                })->orWhereHas('prospectiveStudent', function ($q2) use ($keyword) {
                    $q2->where('name', 'ilike', '%' . $keyword . '%');
                });
            });
        }

        $classMemberships = $query->paginate($perPage);

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

            // memastikan class_id ada
            $studentClass = StudentClass::find($data['student_class_id']);

            if (!$studentClass) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Kelas tidak ditemukan.', 404);
            }

            $capacity = $studentClass->capacity;

            $activeMembershipsCount = ClassMembership::where('student_class_id', $data['student_class_id'])
                ->whereNull('end_at')
                ->count();

            // Cek apakah kapasitas kelas sudah penuh
            if ($activeMembershipsCount >= $capacity) {
                DB::rollBack();
                return ResponseFormatter::error(
                    data: null,
                    message: 'Kapasitas kelas sudah penuh (' . $activeMembershipsCount . '/' . $capacity . '). Tidak dapat menambahkan anggota baru.',
                    code: 422
                );
            }

            // Tutup keanggotaan aktif sebelumnya (jika ada)
            $query = ClassMembership::whereNull('end_at');

            if (isset($data['student_id'])) {
                $query->where('student_id', $data['student_id']);
            } else {
                $query->where('prospective_student_id', $data['prospective_student_id']);
            }

            $query->update([
                'end_at' => $data['start_at'], // Bisa juga pakai now() tergantung kebutuhan
            ]);

            // Simpan keanggotaan baru
            $membership = ClassMembership::create([
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

        $joinStudentClass = $studentClassId ? "AND cm.student_class_id = '{$studentClassId}'" : '';
        $whereKeyword = $keyword
            ? "AND (LOWER(s.full_name) LIKE LOWER('%{$keyword}%') OR LOWER(s.nisn) LIKE LOWER('%{$keyword}%') OR LOWER(s.gender) LIKE LOWER('%{$keyword}%'))"
            : '';

        // Raw SQL Query untuk mengambil data siswa dan calon siswa
        $sql = "
            (SELECT s.id, s.full_name, s.gender, s.nisn, 'student' AS type
             FROM students s
             LEFT JOIN class_memberships cm ON cm.student_id = s.id AND cm.end_at IS NULL {$joinStudentClass}
             LEFT JOIN student_classes sc ON sc.id = cm.student_class_id
             WHERE 1=1 {$whereKeyword})

            UNION ALL

            (SELECT ps.id, ps.full_name, ps.gender, ps.nisn, 'prospective_student' AS type
             FROM prospective_students ps
             LEFT JOIN class_memberships cm ON cm.prospective_student_id = ps.id AND cm.end_at IS NULL {$joinStudentClass}
             LEFT JOIN student_classes sc ON sc.id = cm.student_class_id
             WHERE 1=1 {$whereKeyword})

            LIMIT {$perPage} OFFSET {$offset};
        ";

        // Jalankan query
        $students = DB::select($sql);

        // Ambil ID berdasarkan tipe
        $studentIds = collect($students)->where('type', 'student')->pluck('id')->toArray();
        $prospectiveIds = collect($students)->where('type', 'prospective_student')->pluck('id')->toArray();

        // Preload relasi studentClass untuk masing-masing model
        $studentsEloquent = Student::with('activeClassMembership.studentClass')
            ->whereIn('id', $studentIds)
            ->get();

        $prospectivesEloquent = ProspectiveStudent::with('activeClassMembership.studentClass')
            ->whereIn('id', $prospectiveIds)
            ->get();

        // Gabungkan hasil preload ke raw data
        foreach ($students as $key => $item) {
            if ($item->type === 'student') {
                $eloquent = $studentsEloquent->firstWhere('id', $item->id);
            } else {
                $eloquent = $prospectivesEloquent->firstWhere('id', $item->id);
            }

            // Tambahkan data relasi jika ada
            $students[$key]->active_class = $eloquent->activeClassMembership?->studentClass;

        }

        // Query total data (tanpa limit/offset)
        $countSql = "
            SELECT COUNT(*) as total FROM (
                (SELECT s.id
                 FROM students s
                 LEFT JOIN class_memberships cm ON cm.student_id = s.id AND cm.end_at IS NULL {$joinStudentClass}
                 LEFT JOIN student_classes sc ON sc.id = cm.student_class_id
                 WHERE 1=1 {$whereKeyword})

                UNION ALL

                (SELECT ps.id
                 FROM prospective_students ps
                 LEFT JOIN class_memberships cm ON cm.prospective_student_id = ps.id AND cm.end_at IS NULL {$joinStudentClass}
                 LEFT JOIN student_classes sc ON sc.id = cm.student_class_id
                 WHERE 1=1 {$whereKeyword})
            ) AS subquery_count;
        ";

        // Eksekusi query count
        $total = DB::selectOne($countSql)->total;

        return ResponseFormatter::success(
            $students,
            'Filtered and paginated students and prospective students',
            $total,
            $page,
            $perPage
        );
    }
}
