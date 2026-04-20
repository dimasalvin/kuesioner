@extends('layouts.app')
@section('title', 'Kuesioner Klinik')
@section('content')
<div class="step-header">
    <div class="step-progress" data-current="2" data-total="6"></div>
    <div class="page-title">Kuesioner Klinik</div>
    <div class="page-subtitle">Langkah 2 dari 6 — {{ $pertanyaan->count() }} pertanyaan</div>
</div>
<form id="kuesioner-form" method="POST" action="{{ route('kuesioner.store-klinik') }}">
    @csrf
    <div class="scroll-content">
        @if($errors->any())
            <div class="alert-box">Mohon lengkapi semua penilaian bintang.</div>
        @endif
        <div class="q-list">
            @foreach($pertanyaan as $i => $p)
            <div class="q-item {{ session("klinik.q{$p->id}") ? 'answered' : '' }}">
                <div class="q-number">Pertanyaan {{ $i + 1 }}</div>
                <div class="q-text">{{ $p->teks }}</div>
                <div class="star-rating" data-required>
                    @for($s = 1; $s <= 5; $s++)
                    <span class="star-wrap">
                        <input type="radio"
                               name="q{{ $p->id }}"
                               id="klinik_{{ $p->id }}_{{ $s }}"
                               value="{{ $s }}"
                               {{ session("klinik.q{$p->id}") == $s ? 'checked' : '' }}>
                        <label for="klinik_{{ $p->id }}_{{ $s }}">★</label>
                    </span>
                    @endfor
                </div>
            </div>
            @endforeach
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
