<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\EducationLevel;
use App\Models\IncomeRange;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\Role;
use App\Models\SchoolType;
use App\Models\SpecialCondition;
use App\Models\SpecialNeed;
use App\Models\Student;
use App\Models\StudentOriginSchool;
use App\Models\StudentParent;
use App\Models\Teacher;
use App\Models\TransportationMode;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $roleId = uuid_create();
        $userId = uuid_create();
        Role::create([
            'id' => $roleId,
            'name' => 'Super Admin',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        User::factory()->create([
            'id' => $userId,
            'name' => 'Test User',
            'role_id' => $roleId,
            'email' => 'test@example.com',
            'password' => Hash::make('123')
        ]);
        $religionId = uuid_create();
        Religion::create([
            'id' => $religionId,
            'name' => 'Islam',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        $nationalityId = uuid_create();
        Nationality::create([
            'id' => $nationalityId,
            'name' => 'WNI',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        $specialConditionId = uuid_create();
        SpecialCondition::create([
            'id' => $specialConditionId,
            'name' => 'Anak Guru',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        $specialNeedId = uuid_create();
        SpecialNeed::create([
            'id' => $specialNeedId,
            'name' => 'Ya',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $modaTransportationId = uuid_create();
        TransportationMode::create([
            'id' => $modaTransportationId,
            'name' => 'Motor',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $schoolTypeId = uuid_create();
        SchoolType::create([
            'id' => $schoolTypeId,
            'name' => 'Swasta',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        $educationLevelId = uuid_create();
        EducationLevel::create([
            'id' => $educationLevelId,
            'name' => 'SD',
            'level' => 'Dasar',
            'status' => 'ACTIVE',
            'description' => 'Sekolah Dasar',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $documentTypeId = uuid_create();
        DocumentType::create([
            'id' => $documentTypeId,
            'name' => 'KTP',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $incomeRangeId = uuid_create();
        IncomeRange::create([
            'id' => $incomeRangeId,
            'name' => '1000000-5000000',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $studentId = uuid_create();
        Student::create([
            'id' => $studentId,
            'registration_code' => 'REG-2025-04-0001',
            'full_name' => 'kaizi Anzar',
            'nickname' => 'Kaizi',
            'religion_id' => $religionId,
            'gender' => 'male',
            'birth_place' => 'PADANG',
            'birth_date' => now(),
            'status' => 'waiting',
            'nisn' => '',
            'nationality_id' => $nationalityId,
            'transportation_mode_id' => $modaTransportationId,
            'child_order' => 1,
            'family_status' => 'ANAK',
            'special_need_id' => $specialNeedId,
            'special_condition_id' => $specialConditionId,
            'additional_information' => 'additional condition',
            'health_condition' => 'sehat walafiat',
            'hobby' => 'olahraga',
            'special_need' => 'tidak ada',
            'has_kip' => false,
            'eligible_for_kip' => false,
            'created_by_id' => $userId
        ]);

        $studentOriginSchoolId = uuid_create();
        StudentOriginSchool::create([
            'id' => $studentOriginSchoolId,
            'student_id' => $studentId,
            'school_name' => 'SMP Negeri 1',
            'school_type_id' => $schoolTypeId,
            'npsn' => '0012345678',
            'address_name' => 'Padang',
            'education_level_id' => $educationLevelId,
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $studentParent = uuid_create();
        StudentParent::create([
            'id' => $studentParent,
            'student_id' => $studentId,
            'parent_type' => 'father',
            'full_name' => 'Budi Anzar',
            'nik' => '1234567890123456',
            'birth_year' => 1980,
            'occupation' => 'Guru',
            'income_range_id' => $incomeRangeId,
            'phone' => '081234567890',
            'is_guardian'=> true,
            'education_level_id' => $educationLevelId,
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        $teacherId = uuid_create();
        Teacher::create([
            'id' => $teacherId,
            'name' => 'Dila Anzar',
            'nip' => '1234567890123456',
            'birth_date' => now(),
            'birth_place' => 'Padang',
            'education_level_id' => $educationLevelId,
            'graduated_from' => 'Universitas Andalas',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
    }
}
