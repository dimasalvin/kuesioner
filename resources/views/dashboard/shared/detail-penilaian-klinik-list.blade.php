@extends('layouts.dashboard')
@section('title', 'Detail Penilaian Klinik')
@section('page-title', 'Detail Penilaian')

@push('styles')
<style>
.tipe-tabs { display:flex; gap:4px; background:var(--surface); padding:6px;
             border-radius:12px; border:1px solid var(--border); width:fit-content;
             margin-bottom:24px; flex-wrap:wrap; }
.tipe-tab  { padding:8px 24px; border-radius:9px; font-size:13px; font-weight:700;
             border:2px solid transparent; cursor:pointer; font-family:'Nunito',sans-serif;
             color:var(--muted); background:var(--bg); transition:all .15s; text-decoration:none;
             display:inline-flex; align-items:center; line-height:1; }
.tipe-tab.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
.tipe-tab:hover:not(.active) { background:var(--border); color:var(--text); }
</style>
@endpush

@section('content')

@php $isUser = auth()->user()->isUser(); @endphp

{{-- Tab berbeda untuk user vs admin/management --}}
<div class="tipe-tabs">
    @if($isUser)
        {{-- User: Klinik | Pribadi --}}
        @php $currentTipe = request('tipe', 'klinik'); @endphp

<a href="{{ route('dashboard.user.detail-penilaian', ['tipe'=>'klinik']) }}"
   class="tipe-tab {{ $currentTipe=='klinik'?'active':'' }}">🏥 Klinik</a>

<a href="{{ route('dashboard.user.detail-penilaian', [
    'tipe' => auth()->user()->isDokter() ? 'dokter' : 'perawat'
]) }}"
   class="tipe-tab {{ in_array($currentTipe,['dokter','perawat'])?'active':'' }}">
   👤 Pribadi
</a>
    @else
        {{-- Admin / Management: Klinik | Dokter | Perawat --}}
        @php $base = auth()->user()->isAdmin() ? 'dashboard.admin.detail-penilaian' : 'dashboard.management.detail-penilaian'; @endphp
        <a href="{{ route($base, ['tipe'=>'klinik']) }}"
           class="tipe-tab active">🏥 Klinik</a>
        <a href="{{ route($base, ['tipe'=>'dokter']) }}"
           class="tipe-tab">👨‍⚕️ Dokter</a>
        <a href="{{ route($base, ['tipe'=>'perawat']) }}"
           class="tipe-tab">👩‍⚕️ Perawat</a>
    @endif
</div>

@php
$showRoute = $isUser
    ? 'dashboard.user.detail-penilaian-klinik-show'
    : (auth()->user()->isAdmin()
        ? 'dashboard.admin.detail-penilaian-klinik-show'
        : 'dashboard.management.detail-penilaian-klinik-show');
@endphp

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🏥 Daftar Penilaian Klinik</div>
            <div class="card-subtitle">{{ $rows->total() }} penilaian</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pasien</th>
                    <th>No. Telp</th>
                    <th>Rata-rata</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                @php $stars = round($r->rata_rata); @endphp
                <tr>
                    <td style="color:var(--muted);font-size:12px;">{{ $rows->total() - (($rows->currentPage() - 1) * $rows->perPage()) - $loop->index }}</td>
                    <td><strong>{{ $r->pasien_nama }}</strong></td>
                    <td style="color:var(--muted);font-size:13px;">{{ $r->no_telp }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:13px;font-weight:800;color:var(--gold);">{{ $r->rata_rata }}</span>
                            <span>
                                @for($i=1;$i<=5;$i++)
                                <span style="font-size:12px;color:{{ $i<=$stars?'#F4C842':'#D0D8E0' }}">★</span>
                                @endfor
                            </span>
                        </div>
                    </td>
                    <td style="font-size:12px;color:var(--muted);white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($r->created_at)->tanggalWib() }}<br>
                        <span style="font-size:11px;">{{ \Carbon\Carbon::parse($r->created_at)->jamWib() }}</span>
                    </td>
                    <td>
                        <a href="{{ route($showRoute, $r->id) }}" class="btn btn-primary btn-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">
                        Belum ada penilaian klinik
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rows->hasPages())
    <div class="pagination">
        @if($rows->onFirstPage())
            <span class="page-link" style="opacity:.4;">‹ Prev</span>
        @else
            <a href="{{ $rows->previousPageUrl() }}" class="page-link">‹ Prev</a>
        @endif
        @foreach($rows->getUrlRange(max(1,$rows->currentPage()-2),min($rows->lastPage(),$rows->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page==$rows->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($rows->hasMorePages())
            <a href="{{ $rows->nextPageUrl() }}" class="page-link">Next ›</a>
        @else
            <span class="page-link" style="opacity:.4;">Next ›</span>
        @endif
    </div>
    @endif
</div>

@endsection
