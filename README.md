# 🏥 Kuesioner Kepuasan Pasien — Klinik Gigi

Aplikasi kuesioner berbasis **Laravel 12 + Blade + MySQL** untuk mengukur kepuasan pasien terhadap fasilitas klinik, dokter, dan perawat.

---

## ✨ Fitur Utama

### 📋 Kuesioner Publik (6 Step)
- Form multi-step dengan progress indicator
- Star rating interaktif (1–5 bintang)
- Penilaian: Klinik → Dokter → Perawat → Komplain
- Kritik & saran opsional per nakes
- Session-based state management

### 📊 Dashboard Admin
- Statistik keseluruhan (total kuesioner, komplain, nakes, user)
- Chart distribusi penilaian (Baik/Cukup/Kurang)
- Kelola User (CRUD + role management)
- Kelola Pertanyaan Kuesioner (drag-drop reorder, toggle aktif)
- Detail Penilaian per kuesioner
- Daftar Komplain + Kritik & Saran

### 📈 Dashboard Management
- Chart distribusi + ranking nakes
- Penilaian per individu nakes (chart interaktif)
- Data kuesioner (read-only)
- Komplain + Kritik & Saran

### ⭐ Dashboard User (Nakes)
- Penilaian pribadi (rata-rata, chart, per pertanyaan)
- Kritik & saran yang ditujukan ke diri sendiri
- Penilaian klinik (overview)
- Detail penilaian per kuesioner

### 🔔 Notifikasi
- Real-time notification untuk admin & management saat ada komplain baru
- Mark as read (individual & bulk)

---

## 🔧 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Vanilla JS, CSS Custom |
| Database | MySQL 8.0+ |
| Auth | Laravel built-in (session) |
| Cache | File/Redis (configurable) |

---

## 🚀 Instalasi

### 1. Clone & Install Dependencies
```bash
git clone <repo-url> kuesioner-klinik
cd kuesioner-klinik
composer install
```

### 2. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kuesioner_klinik
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Buat Database & Jalankan Migration
```bash
mysql -u root -p -e "CREATE DATABASE kuesioner_klinik;"
php artisan migrate
php artisan db:seed
```

### 4. Jalankan Server
```bash
php artisan serve
```

Buka: http://localhost:8000

---

## 👤 Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@klinik.com | admin123 |
| Management | management@klinik.com | mgmt123 |
| Dokter | andi.susanto@klinik.com | dokter123 |
| Perawat | ani.wulandari@klinik.com | perawat123 |

---

## 🗂️ Struktur Database

| Tabel | Keterangan |
|-------|------------|
| `users` | Akun login (admin, management, nakes) |
| `dokters` | Data master dokter |
| `perawats` | Data master perawat |
| `kuesioners` | Data utama pasien + komplain |
| `kuesioner_kliniks` | Jawaban klinik (JSON) + rata-rata |
| `kuesioner_dokters` | Jawaban dokter (JSON) + rata-rata + kritik saran |
| `kuesioner_perawats` | Jawaban perawat (JSON) + rata-rata + kritik saran |
| `pertanyaan_kuesioner` | Master pertanyaan (kategori, teks, urutan, aktif) |
| `notifications` | Notifikasi komplain untuk admin/management |

### Format Jawaban (JSON)
```json
// kuesioner_kliniks.jawaban — key = pertanyaan_id, value = nilai (1-5)
{"1": 5, "2": 4, "3": 5, "4": 3, "5": 5, ...}

// kuesioner_kliniks.rata_rata — pre-calculated average
4.53
```

Keuntungan format JSON:
- **Pertanyaan dinamis** — bisa tambah/kurangi tanpa ubah schema
- **Backward compatible** — kuesioner lama tetap valid meski pertanyaan berubah
- **Hemat storage** — 4 rows per kuesioner (bukan 49)

---

## 📁 Struktur Project

```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── KuesionerController.php          # Public kuesioner flow
│   └── Dashboard/
│       ├── AdminController.php          # Admin dashboard + user CRUD
│       ├── ManagementController.php     # Management dashboard
│       ├── UserController.php           # Nakes dashboard
│       ├── DetailPenilaianController.php
│       ├── ManajemenKuesionerController.php
│       └── NotificationController.php
├── Models/
│   ├── User.php, Dokter.php, Perawat.php
│   ├── Kuesioner.php, KuesionerKlinik.php, KuesionerDokter.php, KuesionerPerawat.php
│   ├── PertanyaanKuesioner.php, Notification.php
└── Services/
    ├── KuesionerStatsService.php    # Statistik & aggregation
    └── NotificationService.php

resources/views/
├── kuesioner/          # 6 step form publik
├── auth/               # Login
├── layouts/            # Dashboard layout
└── dashboard/
    ├── admin/          # Admin pages
    ├── management/     # Management pages
    ├── user/           # Nakes pages
    └── shared/         # Shared components (detail penilaian)
```

---

## 🔐 Role & Akses

| Role | Akses |
|------|-------|
| **Administrator** | Semua fitur + kelola user + kelola pertanyaan + hapus data |
| **Management** | Dashboard + penilaian nakes + data kuesioner + komplain (read-only) |
| **User (Nakes)** | Penilaian pribadi + kritik saran + penilaian klinik |

---

## ⚡ Optimasi Performa

Project ini sudah dioptimasi untuk skala 100k+ data:
- **JSON jawaban** — 4 rows per kuesioner (bukan 49), hemat 92% storage
- **Pre-calculated `rata_rata`** — dashboard query instan tanpa AVG subquery
- **Database indexes** pada semua kolom yang sering di-query (composite indexes)
- **Query caching** (60–300 detik) untuk dashboard aggregation
- **Pertanyaan dinamis** — tambah/kurangi/reorder tanpa ubah schema
- **Backward compatible** — kuesioner lama tetap valid meski pertanyaan berubah
- **Notification caching** (unread count di-cache 15 detik)
- **Cache invalidation** otomatis saat data baru masuk

---

## 📱 Testing di HP
```bash
php artisan serve --host=0.0.0.0 --port=8000
# Buka di HP: http://<IP-komputer>:8000
```

---

## 📄 License

MIT
