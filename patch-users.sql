-- ============================================================
--  PATCH: Tambah tabel users & data akun dashboard
--  Jalankan SETELAH setup-database.sql
-- ============================================================

USE kuesioner_klinik;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(255)    NOT NULL,
    email         VARCHAR(255)    NOT NULL UNIQUE,
    password      VARCHAR(255)    NOT NULL,
    role          ENUM('administrator','management','user') NOT NULL DEFAULT 'user',
    tipe_nakes    ENUM('dokter','perawat') NULL,
    nakes_id      BIGINT UNSIGNED NULL,
    aktif         TINYINT(1)      NOT NULL DEFAULT 1,
    remember_token VARCHAR(100)   NULL,
    created_at    TIMESTAMP       NULL,
    updated_at    TIMESTAMP       NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed Users ────────────────────────────────────────────────────────
-- Passwords are bcrypt hashed. Default passwords shown in comments.

INSERT INTO users (name, email, password, role, tipe_nakes, nakes_id, aktif, created_at, updated_at) VALUES
-- administrator   password: admin123
('Administrator',  'admin@klinik.com',        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator', NULL, NULL, 1, NOW(), NOW()),
-- management      password: mgmt123
('Manager Klinik', 'management@klinik.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'management',    NULL, NULL, 1, NOW(), NOW()),
-- dokter accounts password: dokter123
('dr. Andi Susanto, Sp.PD',  'andi.susanto@klinik.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dokter',  1, 1, NOW(), NOW()),
('dr. Siti Rahayu, Sp.A',    'siti.rahayu@klinik.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dokter',  2, 1, NOW(), NOW()),
('dr. Budi Santoso, Sp.OG',  'budi.santoso@klinik.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dokter',  3, 1, NOW(), NOW()),
('dr. Rina Kartika, Sp.JP',  'rina.kartika@klinik.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dokter',  4, 1, NOW(), NOW()),
('dr. Hendra Wijaya, Sp.B',  'hendra.wijaya@klinik.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dokter',  5, 1, NOW(), NOW()),
-- perawat accounts  password: perawat123
('Ns. Ani Wulandari, S.Kep',  'ani.wulandari@klinik.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'perawat', 1, 1, NOW(), NOW()),
('Ns. Budi Prasetyo, S.Kep',  'budi.prasetyo@klinik.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'perawat', 2, 1, NOW(), NOW()),
('Ns. Citra Dewi, S.Kep',     'citra.dewi@klinik.com',     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'perawat', 3, 1, NOW(), NOW()),
('Ns. Dian Pertiwi, S.Kep',   'dian.pertiwi@klinik.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'perawat', 4, 1, NOW(), NOW()),
('Ns. Eko Saputra, S.Kep',    'eko.saputra@klinik.com',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'perawat', 5, 1, NOW(), NOW());

-- ⚠️  PENTING: Hash di atas adalah hash Laravel default untuk string 'password'
--    Setelah import, ganti password via artisan tinker:
--
--    php artisan tinker
--    App\Models\User::where('role','administrator')->first()->update(['password' => Hash::make('admin123')]);
--    App\Models\User::where('role','management')->first()->update(['password' => Hash::make('mgmt123')]);
--    App\Models\User::where('tipe_nakes','dokter')->get()->each(fn($u) => $u->update(['password' => Hash::make('dokter123')]));
--    App\Models\User::where('tipe_nakes','perawat')->get()->each(fn($u) => $u->update(['password' => Hash::make('perawat123')]));

INSERT IGNORE INTO migrations (migration, batch) VALUES
    ('2024_01_01_000002_create_users_table', 1);

SELECT 'Patch users berhasil diterapkan!' AS status;
