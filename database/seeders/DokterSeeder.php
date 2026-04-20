<?php
// database/seeders/DokterSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        $dokter = [
            ['nama' => 'dr. Andi Susanto, Sp.PD',     'spesialisasi' => 'Penyakit Dalam'],
            ['nama' => 'dr. Siti Rahayu, Sp.A',        'spesialisasi' => 'Anak'],
            ['nama' => 'dr. Budi Santoso, Sp.OG',      'spesialisasi' => 'Obstetri & Ginekologi'],
            ['nama' => 'dr. Rina Kartika, Sp.JP',      'spesialisasi' => 'Jantung & Pembuluh Darah'],
            ['nama' => 'dr. Hendra Wijaya, Sp.B',      'spesialisasi' => 'Bedah Umum'],
            ['nama' => 'dr. Maya Lestari, Sp.S',       'spesialisasi' => 'Saraf'],
            ['nama' => 'dr. Fajar Nugroho, Sp.THT',    'spesialisasi' => 'THT'],
            ['nama' => 'dr. Dewi Anggraini, Sp.KK',    'spesialisasi' => 'Kulit & Kelamin'],
            ['nama' => 'dr. Rizky Pratama, Sp.M',      'spesialisasi' => 'Mata'],
            ['nama' => 'dr. Ayu Permata, Sp.KJ',       'spesialisasi' => 'Kesehatan Jiwa'],
        ];

        foreach ($dokter as $d) {
            DB::table('dokters')->insert([
                'nama'         => $d['nama'],
                'spesialisasi' => $d['spesialisasi'],
                'aktif'        => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
