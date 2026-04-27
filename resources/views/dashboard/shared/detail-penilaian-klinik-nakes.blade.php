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
.tipe-tab:hover:not(.active) { background:var(--bg); color:var(--text); }
</style>
@endpush

@section('content')

{{-- Tab: Klinik / Pribadi --}}
<!-- <div class="tipe-tabs">
    <a href="{{ route('dashboard.user.detail-penilaian', ['tipe'=>'klinik']) }}"
       class="tipe-tab active">🏥 Klinik</a>
    <a href="{{ route('dashboard.user.detail-penilaian') }}"
       class="tipe-tab">👤 Pribadi2</a>
</div> -->

{{-- Summary header --}}
<div style="background:linear-gradient(135deg,var(--sky),#3A82C8);border-radius:var(--radius);
            padding:20px 24px;margin-bottom:20px;color:white;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
        <div style="font-size:12px;opacity:.8;margin-bottom:4px;">🏥 Klinik</div>
        <div style="font-family:'Caveat',cursive;font-size:26px;">Penilaian Fasilitas Klinik</div>
        <div style="font-size:12px;opacity:.75;margin-top:6px;">Berdasarkan {{ $total }} responden</div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:42px;font-weight:800;line-height:1;">
            {{ $total > 0 ? number_format($avgTotal, 2) : '—' }}
        </div>
        <div style="font-size:11px;opacity:.75;margin-top:4px;">rata-rata keseluruhan</div>
    </div>
</div>

{{-- Stat cards --}}
<div class="stats-grid mb-20">
    <div class="stat-card teal">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $total }}</div>
        <div class="stat-label">Total Responden</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $chart['data'][0] }}</div>
        <div class="stat-label">Penilaian Baik</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">⚡</div>
        <div class="stat-value">{{ $chart['data'][1] }}</div>
        <div class="stat-label">Penilaian Cukup</div>
    </div>
    <div class="stat-card coral">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value">{{ $chart['data'][2] }}</div>
        <div class="stat-label">Penilaian Kurang</div>
    </div>
</div>

<div class="grid-2 mb-20">
    {{-- Bar Chart --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Distribusi Penilaian</div>
        </div>
        <div class="card-body">
            <div class="chart-wrap" style="height:220px;">
                <canvas id="klinikChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Per pertanyaan --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Rata-rata Per Pertanyaan</div>
        </div>
        <div class="card-body" style="padding:16px;">
            <div style="display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;">
                @foreach($pertanyaan as $p)
                @php
                    $val   = isset($perQ[$p->id]) ? (float)$perQ[$p->id]->rata_rata : 0;
                    $pct   = ($val/5)*100;
                    $color = $val>=4?'var(--teal)':($val>=3?'var(--gold)':'var(--coral)');
                @endphp
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:10px;font-weight:800;color:var(--muted);
                                 min-width:18px;text-align:right;">{{ $loop->iteration }}</span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:11px;color:var(--muted);margin-bottom:3px;
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $p->teks }}
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="flex:1;height:6px;background:var(--border);
                                        border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;height:100%;
                                            background:{{ $color }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:11px;font-weight:800;
                                         color:{{ $color }};min-width:24px;text-align:right;">
                                {{ $val }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
new Chart(document.getElementById('klinikChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($chart['labels']),
        datasets: [{
            label: 'Jumlah Penilai',
            data: @json($chart['data']),
            backgroundColor: ['rgba(43,191,164,.85)','rgba(244,200,66,.85)','rgba(255,107,107,.85)'],
            borderColor: ['#1E9A87','#D4A800','#CC4444'],
            borderWidth: 2, borderRadius: 8, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.raw} penilai`}} },
        scales: {
            y: { beginAtZero:true, ticks:{stepSize:1,font:{family:'Nunito',size:12}}, grid:{color:'rgba(0,0,0,.05)'} },
            x: { ticks:{font:{family:'Nunito',size:11},maxRotation:0}, grid:{display:false} }
        }
    }
});
</script>
@endpush
