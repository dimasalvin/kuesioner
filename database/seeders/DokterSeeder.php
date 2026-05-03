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
            ['nama' => 'dr. Andi Susanto'],
            ['nama' => 'dr. Siti Rahayu'],
            ['nama' => 'dr. Budi Santoso'],
            ['nama' => 'dr. Rina Kartika'],
            ['nama' => 'dr. Hendra Wijaya'],
            ['nama' => 'dr. Maya Lestari'],
            ['nama' => 'dr. Fajar Nugroho'],
            ['nama' => 'dr. Dewi Anggraini'],
            ['nama' => 'dr. Rizky Pratama'],
            ['nama' => 'dr. Ayu Permata'],
        ];

        foreach ($dokter as $d) {
            DB::table('dokters')->insert([
                'nama'         => $d['nama'],
                'aktif'        => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
