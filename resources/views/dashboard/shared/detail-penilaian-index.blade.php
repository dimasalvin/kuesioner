@extends('layouts.dashboard')
@section('title', 'Detail Penilaian')
@section('page-title', 'Detail Penilaian')

@push('styles')
<style>
.tipe-tabs { display:flex; gap:4px; background:var(--surface); padding:6px;
             border-radius:12px; border:1px solid var(--border); width:fit-content;
             margin-bottom:24px; flex-wrap:wrap; }
.tipe-tab  { padding:8px 24px; border-radius:9px; font-size:13px; font-weight:700;
             border:none; cursor:pointer; font-family:'Nunito',sans-serif;
             color:var(--muted); background:none; transition:all .15s; text-decoration:none;
             display:inline-block; }
.tipe-tab.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
.tipe-tab:hover:not(.active) { background:var(--bg); color:var(--text); }

.nakes-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:16px; }
.nakes-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--radius);
    padding:20px; display:flex; flex-direction:column; gap:14px;
    transition:box-shadow .15s, border-color .15s;
}
.nakes-card:hover { border-color:var(--teal); box-shadow:0 4px 20px rgba(43,191,164,.12); }
.nakes-card-top { display:flex; align-items:center; gap:12px; }
.nakes-avatar { width:44px; height:44px; border-radius:50%; flex-shrink:0;
                display:flex; align-items:center; justify-content:center;
                font-weight:800; font-size:16px; color:white; }
.av-dokter  { background:linear-gradient(135deg,#2BBFA4,#1E9A87); }
.av-perawat { background:linear-gradient(135deg,#5BA4E5,#3A82C8); }
.nakes-meta .nama { font-size:14px; font-weight:800; color:var(--text); line-height:1.3; }
.nakes-meta .spek { font-size:12px; color:var(--muted); margin-top:2px; }
.nakes-stats { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.nstat { background:var(--bg); border-radius:9px; padding:10px 12px; }
.nstat-val { font-size:20px; font-weight:800; color:var(--teal); line-height:1; }
.nstat-lbl { font-size:10px; color:var(--muted); font-weight:700; text-transform:uppercase;
             letter-spacing:.05em; margin-top:3px; }
.nakes-card-footer { border-top:1px solid var(--border); padding-top:12px; }
</style>
@endpush

@section('content')

@php
$isAdmin = auth()->user()->isAdmin();
$baseRoute = $isAdmin ? 'dashboard.admin.detail-penilaian' : 'dashboard.management.detail-penilaian';
@endphp

{{-- Tab: Klinik / Dokter / Perawat --}}
<div class="tipe-tabs">
    <a href="{{ route($baseRoute, ['tipe'=>'klinik']) }}"
       class="tipe-tab {{ $tipe==='klinik' ? 'active' : '' }}">🏥 Klinik</a>
    <a href="{{ route($baseRoute, ['tipe'=>'dokter']) }}"
       class="tipe-tab {{ $tipe==='dokter' ? 'active' : '' }}">👨‍⚕️ Dokter</a>
    <a href="{{ route($baseRoute, ['tipe'=>'perawat']) }}"
       class="tipe-tab {{ $tipe==='perawat' ? 'active' : '' }}">👩‍⚕️ Perawat</a>
</div>

{{-- Grid card nakes --}}
<div class="nakes-grid">
    @forelse($list as $n)
    @php
        $stars = $n->rata_rata ? round($n->rata_rata) : 0;
        $pct   = $n->rata_rata ? ($n->rata_rata / 5) * 100 : 0;
    @endphp
    <div class="nakes-card">
        <div class="nakes-card-top">
            <div class="nakes-avatar {{ $tipe==='dokter' ? 'av-dokter' : 'av-perawat' }}">
                {{ strtoupper(substr(preg_replace('/^(dr\.|Ns\.)\s*/i','',$n->nama), 0, 1)) }}
            </div>
            <div class="nakes-meta">
                <div class="nama">{{ $n->nama }}</div>
                <div class="spek">{{ $n->spesialisasi ?? ($tipe==='dokter' ? 'Dokter Umum' : 'Perawat') }}</div>
            </div>
        </div>

        <div class="nakes-stats">
            <div class="nstat">
                <div class="nstat-val">{{ $n->total }}</div>
                <div class="nstat-lbl">Penilai</div>
            </div>
            <div class="nstat">
                <div class="nstat-val" style="color:var(--gold);">{{ $n->rata_rata ?? '—' }}</div>
                <div class="nstat-lbl">Rata-rata</div>
            </div>
        </div>

        @if($n->rata_rata)
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="flex:1;height:6px;background:var(--border);border-radius:4px;overflow:hidden;">
                <div style="width:{{ $pct }}%;height:100%;background:var(--gold);border-radius:4px;"></div>
            </div>
            <div style="display:flex;gap:2px;">
                @for($i=1;$i<=5;$i++)
                    <span style="font-size:14px;color:{{ $i<=$stars?'#F4C842':'#D0D8E0' }}">★</span>
                @endfor
            </div>
        </div>
        @else
        <div style="font-size:12px;color:var(--muted);">Belum ada penilaian</div>
        @endif

        <div class="nakes-card-footer">
            <a href="{{ route($baseRoute.'-list', ['id'=>$n->id,'tipe'=>$tipe]) }}"
               class="btn btn-primary" style="width:100%;justify-content:center;">
                Lihat Detail Penilaian →
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:60px;">
        Belum ada data {{ $tipe }}
    </div>
    @endforelse
</div>

@endsection
