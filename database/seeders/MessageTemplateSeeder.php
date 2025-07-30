<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        MessageTemplate::create([
            'name' => 'invoice_reminder',
            'body' => 'Yth. Orang Tua/Wali dari {{name}},
                        Ini adalah pengingat untuk pembayaran tagihan dengan nomor faktur {{code}} sebesar Rp {{total}}. Pembayaran jatuh tempo pada {{due_date}}.

                        Terima kasih,
                        Bagian Keuangan Sekolah'
        ]);
    }
}

