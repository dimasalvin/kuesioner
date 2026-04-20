<?php
/**
 * set_passwords.php
 * Letakkan di ROOT project Laravel, jalankan: php set_passwords.php
 * Kompatibel PHP 7.x dan 8.x (tidak pakai arrow function)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "\n=== Set Password User Klinik ===\n\n";

$users = array(
    'admin@klinik.com'          => 'admin123',
    'management@klinik.com'     => 'mgmt123',
    'andi.susanto@klinik.com'   => 'dokter123',
    'siti.rahayu@klinik.com'    => 'dokter123',
    'budi.santoso@klinik.com'   => 'dokter123',
    'rina.kartika@klinik.com'   => 'dokter123',
    'hendra.wijaya@klinik.com'  => 'dokter123',
    'ani.wulandari@klinik.com'  => 'perawat123',
    'budi.prasetyo@klinik.com'  => 'perawat123',
    'citra.dewi@klinik.com'     => 'perawat123',
    'dian.pertiwi@klinik.com'   => 'perawat123',
    'eko.saputra@klinik.com'    => 'perawat123',
);

foreach ($users as $email => $password) {
    $updated = DB::table('users')
        ->where('email', $email)
        ->update(array(
            'password'   => Hash::make($password),
            'updated_at' => date('Y-m-d H:i:s'),
        ));

    if ($updated) {
        echo "OK         " . $email . "  =>  " . $password . "\n";
    } else {
        echo "NOT FOUND  " . $email . "\n";
    }
}

echo "\nSelesai!\n";
echo "\nAkun yang bisa digunakan:\n";
echo "  admin@klinik.com         / admin123\n";
echo "  management@klinik.com    / mgmt123\n";
echo "  andi.susanto@klinik.com  / dokter123\n";
echo "  ani.wulandari@klinik.com / perawat123\n\n";
