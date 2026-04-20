@extends('layouts.app')
@section('title', 'Kuesioner Dokter')
@section('content')
<div class="step-header">
    <div class="step-progress" data-current="3" data-total="6"></div>
    <div class="page-title">Kuesioner Dokter</div>
    <div class="page-subtitle">Langkah 3 dari 6 — {{ $pertanyaan->count() }} pertanyaan</div>
</div>
<form id="kuesioner-form" method="POST" action="{{ route('kuesioner.store-dokter') }}">
    @csrf
    <div class="scroll-content">
        @if($errors->any())
            <div class="alert-box">Mohon pilih dokter dan lengkapi semua penilaian.</div>
        @endif
        <div class="field-group">
            <label class="field-label">Nama Dokter</label>
            <div class="select-wrapper">
                <select name="nama_dokter" class="field-input" required>
                    <option value="" disabled {{ !session('dokter.nama_dokter') ? 'selected' : '' }}>Pilih nama dokter...</option>
                    @foreach($dokter as $d)
                        <option value="{{ $d->id }}" {{ session('dokter.nama_dokter') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="section-divider"><span>Penilaian</span></div>
        <div class="q-list">
            @foreach($pertanyaan as $i => $p)
            <div class="q-item {{ session("dokter.q{$p->id}") ? 'answered' : '' }}">
                <div class="q-number">Pertanyaan {{ $i + 1 }}</div>
                <div class="q-text">{{ $p->teks }}</div>
                <div class="star-rating" data-required>
                    @for($s = 1; $s <= 5; $s++)
                    <span class="star-wrap">
                        <input type="radio"
                               name="q{{ $p->id }}"
                               id="dokter_{{ $p->id }}_{{ $s }}"
                               value="{{ $s }}"
                               {{ session("dokter.q{$p->id}") == $s ? 'checked' : '' }}>
                        <label for="dokter_{{ $p->id }}_{{ $s }}">★</label>
                    </span>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
        <div class="section-divider"><span>Masukan</span></div>
        <div class="field-group">
            <label class="field-label">Kritik & Saran</label>
            <textarea name="kritik_saran" class="field-input" placeholder="Tuliskan kritik atau saran... (opsional)">{{ session('dokter.kritik_saran') }}</textarea>
        </div>
    </div>
    <div class="bottom-cta">
        <button type="submit" class="btn-next">
            Selanjutnya
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>
@endsection
