@extends('layouts.app')
@section('title', 'Komplain')

@section('content')
<div class="step-header">
    <div class="step-progress" data-current="5" data-total="6"></div>
    <div class="page-title">Apakah Anda Memiliki Komplain?</div>
    <div class="page-subtitle">Langkah 5 dari 6 — Pengaduan</div>
</div>

<form id="kuesioner-form" method="POST" action="{{ route('kuesioner.store-komplain') }}">
    @csrf
    <input type="hidden" id="has_complain" name="has_complain" value="{{ session('komplain.has_complain', '') }}">

    <div class="scroll-content">

        <p style="font-size:15px; color:var(--text-muted); margin-bottom:8px; line-height:1.6;">
            Jika Anda memiliki keluhan atau ketidakpuasan terhadap layanan kami, silahkan sampaikan agar kami dapat terus meningkatkan kualitas.
        </p>

        <div class="complaint-options">
            <button type="button" id="btn-yes" class="choice-btn yes {{ session('komplain.has_complain') == '1' ? 'selected' : '' }}">
                😟 Ya
            </button>
            <button type="button" id="btn-no" class="choice-btn no {{ session('komplain.has_complain') == '0' ? 'selected' : '' }}">
                😊 Tidak
            </button>
        </div>

        <div id="complaint-box" class="complaint-box {{ session('komplain.has_complain') == '1' ? 'show' : '' }}">
            <div class="field-group">
                <label class="field-label" for="komplain_text">Masukkan Komplain</label>
                <textarea
                    id="komplain_text"
                    name="komplain"
                    class="field-input"
                    placeholder="Ceritakan pengalaman dan keluhan Anda di sini..."
                    style="height: 140px;"
                >{{ session('komplain.komplain') }}</textarea>
            </div>
        </div>

        <p class="text-muted mt-4">
            ✦ Setiap masukan sangat berarti bagi kami. Terima kasih telah meluangkan waktu.
        </p>
    </div>

    <div class="bottom-cta">
        <button type="submit" class="btn-next">
            Selesai & Kirim
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</form>
@endsection
