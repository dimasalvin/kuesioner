@extends('layouts.dashboard')
@section('title', 'Kritik & Saran Nakes')
@section('page-title', 'Kritik & Saran Nakes')

@push('styles')
<style>
.tipe-tabs { display:flex; gap:4px; background:var(--surface); padding:6px;
             border-radius:12px; border:1px solid var(--border); width:fit-content;
             margin-bottom:24px; }
.tipe-tab  { padding:8px 24px; border-radius:9px; font-size:13px; font-weight:700;
             border:none; cursor:pointer; font-family:'Nunito',sans-serif;
             color:var(--muted); background:none; transition:all .15s; text-decoration:none;
             display:inline-block; }
.tipe-tab.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
.tipe-tab:hover:not(.active) { background:var(--bg); color:var(--text); }

.summary-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
                gap:12px; margin-bottom:24px; }
.summary-card { background:var(--surface); border:1px solid var(--border);
                border-radius:12px; padding:14px 16px; cursor:pointer;
                transition:all .15s; text-decoration:none; display:block; }
.summary-card:hover,
.summary-card.selected { border-color:var(--teal); background:var(--teal-light);
                          box-shadow:0 2px 12px rgba(43,191,164,.15); }
.summary-card .s-nama  { font-size:13px; font-weight:700; color:var(--text);
                          white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.summary-card .s-spek  { font-size:11px; color:var(--muted); margin-top:2px; }
.summary-card .s-total { font-size:22px; font-weight:800; color:var(--teal);
                          margin-top:6px; line-height:1; }
.summary-card .s-label { font-size:10px; color:var(--muted); font-weight:600;
                          text-transform:uppercase; }

.kritik-card { background:var(--surface); border:1px solid var(--border);
               border-radius:12px; padding:18px; margin-bottom:12px;
               transition:box-shadow .15s; }
.kritik-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
.kritik-meta { display:flex; align-items:center; justify-content:space-between;
               margin-bottom:10px; flex-wrap:wrap; gap:8px; }
.kritik-nakes { display:flex; align-items:center; gap:8px; }
.nakes-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0;
                display:flex; align-items:center; justify-content:center;
                font-weight:800; font-size:13px; color:white; }
.avatar-dokter  { background:linear-gradient(135deg,#2BBFA4,#1E9A87); }
.avatar-perawat { background:linear-gradient(135deg,#5BA4E5,#3A82C8); }
.nakes-info .nama { font-size:13px; font-weight:800; color:var(--text); }
.nakes-info .spek { font-size:11px; color:var(--muted); }
.kritik-right { display:flex; align-items:center; gap:10px; }
.pasien-label { font-size:11px; color:var(--muted); }
.pasien-nama  { font-size:12px; font-weight:700; color:var(--text); }
.waktu-label  { font-size:11px; color:var(--muted); white-space:nowrap; }
.kritik-teks  { padding:12px 16px; background:var(--bg); border-radius:9px;
                font-size:14px; line-height:1.7; color:var(--text);
                border-left:3px solid var(--teal); }

.empty-state { text-align:center; padding:60px 20px; color:var(--muted); }
.empty-state .icon { font-size:48px; margin-bottom:12px; }
.empty-state p { font-size:14px; }
</style>
@endpush

@section('content')

{{-- Tipe tabs --}}
@php
$baseRoute = auth()->user()->isAdmin()
    ? 'dashboard.admin.kritik-saran'
    : 'dashboard.management.kritik-saran';
@endphp

<div class="tipe-tabs">
    <a href="{{ route($baseRoute, ['tipe' => 'dokter']) }}"
       class="tipe-tab {{ $tipe === 'dokter' ? 'active' : '' }}">
        👨‍⚕️ Dokter
    </a>
    <a href="{{ route($baseRoute, ['tipe' => 'perawat']) }}"
       class="tipe-tab {{ $tipe === 'perawat' ? 'active' : '' }}">
        👩‍⚕️ Perawat
    </a>
</div>

{{-- Summary cards (filter by nakes) --}}
@if($summary->isNotEmpty())
<div class="summary-grid">
    {{-- "Semua" card --}}
    <a href="{{ route($baseRoute, ['tipe' => $tipe]) }}"
       class="summary-card {{ !$nakesId ? 'selected' : '' }}">
        <div class="s-nama">Semua {{ $tipe === 'dokter' ? 'Dokter' : 'Perawat' }}</div>
        <div class="s-spek">Tampilkan semua</div>
        <div class="s-total">{{ $summary->sum('total') }}</div>
        <div class="s-label">total masukan</div>
    </a>

    @foreach($summary as $s)
    <a href="{{ route($baseRoute, ['tipe' => $tipe, 'nakes_id' => $s->id]) }}"
       class="summary-card {{ $nakesId == $s->id ? 'selected' : '' }}">
        <div class="s-nama" title="{{ $s->nama }}">{{ $s->nama }}</div>
        <div class="s-spek">&nbsp;</div>
        <div class="s-total">{{ $s->total }}</div>
        <div class="s-label">masukan</div>
    </a>
    @endforeach
</div>
@endif

{{-- Search & filter bar --}}
<div class="card mb-20" style="overflow:visible;">
    <div class="card-header">
        <div>
            <div class="card-title">
                💬 Kritik & Saran
                @if($nakesId)
                    — {{ $nakesList->firstWhere('id', $nakesId)?->nama ?? '' }}
                @endif
            </div>
            <div class="card-subtitle">{{ $kritik->total() }} masukan ditemukan</div>
        </div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="tipe" value="{{ $tipe }}">
            @if($nakesId)
                <input type="hidden" name="nakes_id" value="{{ $nakesId }}">
            @endif
            <input type="text" name="search" value="{{ $search }}"
                   class="form-control" style="width:220px;"
                   placeholder="Cari isi kritik / nama pasien...">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            @if($search)
                <a href="{{ route($baseRoute, array_filter(['tipe'=>$tipe,'nakes_id'=>$nakesId])) }}"
                   class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <div style="padding:20px;">
        @forelse($kritik as $k)
        <div class="kritik-card">
            <div class="kritik-meta">
                <div class="kritik-nakes">
                    <div class="nakes-avatar {{ $tipe === 'dokter' ? 'avatar-dokter' : 'avatar-perawat' }}">
                        {{ strtoupper(substr($k->nakes_nama, 4, 1)) }}
                    </div>
                    <div class="nakes-info">
                        <div class="nama">{{ $k->nakes_nama }}</div>
                    </div>
                </div>
                <div class="kritik-right">
                    <div style="text-align:right;">
                        <div class="pasien-label">dari pasien</div>
                        <div class="pasien-nama">{{ $k->pasien_nama }}</div>
                    </div>
                    <div class="waktu-label">
                        {{ \Carbon\Carbon::parse($k->created_at)->tanggalWib() }}<br>
                        <span style="font-size:10px;">{{ \Carbon\Carbon::parse($k->created_at)->jamWib() }}</span>
                    </div>
                </div>
            </div>
            <div class="kritik-teks">{{ $k->kritik_saran }}</div>
        </div>
        @empty
        <div class="empty-state">
            <div class="icon">💬</div>
            <p>Belum ada kritik & saran masuk
                @if($nakesId) untuk nakes ini @endif
                @if($search) dengan kata kunci "{{ $search }}" @endif
            </p>
        </div>
        @endforelse

        {{-- Pagination --}}
        @if($kritik->hasPages())
        <div class="pagination" style="padding:16px 0 0;border-top:1px solid var(--border);margin-top:16px;">
            @if($kritik->onFirstPage())
                <span class="page-link" style="opacity:.4;">‹ Prev</span>
            @else
                <a href="{{ $kritik->previousPageUrl() }}" class="page-link">‹ Prev</a>
            @endif
            @foreach($kritik->getUrlRange(max(1,$kritik->currentPage()-2),min($kritik->lastPage(),$kritik->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page==$kritik->currentPage()?'active':'' }}">{{ $page }}</a>
            @endforeach
            @if($kritik->hasMorePages())
                <a href="{{ $kritik->nextPageUrl() }}" class="page-link">Next ›</a>
            @else
                <span class="page-link" style="opacity:.4;">Next ›</span>
            @endif
        </div>
        @endif
    </div>
</div>

@endsection
