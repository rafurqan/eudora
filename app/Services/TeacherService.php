<?php
namespace App\Services;

use App\Interfaces\TeacherRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TeacherService
{
    public function __construct(
        protected TeacherRepositoryInterface $teacherRepository
    ) {
    }

    public function index()
    {
        return $this->teacherRepository->index();
    }

    public function show(string $id)
    {
        return $this->teacherRepository->show($id);
    }

    public function store(array $data)
    {
        $id = uuid_create();
        $userId = auth()->id();
        $data['id'] = $id;
        $data['education_level_id'] = is_array($data['education_level'])
            ? $data['education_level']['id'] ?? null
            : ($data['education_level']->id ?? null);
        $data['created_at'] = now();
        $data['created_by_id'] = $userId;
        DB::beginTransaction();
        try {
            $teacher = $this->teacherRepository->store($data);

            DB::commit();
            return $teacher;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to create student", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data)
    {
        $data["updated_by_id"] = auth()->id();
        $data['education_level_id'] = is_array($data['education_level'])
            ? $data['education_level']['id'] ?? null
            : ($data['education_level']->id ?? null);
        unset($data['education_level']);
        DB::beginTransaction();
        try {

            $student = $this->teacherRepository->update($data, $id);
            Log::info("Student updated", ['id' => $id]);
            DB::commit();
            return $student;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to update student", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id)
    {
        try {
            return $this->teacherRepository->delete($id);
        } catch (Exception $e) {
            Log::error("Failed to delete student", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
