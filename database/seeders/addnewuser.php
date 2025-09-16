<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Education;
use App\Models\EducationLevel;
use App\Models\IncomeRange;
use App\Models\Nationality;
use App\Models\Occupation;
use App\Models\ParentType;
use App\Models\Program;
use App\Models\Religion;
use App\Models\Role;
use App\Models\SchoolType;
use App\Models\Service;
use App\Models\SpecialCondition;
use App\Models\SpecialNeed;
use App\Models\TransportationMode;
use App\Models\User;
use App\Models\Rate;
use App\Models\ProgramSchool;
use App\Models\Donor;
use App\Models\DonationType;
use App\Models\Grant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class addnewuser extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userId = uuid_create();
        User::factory()->create([
            'id' => $userId,
            'name' => 'Gilang',
            'role_id' => 'd5dce4e6-21e3-4b3e-b55c-d70bfe8e4b59',
            'email' => 'gilang@example.com',
            'password' => Hash::make('123')
        ]);
        User::factory()->create([
            'id' => uuid_create(),
            'name' => 'Ridwan',
            'role_id' => 'd5dce4e6-21e3-4b3e-b55c-d70bfe8e4b59',
            'email' => 'ridwan@example.com',
            'password' => Hash::make('123')
        ]);
        User::factory()->create([
            'id' => uuid_create(),
            'name' => 'Imron',
            'role_id' => 'd5dce4e6-21e3-4b3e-b55c-d70bfe8e4b59',
            'email' => 'imron@example.com',
            'password' => Hash::make('123')
        ]);
        User::factory()->create([
            'id' => uuid_create(),
            'name' => 'Zayn',
            'role_id' => 'd5dce4e6-21e3-4b3e-b55c-d70bfe8e4b59',
            'email' => 'zayn@example.com',
            'password' => Hash::make('123')
        ]);
    }
}
