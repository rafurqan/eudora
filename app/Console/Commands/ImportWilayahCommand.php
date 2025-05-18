<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportWilayahCommand extends Command
{

    //php artisan import:wilayah storage/app/wilayah1.xlsx   --> import wilayah - from excel file to db
    //php artisan import:wilayah storage/app/wilayah2.xlsx   --> import wilayah - from excel file to db
    protected $signature = 'import:wilayah {file}';
    protected $description = 'Import data wilayah dari file Excel';

    public function handle()
    {
        ini_set('memory_limit', '512M');
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: $file");
            return 1;
        }

        $this->info("Membaca file: $file");

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheetByName('REFERENSI');
        if (!$sheet) {
            $this->error("Sheet 'REFERENSI' tidak ditemukan di file Excel.");
            return 1;
        }

        $rows = $sheet->toArray(null, true, true, true);


        $provinces = [];
        $cities = [];
        $sub_districts = [];
        $villages = [];

        foreach ($rows as $row) {
            $kdprov = trim($row['A']);
            $provinsi = trim($row['B']);
            $kddati2 = trim($row['C']);
            $dati2 = trim($row['D']);
            $kdkec = trim($row['E']);
            $kecamatan = trim($row['F']);
            $kddesa = trim($row['G']);
            $desa = trim($row['H']);
            $kemendagri = trim($row['I']);


            $provinces[$kdprov] = ['id' => $kdprov, 'name' => $provinsi];
            $cities[$kddati2] = ['id' => $kddati2, 'name' => $dati2, 'province_id' => $kdprov];
            $sub_districts[$kdkec] = ['id' => $kdkec, 'name' => $kecamatan, 'city_id' => $kddati2];
            $villages[$kddesa] = ['id' => $kddesa, 'name' => $desa, 'sub_district_id' => $kdkec, 'kemendagri' => $kemendagri];
        }


        DB::transaction(function () use ($provinces, $cities, $sub_districts, $villages) {
            DB::table('provinces')->upsert(array_values($provinces), ['id'], ['name']);
            DB::table('cities')->upsert(array_values($cities), ['id'], ['name', 'province_id']);
            DB::table('sub_districts')->upsert(array_values($sub_districts), ['id'], ['name', 'city_id']);
            $chunkedVillages = array_chunk($villages, 1000);
            foreach ($chunkedVillages as $chunk) {
                DB::table('villages')->insert(array_values($chunk));
            }
        });

        $this->info("Import selesai: "
            . count($provinces) . " provinsi, "
            . count($cities) . " kota/kabupaten, "
            . count($sub_districts) . " kecamatan, "
            . count($villages) . " desa/kelurahan.");

        return 0;
    }
}
