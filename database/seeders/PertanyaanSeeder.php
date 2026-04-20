<?php
// database/seeders/PertanyaanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PertanyaanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Klinik ────────────────────────────────────────────────
        $klinik = [
            'Apakah klinik bersih dan terawat?',
            'Apakah suasana ruang tunggu nyaman?',
            'Apakah fasilitas klinik memadai?',
            'Apakah area parkir tersedia dengan cukup?',
            'Apakah papan informasi/petunjuk jelas?',
            'Apakah jam operasional klinik sesuai?',
            'Apakah proses pendaftaran mudah?',
            'Apakah waktu tunggu tidak terlalu lama?',
            'Apakah sistem antrian berjalan baik?',
            'Apakah administrasi pelayanan baik?',
            'Apakah klinik mudah dijangkau?',
            'Apakah apotek klinik lengkap?',
            'Apakah biaya pelayanan sesuai?',
            'Apakah klinik menerima BPJS / asuransi?',
            'Apakah toilet bersih dan terawat?',
        ];

        // ── Dokter ────────────────────────────────────────────────
        $dokter = [
            'Apakah Nakes informatif dalam menjelaskan penyakit?',
            'Apakah dokter mendengarkan keluhan Anda dengan baik?',
            'Apakah dokter memeriksa dengan teliti?',
            'Apakah dokter memberikan diagnosis yang jelas?',
            'Apakah penjelasan resep obat mudah dipahami?',
            'Apakah dokter bersikap profesional?',
            'Apakah dokter menghormati privasi pasien?',
            'Apakah dokter tersedia sesuai jadwal?',
            'Apakah waktu konsultasi cukup?',
            'Apakah dokter memberikan saran gaya hidup?',
            'Apakah Anda puas dengan tindakan medis?',
            'Apakah dokter memberikan informed consent?',
            'Apakah dokter mudah dihubungi jika ada pertanyaan?',
            'Apakah dokter bekerja sama dengan tenaga lain dengan baik?',
            'Apakah Nakes ramah dan bersahabat?',
        ];

        // ── Perawat ───────────────────────────────────────────────
        $perawat = [
            'Apakah Nakes informatif dalam menjelaskan prosedur?',
            'Apakah perawat cepat tanggap terhadap kebutuhan Anda?',
            'Apakah perawat bersikap empati?',
            'Apakah perawat melakukan tindakan dengan hati-hati?',
            'Apakah perawat menjelaskan prosedur dengan jelas?',
            'Apakah perawat menjaga kebersihan saat tindakan?',
            'Apakah perawat menghormati privasi pasien?',
            'Apakah perawat memperkenalkan diri sebelum tindakan?',
            'Apakah perawat komunikatif?',
            'Apakah perawat memberikan dukungan psikologis?',
            'Apakah perawat tepat waktu?',
            'Apakah perawat memberikan edukasi kesehatan?',
            'Apakah perawat bekerja sama dengan tim dengan baik?',
            'Apakah Anda merasa aman dengan tindakan perawat?',
            'Apakah Nakes ramah dan bersahabat?',
        ];

        foreach (['klinik' => $klinik, 'dokter' => $dokter, 'perawat' => $perawat] as $kategori => $list) {
            foreach ($list as $i => $teks) {
                DB::table('pertanyaan_kuesioner')->insert([
                    'kategori'   => $kategori,
                    'teks'       => $teks,
                    'urutan'     => $i + 1,
                    'aktif'      => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
