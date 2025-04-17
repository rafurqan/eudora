<?php

namespace Database\Seeders;

use App\Models\Role;
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
    }
}
