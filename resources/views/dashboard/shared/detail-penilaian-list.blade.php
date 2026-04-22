@extends('layouts.dashboard')
@section('title', 'Daftar Penilaian')
@section('page-title', 'Daftar Penilaian')

@section('content')

@php
$user = auth()->user();
$isNakes = $user->isUser();
if ($isNakes) {
    $backRoute = $tipe==='dokter'
        ? route('dashboard.user.detail-penilaian')
        : route('dashboard.user.detail-penilaian');
    $detailRoute = 'dashboard.user.detail-penilaian-show';
} elseif ($user->isAdmin()) {
    $backRoute   = route('dashboard.admin.detail-penilaian', ['tipe'=>$tipe]);
    $detailRoute = 'dashboard.admin.detail-penilaian-show';
} else {
    $backRoute   = route('dashboard.management.detail-penilaian', ['tipe'=>$tipe]);
    $detailRoute = 'dashboard.management.detail-penilaian-show';
}
@endphp

{{-- Back + header --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ $backRoute }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <div>
        <div style="font-size:16px;font-weight:800;">{{ $nakes->nama }}</div>
        @if(isset($nakes->spesialisasi) && $nakes->spesialisasi)
        <div style="font-size:12px;color:var(--muted);">{{ $nakes->spesialisasi }}</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Riwayat Penilaian</div>
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
                    <td style="color:var(--muted);font-size:12px;">{{ $r->id }}</td>
                    <td><strong>{{ $r->pasien_nama }}</strong></td>
                    <td style="color:var(--muted);font-size:13px;">{{ $r->no_telp }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:13px;font-weight:800;color:var(--gold);">
                                {{ $r->rata_rata }}
                            </span>
                            <span style="font-size:12px;color:#D0D8E0;">
                                @for($i=1;$i<=5;$i++)
                                    <span style="color:{{ $i<=$stars?'#F4C842':'#D0D8E0' }}">★</span>
                                @endfor
                            </span>
                        </div>
                    </td>
                    <td style="font-size:12px;color:var(--muted);white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($r->created_at)->tanggalWib() }}<br>
                        <span style="font-size:11px;">{{ \Carbon\Carbon::parse($r->created_at)->jamWib() }}</span>
                    </td>
                    <td>
                        <a href="{{ route($detailRoute, ['id'=>$r->id,'tipe'=>$tipe]) }}"
                           class="btn btn-primary btn-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">
                        Belum ada penilaian
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
