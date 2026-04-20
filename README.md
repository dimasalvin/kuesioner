# Kuesioner Kepuasan Pasien Klinik
> Aplikasi kuesioner berbasis Laravel 10+ untuk mengukur kepuasan pasien terhadap fasilitas klinik, dokter, dan perawat.

---

## ✨ Fitur
- **6-step multi-page form** dengan progress indicator
- **Star rating interaktif** (hover & tap) untuk mobile
- **Conditional complaint field** — muncul hanya jika pilih "Ya"
- **Session-based state** — data aman saat navigasi
- **Validasi client & server side** lengkap
- **Mobile-first design** — dioptimalkan untuk layar HP
- **Database relasional** — 6 tabel dengan foreign key

---

## 🔧 Requirements
- PHP >= 8.1
- Laravel >= 10.x
- MySQL / MariaDB / SQLite
- Composer

---

## 🚀 Instalasi

### 1. Buat Project Laravel Baru
```bash
composer create-project laravel/laravel kuesioner-klinik
cd kuesioner-klinik
```

### 2. Copy File Project
Salin semua file dari folder ini ke dalam project Laravel:
```
app/Http/Controllers/KuesionerController.php
app/Models/Kuesioner.php
app/Models/Dokter.php
app/Models/Perawat.php
app/Models/KuesionerKlinik.php
app/Models/KuesionerDokter.php
app/Models/KuesionerPerawat.php
database/migrations/2024_01_01_000001_create_kuesioner_tables.php
database/seeders/DatabaseSeeder.php
database/seeders/DokterSeeder.php
database/seeders/PerawatSeeder.php
resources/views/layouts/app.blade.php
resources/views/kuesioner/step1-identitas.blade.php
resources/views/kuesioner/step2-klinik.blade.php
resources/views/kuesioner/step3-dokter.blade.php
resources/views/kuesioner/step4-perawat.blade.php
resources/views/kuesioner/step5-komplain.blade.php
resources/views/kuesioner/step6-thankyou.blade.php
routes/web.php
public/css/app.css
public/js/app.js
```

### 3. Konfigurasi .env
```env
APP_NAME="Kuesioner Klinik"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kuesioner_klinik
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat Database
```bash
# MySQL
mysql -u root -p -e "CREATE DATABASE kuesioner_klinik;"

# Atau gunakan SQLite (lebih mudah untuk development)
# Ubah DB_CONNECTION=sqlite di .env
# touch database/database.sqlite
```

### 5. Jalankan Migration & Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 6. Jalankan Server
```bash
php artisan serve
# Buka: http://localhost:8000
```

---

## 📱 Testing di HP
```bash
# Jalankan di IP lokal agar bisa diakses HP di jaringan yang sama
php artisan serve --host=0.0.0.0 --port=8000

# Cari IP komputer kamu:
# Windows: ipconfig
# Mac/Linux: ifconfig
# Buka di HP: http://192.168.x.x:8000
```

---

## 🗂️ Struktur Database

| Tabel | Keterangan |
|-------|------------|
| `dokters` | Data master dokter |
| `perawats` | Data master perawat |
| `kuesioners` | Data utama pasien + komplain |
| `kuesioner_kliniks` | 15 penilaian fasilitas klinik |
| `kuesioner_dokters` | 15 penilaian dokter + kritik |
| `kuesioner_perawats` | 15 penilaian perawat + kritik |

---

## 📊 Contoh Query Laporan

```sql
-- Rata-rata penilaian klinik
SELECT ROUND(AVG((q1+q2+q3+q4+q5+q6+q7+q8+q9+q10+q11+q12+q13+q14+q15)/15.0), 2)
AS rata_rata_klinik FROM kuesioner_kliniks;

-- Rata-rata per dokter
SELECT d.nama, ROUND(AVG((kd.q1+kd.q2+kd.q3+kd.q4+kd.q5+kd.q6+kd.q7+kd.q8+kd.q9+kd.q10+kd.q11+kd.q12+kd.q13+kd.q14+kd.q15)/15.0), 2) AS rata_rata
FROM kuesioner_dokters kd
JOIN dokters d ON d.id = kd.dokter_id
GROUP BY d.id, d.nama
ORDER BY rata_rata DESC;

-- Total kuesioner per hari
SELECT DATE(created_at) as tanggal, COUNT(*) as total
FROM kuesioners
GROUP BY DATE(created_at)
ORDER BY tanggal DESC;
```

---

## 🛠️ Kustomisasi

### Menambah/mengubah pertanyaan
Edit array `$pertanyaan` di masing-masing view blade:
- `step2-klinik.blade.php` — pertanyaan klinik
- `step3-dokter.blade.php` — pertanyaan dokter
- `step4-perawat.blade.php` — pertanyaan perawat

### Menambah dokter/perawat
```bash
# Via seeder
php artisan db:seed --class=DokterSeeder

# Atau via Tinker
php artisan tinker
App\Models\Dokter::create(['nama' => 'dr. Baru', 'spesialisasi' => 'Umum']);
```

### Mengubah warna tema
Edit CSS variables di `public/css/app.css`:
```css
:root {
    --teal:  #2BBFA4;  /* warna utama */
    --coral: #FF6B6B;  /* warna aksen merah */
    --sky:   #5BA4E5;  /* warna biru */
    --gold:  #F4C842;  /* warna bintang */
}
```
