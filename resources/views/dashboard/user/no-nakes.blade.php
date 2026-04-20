@extends('layouts.dashboard')
@section('title','Akun Tidak Terhubung')
@section('page-title','Dashboard')

@section('content')
<div style="max-width:480px; margin:60px auto; text-align:center;">
    <div style="font-size:64px; margin-bottom:20px;">⚠️</div>
    <h2 style="font-family:'Caveat',cursive; font-size:28px; margin-bottom:12px;">Akun Belum Terhubung</h2>
    <p style="color:var(--muted); line-height:1.7; margin-bottom:28px;">
        Akun Anda belum dihubungkan ke data dokter atau perawat. Silakan hubungi administrator untuk menyelesaikan pengaturan akun.
    </p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-ghost">Keluar</button>
    </form>
</div>
@endsection
