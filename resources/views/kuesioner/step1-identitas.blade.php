@extends('layouts.app')
@section('title', 'Identitas Pasien')

@section('content')

{{-- Hero: foto klinik bebas copyright via Unsplash --}}
<div class="hero-photo">
    <img
        src="https://images.unsplash.com/photo-1625134673337-519d4d10b313?w=800&q=80&auto=format&fit=crop"
        alt="Klinik"
        onerror="this.parentElement.classList.add('hero-fallback')"
    >
    <div class="hero-overlay">
        <div class="hero-badge">🏥 Selamat Datang</div>
        <div class="hero-tagline">Kepuasan Anda, Prioritas Kami</div>
    </div>
</div>

{{-- Step Header --}}
<div class="step-header">
    <div class="step-progress" data-current="1" data-total="6"></div>
    <div class="page-title">Data Diri Anda</div>
    <div class="page-subtitle">Langkah 1 dari 6 — Identitas Pasien</div>
</div>

<form id="kuesioner-form" method="POST" action="{{ route('kuesioner.store-identitas') }}">
    @csrf

    <div class="scroll-content">
        @if($errors->any())
            <div class="alert-box">Mohon lengkapi semua field yang diperlukan.</div>
        @endif

        <div class="field-group">
            <label class="field-label" for="nama">Nama Lengkap</label>
            <input
                type="text"
                id="nama"
                name="nama"
                class="field-input"
                placeholder="Masukkan nama lengkap Anda"
                value="{{ old('nama', session('identitas.nama')) }}"
                required
                autocomplete="name"
            >
            <div class="field-error" style="{{ $errors->has('nama') ? '' : 'display:none' }}">
                Nama tidak boleh kosong.
            </div>
        </div>

        <div class="field-group">
            <label class="field-label" for="no_telp">Nomor Telepon</label>
            <input
                type="tel"
                id="no_telp"
                name="no_telp"
                class="field-input"
                placeholder="08xx-xxxx-xxxx"
                value="{{ old('no_telp', session('identitas.no_telp')) }}"
                required
                autocomplete="tel"
                inputmode="numeric"
            >
            <div class="field-error" style="{{ $errors->has('no_telp') ? '' : 'display:none' }}">
                Nomor telepon tidak boleh kosong.
            </div>
        </div>

        <p class="text-muted mt-4">
            ✦ Data Anda akan dijaga kerahasiaannya dan hanya digunakan untuk kepentingan evaluasi layanan.
        </p>
    </div>

    <div class="bottom-cta">
        <button type="submit" class="btn-next">
            Selanjutnya
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</form>
@endsection
