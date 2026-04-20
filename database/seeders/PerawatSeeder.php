<?php
// database/seeders/PerawatSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerawatSeeder extends Seeder
{
    public function run(): void
    {
        $perawat = [
            ['nama' => 'Ns. Ani Wulandari, S.Kep'],
            ['nama' => 'Ns. Budi Prasetyo, S.Kep'],
            ['nama' => 'Ns. Citra Dewi, S.Kep'],
            ['nama' => 'Ns. Dian Pertiwi, S.Kep'],
            ['nama' => 'Ns. Eko Saputra, S.Kep'],
            ['nama' => 'Ns. Fitri Handayani, S.Kep'],
            ['nama' => 'Ns. Galih Kusuma, S.Kep'],
            ['nama' => 'Ns. Hani Safitri, S.Kep'],
            ['nama' => 'Ns. Indra Wahyudi, S.Kep'],
            ['nama' => 'Ns. Juna Maharani, S.Kep'],
        ];

        foreach ($perawat as $p) {
            DB::table('perawats')->insert([
                'nama'       => $p['nama'],
                'aktif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
