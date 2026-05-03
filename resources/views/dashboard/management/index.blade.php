@extends('layouts.dashboard')
@section('title', 'Dashboard Management')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="stats-grid mb-28">
    <div class="stat-card teal">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $stats['total_kuesioner'] }}</div>
        <div class="stat-label">Total Kuesioner</div>
    </div>
    <div class="stat-card coral">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value">{{ $stats['total_komplain'] }}</div>
        <div class="stat-label">Komplain Masuk</div>
    </div>
    <div class="stat-card sky">
        <div class="stat-icon">👨‍⚕️</div>
        <div class="stat-value">{{ $stats['total_dokter'] }}</div>
        <div class="stat-label">Dokter</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">👩‍⚕️</div>
        <div class="stat-value">{{ $stats['total_perawat'] }}</div>
        <div class="stat-label">Perawat</div>
    </div>
</div>

{{-- Bar Charts --}}
<div class="grid-3 mb-20">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">🏥 Penilaian Klinik</div>
                <div class="card-subtitle">{{ $chartKlinik['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body"><div class="chart-wrap"><canvas id="chartKlinik"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👨‍⚕️ Penilaian Dokter</div>
                <div class="card-subtitle">{{ $chartDokter['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body"><div class="chart-wrap"><canvas id="chartDokter"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👩‍⚕️ Penilaian Perawat</div>
                <div class="card-subtitle">{{ $chartPerawat['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body"><div class="chart-wrap"><canvas id="chartPerawat"></canvas></div></div>
    </div>
</div>

{{-- Top Ratings --}}
<div class="grid-2 mb-20">
    {{-- Top Dokter --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏆 Top 5 Dokter</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama Dokter</th><th>Penilai</th><th>Rata-rata</th></tr>
                </thead>
                <tbody>
                    @forelse($ratingDokter as $d)
                    <tr>
                        <td><strong style="font-size:13px;">{{ $d->nama }}</strong></td>
                        <td style="font-size:13px;">{{ $d->total }}</td>
                        <td>
                            @php $pct = ($d->rata_rata / 5) * 100; @endphp
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="flex:1; height:6px; background:var(--border); border-radius:4px; overflow:hidden;">
                                    <div style="width:{{ $pct }}%; height:100%; background:var(--gold); border-radius:4px;"></div>
                                </div>
                                <span style="font-size:12px; font-weight:700; min-width:32px;">{{ $d->rata_rata }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Perawat --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏆 Top 5 Perawat</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nama Perawat</th><th>Penilai</th><th>Rata-rata</th></tr>
                </thead>
                <tbody>
                    @forelse($ratingPerawat as $p)
                    <tr>
                        <td><strong style="font-size:13px;">{{ $p->nama }}</strong></td>
                        <td style="font-size:13px;">{{ $p->total }}</td>
                        <td>
                            @php $pct = ($p->rata_rata / 5) * 100; @endphp
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="flex:1; height:6px; background:var(--border); border-radius:4px; overflow:hidden;">
                                    <div style="width:{{ $pct }}%; height:100%; background:var(--gold); border-radius:4px;"></div>
                                </div>
                                <span style="font-size:12px; font-weight:700; min-width:32px;">{{ $p->rata_rata }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center; color:var(--muted); padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const COLORS = {
    baik:   { bg: 'rgba(43,191,164,0.85)',  border: '#1E9A87' },
    cukup:  { bg: 'rgba(244,200,66,0.85)',  border: '#D4A800' },
    kurang: { bg: 'rgba(255,107,107,0.85)', border: '#CC4444' },
};
function makeChart(id, data) {
    new Chart(document.getElementById(id).getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah Penilai',
                data: data.data,
                backgroundColor: [COLORS.baik.bg, COLORS.cukup.bg, COLORS.kurang.bg],
                borderColor: [COLORS.baik.border, COLORS.cukup.border, COLORS.kurang.border],
                borderWidth: 2, borderRadius: 8, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ` ${c.raw} penilai` } } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { family:'Nunito', size:12 } }, grid: { color:'rgba(0,0,0,0.05)' } },
                x: { ticks: { font: { family:'Nunito', size:11 }, maxRotation:0 }, grid: { display:false } }
            }
        }
    });
}
makeChart('chartKlinik',  @json($chartKlinik));
makeChart('chartDokter',  @json($chartDokter));
makeChart('chartPerawat', @json($chartPerawat));
</script>
@endpush
