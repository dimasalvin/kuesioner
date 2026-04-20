<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrator
        DB::table('users')->insert([
            'name'       => 'Administrator',
            'email'      => 'admin@klinik.com',
            'password'   => Hash::make('admin123'),
            'role'       => 'administrator',
            'tipe_nakes' => null,
            'nakes_id'   => null,
            'aktif'      => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Management
        DB::table('users')->insert([
            'name'       => 'Manager Klinik',
            'email'      => 'management@klinik.com',
            'password'   => Hash::make('mgmt123'),
            'role'       => 'management',
            'tipe_nakes' => null,
            'nakes_id'   => null,
            'aktif'      => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Dokter (ambil ID 1–5 dari tabel dokters)
        $dokterData = [
            [1, 'dr. Andi Susanto, Sp.PD',   'andi.susanto@klinik.com'],
            [2, 'dr. Siti Rahayu, Sp.A',      'siti.rahayu@klinik.com'],
            [3, 'dr. Budi Santoso, Sp.OG',    'budi.santoso@klinik.com'],
            [4, 'dr. Rina Kartika, Sp.JP',    'rina.kartika@klinik.com'],
            [5, 'dr. Hendra Wijaya, Sp.B',    'hendra.wijaya@klinik.com'],
        ];

        foreach ($dokterData as [$id, $name, $email]) {
            DB::table('users')->insert([
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make('dokter123'),
                'role'       => 'user',
                'tipe_nakes' => 'dokter',
                'nakes_id'   => $id,
                'aktif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Perawat (ambil ID 1–5 dari tabel perawats)
        $perawatData = [
            [1, 'Ns. Ani Wulandari, S.Kep',   'ani.wulandari@klinik.com'],
            [2, 'Ns. Budi Prasetyo, S.Kep',   'budi.prasetyo@klinik.com'],
            [3, 'Ns. Citra Dewi, S.Kep',      'citra.dewi@klinik.com'],
            [4, 'Ns. Dian Pertiwi, S.Kep',    'dian.pertiwi@klinik.com'],
            [5, 'Ns. Eko Saputra, S.Kep',     'eko.saputra@klinik.com'],
        ];

        foreach ($perawatData as [$id, $name, $email]) {
            DB::table('users')->insert([
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make('perawat123'),
                'role'       => 'user',
                'tipe_nakes' => 'perawat',
                'nakes_id'   => $id,
                'aktif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
