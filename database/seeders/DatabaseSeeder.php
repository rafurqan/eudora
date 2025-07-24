<?php

namespace Database\Seeders;

use App\Models\DocumentType;
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
        User::factory()->create([
            'id' => uuid_create(),
            'name' => 'Test User 2',
            'role_id' => $roleId,
            'email' => 'test2@example.com',
            'password' => Hash::make('123')
        ]);
        Religion::create([
            'id' => uuid_create(),
            'name' => 'Islam',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Religion::create([
            'id' => uuid_create(),
            'name' => 'Hindu',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Religion::create([
            'id' => uuid_create(),
            'name' => 'Kristen',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Religion::create([
            'id' => uuid_create(),
            'name' => 'Khatolik',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Religion::create([
            'id' => uuid_create(),
            'name' => 'Budha',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Nationality::create([
            'id' => uuid_create(),
            'name' => 'WNI',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        Nationality::create([
            'id' => uuid_create(),
            'name' => 'WNA',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        SpecialCondition::create([
            'id' => uuid_create(),
            'name' => 'Anak Guru',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        SpecialNeed::create([
            'id' => uuid_create(),
            'name' => 'Ya',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        SpecialNeed::create([
            'id' => uuid_create(),
            'name' => 'Tidak',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Motor',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Jalan Kaki',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Kendaraan Pribadi',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Kendaraan Umum / Angkot / Bus / Perahu Umum',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Jemputan Sekolah',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Kereta Api',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Ojek',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Andong / dokar / delman / becak',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Perahu / Sampan',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        TransportationMode::create([
            'id' => uuid_create(),
            'name' => 'Transportasi Lainnya',
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

        SchoolType::create([
            'id' => uuid_create(),
            'name' => 'Negeri',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $programId = uuid_create();
        Program::create([
            'id' => $programId,
            'name' => 'MI Tahfidz',
            'level' => 'Dasar',
            'status' => 'ACTIVE',
            'description' => 'Khusus tahfidz',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '01',
            'name' => 'Tidak Sekolah',
            'level' => 'Dasar',
            'status' => 'ACTIVE',
            'description' => 'Sekolah Dasar',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '02',
            'name' => 'SD Sederajat',
            'level' => 'Dasar',
            'status' => 'ACTIVE',
            'description' => 'Sekolah Dasar',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '03',
            'name' => 'SMP Sederajat',
            'level' => 'Menengah',
            'status' => 'ACTIVE',
            'description' => 'Sekolah Dasar',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '04',
            'name' => 'SMA Sederajat',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => 'Sekolah Dasar',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '05',
            'name' => 'D1',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '06',
            'name' => 'D2',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '07',
            'name' => 'D3',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '08',
            'name' => 'D4 / S1',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '09',
            'name' => 'S2',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        EducationLevel::create([
            'id' => uuid_create(),
            'code' => '10',
            'name' => 'S3',
            'level' => 'Atas',
            'status' => 'ACTIVE',
            'description' => ' ',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);



        DocumentType::create([
            'id' => uuid_create(),
            'code' => '01',
            'name' => 'Akta Kelahiran',
            'is_required' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '02',
            'name' => 'Kartu Keluarga',
            'is_required' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '03',
            'name' => 'Ijazah / Surat Keterangan Lulus',
            'is_required' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '04',
            'name' => 'Kartu Identitas Anak',
            'is_required' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '05',
            'name' => 'Kartu Indonesia Pintar (KIP) (jika ada)',
            'is_required' => 'N',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '06',
            'name' => 'Kartu Program Keluarga Harapan (PKH) / KKS (jika ada)',
            'is_required' => 'N',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        DocumentType::create([
            'id' => uuid_create(),
            'code' => '07',
            'name' => 'Surat Pindah / Mutasi Sekolah (jika siswa pindahan)',
            'is_required' => 'N',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);


        $data = [
            ['01', 'Tidak Bekerja'],
            ['02', 'Nelayan'],
            ['03', 'Petani'],
            ['04', 'Peternak'],
            ['05', 'PNS/TNI/POLRI'],
            ['06', 'Karyawan Swasta'],
            ['07', 'Pedagang Kecil'],
            ['08', 'Pedagang Besar'],
            ['09', 'Wiraswasta'],
            ['10', 'Wirausaha'],
            ['11', 'Buruh'],
            ['12', 'Pensiunan'],
            ['13', 'Tenaga Kerja Indonesia (TKI)'],
            ['14', 'Sudah Meninggal'],
            ['99', 'Lainnya'],
        ];

        foreach ($data as [$code, $name]) {
            Occupation::create([
                'id' => uuid_create(),
                'code' => $code,
                'name' => $name,
                'created_by_id' => $userId,
                'created_at' => now(),
                'updated_at' => null,
            ]);
        }

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '01',
            'name' => 'Kurang dari Rp 500.000,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '02',
            'name' => 'Rp 500.000,00 - Rp 999.999,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '03',
            'name' => 'Rp 1.000.000,00 - Rp 1.999.999,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '04',
            'name' => 'Rp 2.000.000,00 - Rp 4.999.999,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '05',
            'name' => 'Rp 5.000.000,00 - Rp 20.000.000,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '06',
            'name' => 'Lebih dari Rp 20.000.000,00',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        IncomeRange::create([
            'id' => uuid_create(),
            'code' => '07',
            'name' => 'Tidak berpenghasilan',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);


        ParentType::create([
            'id' => uuid_create(),
            'code' => '01',
            'name' => 'Ayah',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '02',
            'name' => 'Ibu',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '03',
            'name' => 'Wali',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '04',
            'name' => 'Kakek',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '05',
            'name' => 'Nenek',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '06',
            'name' => 'Kakak',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '07',
            'name' => 'Paman',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '08',
            'name' => 'Bibi',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '09',
            'name' => 'Sepupu',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        ParentType::create([
            'id' => uuid_create(),
            'code' => '10',
            'name' => 'Lainnya',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);

        $programId = uuid_create();
        ProgramSchool::create([
            'id' => $programId,
            'name' => 'Sekolah Menengah Atas',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        // Layanan
        $servicesId = uuid_create();
        Service::create([
            'id' => $servicesId,
            'name' => 'SPP Bulanan',
            'is_active' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null,
        ]);
        // Tarif
        $rateId = uuid_create();
        Rate::create([
            'id' => $rateId,
            'service_id' => $servicesId,
            'child_ids' => null,
            'program_id' => $programId,
            'price' => 150000,
            'is_active' => 'Y',
            'created_by_id' => $userId,
            'created_at' => now(),
            'updated_at' => null
        ]);
        // Donor
        $donorId = uuid_create();
        Donor::create([
            'id' => $donorId,
            'name' => 'John Doe',
            'is_active' => 'Y',
            'created_by_id' => $userId,
            'updated_by_id' => null,
            'created_at' => now(),
            'updated_at' => null
        ]);

        // DonationType
        $donationTypeId = uuid_create();
        DonationType::create([
            'id' => $donationTypeId,
            'name' => 'Scholarship',
            'created_by_id' => $userId,
            'updated_by_id' => null,
            'created_at' => now(),
            'updated_at' => null
        ]);

        // Grant
        // $grantId = uuid_create();
        // Grant::create([
        //     'id' => $grantId,
        //     'donor_id' => $donorId,
        //     'donation_type_id' => $donationTypeId,
        //     'is_active' => 'Y',
        //     'description' => 'Science Grant',
        //     'total_funds' => 5000,
        //     'grant_expiration_date' => now()->addYear(),
        //     'created_by_id' => $userId,
        //     'created_at' => now(),
        //     'updated_at' => null
        // ]);

        $this->call([
            MessageTemplateSeeder::class,
        ]);
    }
}
