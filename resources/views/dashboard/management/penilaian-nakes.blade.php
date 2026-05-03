@extends('layouts.dashboard')
@section('title', 'Penilaian Tenaga Kesehatan')
@section('page-title', 'Penilaian Tenaga Kesehatan')

@push('styles')
<style>
/* ── Tab Utama (Dokter / Perawat) ───────────────────── */
.tipe-tabs { display:flex; gap:4px; background:var(--surface); padding:6px;
             border-radius:12px; border:1px solid var(--border); width:fit-content;
             margin-bottom:24px; }
.tipe-tab  { padding:8px 24px; border-radius:9px; font-size:13px; font-weight:700;
             border:none; cursor:pointer; font-family:'Nunito',sans-serif;
             color:var(--muted); background:none; transition:all .15s; }
.tipe-tab.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
.tipe-tab:hover:not(.active) { background:var(--bg); color:var(--text); }

/* ── Card Grid Nakes ─────────────────────────────────── */
.nakes-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr));
              gap:12px; margin-bottom:28px; }
.nakes-card { background:var(--surface); border:1.5px solid var(--border);
              border-radius:12px; padding:14px 16px; cursor:pointer;
              transition:all .18s; user-select:none; }
.nakes-card:hover    { border-color:var(--teal); background:var(--teal-light);
                        box-shadow:0 2px 12px rgba(43,191,164,.13); }
.nakes-card.selected { border-color:var(--teal); background:var(--teal-light);
                        box-shadow:0 2px 16px rgba(43,191,164,.22); }
.nakes-card .nc-nama  { font-size:13px; font-weight:700; color:var(--text);
                         white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.nakes-card .nc-spek  { font-size:11px; color:var(--muted); margin-top:2px; min-height:15px; }
.nakes-card .nc-total { font-size:22px; font-weight:800; color:var(--teal); margin-top:6px; line-height:1; }
.nakes-card .nc-label { font-size:10px; color:var(--muted); font-weight:600;
                         text-transform:uppercase; letter-spacing:.5px; }

/* ── Chart Section ───────────────────────────────────── */
.chart-section { background:var(--surface); border:1px solid var(--border);
                 border-radius:14px; padding:22px; margin-bottom:28px; }
.chart-section .cs-header { display:flex; align-items:center; justify-content:space-between;
                              flex-wrap:wrap; gap:8px; margin-bottom:18px; }
.cs-title { font-size:15px; font-weight:800; color:var(--text); }
.cs-subtitle { font-size:12px; color:var(--muted); }
.chart-wrap { height:260px; position:relative; }

/* ── Ranking ─────────────────────────────────────────── */
.ranking-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
@media(max-width:700px) { .ranking-grid { grid-template-columns:1fr; } }

/* spinner */
.chart-loading { position:absolute; inset:0; display:flex; align-items:center;
                 justify-content:center; background:var(--surface); border-radius:8px;
                 font-size:13px; color:var(--muted); z-index:2; }
.chart-loading.hidden { display:none; }
</style>
@endpush

@section('content')

{{-- Tab Dokter / Perawat --}}
<div class="tipe-tabs">
    <button class="tipe-tab active" id="tab-dokter"  onclick="switchTipe('dokter')">👨‍⚕️ Dokter</button>
    <button class="tipe-tab"        id="tab-perawat" onclick="switchTipe('perawat')">👩‍⚕️ Perawat</button>
</div>

{{-- Grid Card Dokter --}}
<div id="panel-dokter">
    <div class="nakes-grid">
        <div class="nakes-card selected" id="card-dokter-all"
             onclick="selectNakes('dokter', 0, this)">
            <div class="nc-nama">Semua Dokter</div>
            <div class="nc-spek">Tampilkan semua</div>
            <div class="nc-total">{{ $ratingDokter->sum('total') }}</div>
            <div class="nc-label">TOTAL MASUKAN</div>
        </div>
        @foreach($ratingDokter as $d)
        <div class="nakes-card" id="card-dokter-{{ $d->id }}"
             onclick="selectNakes('dokter', {{ $d->id }}, this)">
            <div class="nc-nama">{{ $d->nama }}</div>
            <div class="nc-total">{{ $d->total }}</div>
            <div class="nc-label">MASUKAN</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Grid Card Perawat --}}
<div id="panel-perawat" style="display:none;">
    <div class="nakes-grid">
        <div class="nakes-card selected" id="card-perawat-all"
             onclick="selectNakes('perawat', 0, this)">
            <div class="nc-nama">Semua Perawat</div>
            <div class="nc-spek">Tampilkan semua</div>
            <div class="nc-total">{{ $ratingPerawat->sum('total') }}</div>
            <div class="nc-label">TOTAL MASUKAN</div>
        </div>
        @foreach($ratingPerawat as $p)
        <div class="nakes-card" id="card-perawat-{{ $p->id }}"
             onclick="selectNakes('perawat', {{ $p->id }}, this)">
            <div class="nc-nama">{{ $p->nama }}</div>
            <div class="nc-spek" style="visibility:hidden;">—</div>
            <div class="nc-total">{{ $p->total }}</div>
            <div class="nc-label">MASUKAN</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Dynamic Chart --}}
<div class="chart-section">
    <div class="cs-header">
        <div>
            <div class="cs-title"    id="chart-title">Rata-rata Nilai — Semua Dokter</div>
            <div class="cs-subtitle" id="chart-subtitle">Nilai rata-rata per individu (skala 1–5)</div>
        </div>
    </div>
    <div class="chart-wrap">
        <div class="chart-loading hidden" id="chart-loading">Memuat grafik…</div>
        <canvas id="mainChart"></canvas>
    </div>
</div>

{{-- Ranking --}}
<div class="ranking-grid">
    <div class="card">
        <div class="card-header"><div class="card-title">🏅 Ranking Dokter</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Nama</th><th>Penilai</th><th>Rata-rata</th></tr></thead>
                <tbody>
                    @forelse($ratingDokter as $i => $d)
                    <tr>
                        <td style="font-weight:800;color:var(--teal);font-size:13px;">{{ $i+1 }}</td>
                        <td><strong style="font-size:13px;">{{ $d->nama }}</strong></td>
                        <td style="font-size:13px;">{{ $d->total }}</td>
                        <td>
                            @php $pct = ($d->rata_rata/5)*100; @endphp
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;height:6px;background:var(--border);border-radius:4px;overflow:hidden;">
                                    <div style="width:{{ $pct }}%;height:100%;background:var(--gold);border-radius:4px;"></div>
                                </div>
                                <span style="font-size:12px;font-weight:700;min-width:32px;">{{ $d->rata_rata }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">🏅 Ranking Perawat</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Nama</th><th>Penilai</th><th>Rata-rata</th></tr></thead>
                <tbody>
                    @forelse($ratingPerawat as $i => $p)
                    <tr>
                        <td style="font-weight:800;color:var(--teal);font-size:13px;">{{ $i+1 }}</td>
                        <td><strong style="font-size:13px;">{{ $p->nama }}</strong></td>
                        <td style="font-size:13px;">{{ $p->total }}</td>
                        <td>
                            @php $pct = ($p->rata_rata/5)*100; @endphp
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;height:6px;background:var(--border);border-radius:4px;overflow:hidden;">
                                    <div style="width:{{ $pct }}%;height:100%;background:var(--gold);border-radius:4px;"></div>
                                </div>
                                <span style="font-size:12px;font-weight:700;min-width:32px;">{{ $p->rata_rata }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data chart semua dari server (PHP → JS)
const semuaDokter  = @json($chartSemuaDokter);
const semuaPerawat = @json($chartSemuaPerawat);

// URL endpoint individu (Laravel route helper)
@php
try {
    $nakesApiUrl = route(
        (auth()->user()->isAdmin() ? 'dashboard.admin' : 'dashboard.management') . '.chart-nakes',
        ['tipe' => '__TIPE__', 'id' => '__ID__']
    );
} catch (\Exception $e) {
    $nakesApiUrl = url('dashboard/management/chart-nakes/__TIPE__/__ID__');
}
@endphp
const nakesApiUrl = @json($nakesApiUrl);

let mainChart = null;

// Init: tampilkan semua dokter
document.addEventListener('DOMContentLoaded', () => renderChart(semuaDokter, 'Rata-rata Nilai — Semua Dokter', 'semua'));

// ── Switch tab ────────────────────────────────────────────────────────────
function switchTipe(tipe) {
    ['dokter','perawat'].forEach(t => {
        document.getElementById('panel-'+t).style.display = t===tipe ? '' : 'none';
        document.getElementById('tab-'+t).classList.toggle('active', t===tipe);
    });
    // reset selection ke "semua"
    document.querySelectorAll('.nakes-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('card-'+tipe+'-all')?.classList.add('selected');

    const data  = tipe==='dokter' ? semuaDokter : semuaPerawat;
    const label = tipe==='dokter' ? 'Rata-rata Nilai — Semua Dokter' : 'Rata-rata Nilai — Semua Perawat';
    renderChart(data, label, 'semua');
}

// ── Pilih nakes ───────────────────────────────────────────────────────────
async function selectNakes(tipe, id, el) {
    document.querySelectorAll('#panel-'+tipe+' .nakes-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    if (id === 0) {
        const data  = tipe==='dokter' ? semuaDokter : semuaPerawat;
        const label = tipe==='dokter' ? 'Rata-rata Nilai — Semua Dokter' : 'Rata-rata Nilai — Semua Perawat';
        renderChart(data, label, 'semua');
        return;
    }

    const nama = el.querySelector('.nc-nama').textContent.trim();
    setLoading(true);
    try {
        const url = nakesApiUrl.replace('__TIPE__', tipe).replace('__ID__', id);
        const res  = await fetch(url);
        const data = await res.json();
        renderChart(data, 'Distribusi Penilaian — '+nama, 'individu');
    } catch(e) { console.error(e); }
    finally    { setLoading(false); }
}

// ── Render chart ──────────────────────────────────────────────────────────
function renderChart(data, title, mode) {
    document.getElementById('chart-title').textContent    = title;
    document.getElementById('chart-subtitle').textContent =
        mode==='semua'
            ? 'Nilai rata-rata per individu (skala 1–5)'
            : 'Jumlah penilai per kategori';

    if (mainChart) { mainChart.destroy(); mainChart = null; }
    const ctx = document.getElementById('mainChart').getContext('2d');

    if (mode === 'semua') {
        const bg  = data.data.map(v => v>=4 ? 'rgba(43,191,164,.85)' : v>=3 ? 'rgba(244,200,66,.85)' : 'rgba(255,107,107,.85)');
        const brd = data.data.map(v => v>=4 ? '#1E9A87' : v>=3 ? '#D4A800' : '#CC4444');
        mainChart = new Chart(ctx, {
            type:'bar',
            data:{ labels:data.labels, datasets:[{ label:'Rata-rata', data:data.data,
                backgroundColor:bg, borderColor:brd, borderWidth:2,
                borderRadius:8, borderSkipped:false }]},
            options:{ responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` Rata-rata: ${c.raw}`}}},
                scales:{ y:{ beginAtZero:true, max:5,
                             ticks:{font:{family:'Nunito',size:12}}, grid:{color:'rgba(0,0,0,.05)'}},
                         x:{ ticks:{font:{family:'Nunito',size:11},maxRotation:30}, grid:{display:false}}}}
        });
    } else {
        mainChart = new Chart(ctx, {
            type:'bar',
            data:{ labels:data.labels, datasets:[{ label:'Penilai', data:data.data,
                backgroundColor:['rgba(43,191,164,.85)','rgba(244,200,66,.85)','rgba(255,107,107,.85)'],
                borderColor:['#1E9A87','#D4A800','#CC4444'],
                borderWidth:2, borderRadius:8, borderSkipped:false }]},
            options:{ responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.raw} penilai`}}},
                scales:{ y:{ beginAtZero:true, ticks:{stepSize:1,font:{family:'Nunito',size:12}},
                             grid:{color:'rgba(0,0,0,.05)'}},
                         x:{ ticks:{font:{family:'Nunito',size:12},maxRotation:0}, grid:{display:false}}}}
        });
    }
}

function setLoading(show) {
    document.getElementById('chart-loading').classList.toggle('hidden', !show);
}
</script>
@endpush
