# 🤖 AI_CONTEXT.md / README_AI.md

Dokumen ini dibuat khusus agar AI (ChatGPT, Cursor, Copilot, dll) bisa langsung memahami struktur dan konteks project ini tanpa perlu penjelasan ulang.

---

# 🧱 PROJECT OVERVIEW

Project ini adalah aplikasi **Kuesioner Penilaian Klinik dan Tenaga Kesehatan** berbasis Laravel.

Digunakan untuk:

* Mengisi kuesioner
* Menilai:

  * Klinik
  * Dokter
  * Perawat
* Menampilkan hasil penilaian

---

# ⚙️ TECH STACK

* Laravel (Backend)
* MySQL (Database)
* Blade (Templating)
* Tailwind CSS / Argon Dashboard (UI)
* JavaScript (AJAX interaksi)

---

# 📁 STRUKTUR PENTING

## Backend

* `app/Http/Controllers/Dashboard/`

  * Berisi logic utama dashboard dan penilaian
  * Contoh penting:

    * `DetailPenilaianController.php`
    * `UserController.php`

## Model

* `app/Models/`

  * Representasi tabel database

## View

* `resources/views/dashboard/`

  * UI halaman dashboard
  * Menampilkan data kuesioner dan hasil

## Routes

* `routes/web.php`

  * Routing utama aplikasi

---

# 🧠 CORE LOGIC

## Flow Kuesioner

1. User login
2. User memilih kategori kuesioner

   * Klinik
   * Dokter
   * Perawat
3. User mengisi pertanyaan
4. Data disimpan ke tabel sesuai kategori

---

# 🗃️ STRUKTUR DATA (PENTING)

## Tabel Kuesioner

Data disimpan ke beberapa tabel berbeda:

* `kuesioner_kliniks`
* `kuesioner_dokters`
* `kuesioner_perawats`

Masalah umum:

* Data bisa masuk ke tabel yang salah
* Atau tidak masuk sama sekali (0 semua)

---

## Struktur Object (Frontend)

Menggunakan struktur seperti:

```
tmpDetailLeads = {
  id,
  nama,
  referral: {
    bank,
    sumber
  }
}
```

---

# ⚠️ KNOWN ISSUES

1. Data kuesioner tersimpan 0 semua
2. Insert tidak masuk ke tabel yang sesuai
3. Mapping kategori → tabel belum konsisten
4. Kemungkinan issue di:

   * Controller logic
   * Request handling
   * Relasi model

---

# 🎯 BAGIAN KRITIS UNTUK ANALISIS AI

Jika AI diminta membantu, fokus ke:

### 1. Controller

* `DetailPenilaianController`
* Logic insert kuesioner

### 2. Request Data

* Struktur request dari form
* Validasi input

### 3. Mapping Kategori

* Klinik → `kuesioner_kliniks`
* Dokter → `kuesioner_dokters`
* Perawat → `kuesioner_perawats`

### 4. Database Insert

* Pastikan field sesuai
* Tidak null

---

# 🧪 CARA DEBUG (RECOMMENDED)

Gunakan langkah ini saat debugging:

1. `dd($request->all())` → cek data masuk
2. Cek query insert
3. Pastikan kondisi if/else kategori benar
4. Cek apakah loop berjalan

---

# 📌 KONVENSI PROJECT

* Gunakan ID sebagai referensi utama (bukan nama)
* Hindari hardcode string kategori
* Pisahkan logic dari view

---

# 🧾 CONTOH PROMPT UNTUK AI

Gunakan ini agar AI langsung paham:

```
Ini adalah project Laravel kuesioner.

Baca:
- README_AI.md
- DetailPenilaianController.php
- routes/web.php

Fokus:
- Kenapa data kuesioner tidak masuk ke tabel

Berikan solusi spesifik (bukan teori)
```

---

# 🚀 TUJUAN AKHIR

* Sistem kuesioner stabil
* Data masuk ke tabel yang benar
* Struktur scalable untuk multi kategori

---

Jika ada perubahan struktur atau flow, update file ini agar AI tetap relevan.
