@extends('layouts.app')
@section('title', 'Terima Kasih')

@section('content')
<div class="thankyou-screen">
    <!-- Hospital illustration -->
    <div class="hero-illustration" style="width:200px; height:140px; background: linear-gradient(145deg, #E8F7F4, #D4EEF9);">
        <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="60" y="40" width="80" height="65" fill="#2BBFA4" rx="4" opacity="0.15"/>
            <rect x="60" y="40" width="80" height="65" stroke="#2BBFA4" stroke-width="2" rx="4"/>
            <rect x="92" y="52" width="16" height="5" fill="#2BBFA4" rx="2"/>
            <rect x="97" y="47" width="6" height="15" fill="#2BBFA4" rx="2"/>
            <rect x="72" y="60" width="14" height="14" fill="#5BA4E5" rx="2" opacity="0.5"/>
            <rect x="114" y="60" width="14" height="14" fill="#5BA4E5" rx="2" opacity="0.5"/>
            <rect x="88" y="80" width="24" height="25" fill="#1E9A87" rx="3" opacity="0.4"/>
            <path d="M55 42 L100 18 L145 42" stroke="#2BBFA4" stroke-width="2" fill="none"/>
            <line x1="30" y1="105" x2="170" y2="105" stroke="#D0D8E0" stroke-width="1.5"/>
            <circle cx="40" cy="95" r="12" fill="#E6F9F5" stroke="#2BBFA4" stroke-width="1.5"/>
            <circle cx="160" cy="95" r="12" fill="#E6F9F5" stroke="#2BBFA4" stroke-width="1.5"/>
        </svg>
    </div>

    <div class="thankyou-badge">✓</div>

    <div>
        <div class="thankyou-title">Terima Kasih!</div>
        <p class="thankyou-subtitle">
            Kuesioner Anda telah berhasil dikirimkan.<br>
            Penilaian Anda sangat membantu kami dalam meningkatkan kualitas layanan kesehatan.
        </p>
    </div>

    <div class="thankyou-card">
        🏥 Terima Kasih Sudah Mengisi Kuesioner
    </div>

    <p class="text-muted" style="text-align:center; line-height:1.6;">
        Semoga Anda selalu sehat dan mendapatkan pelayanan terbaik dari kami. Sampai jumpa kembali!
    </p>

</div>
@endsection
