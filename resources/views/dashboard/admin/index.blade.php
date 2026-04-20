@extends('layouts.dashboard')
@section('title', 'Dashboard Admin')
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
    <div class="stat-card gold">
        <div class="stat-icon">👤</div>
        <div class="stat-value">{{ $stats['total_user'] }}</div>
        <div class="stat-label">Total User</div>
    </div>
</div>

{{-- Bar Charts --}}
<div class="grid-3 mb-20">
    {{-- Chart Klinik --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">🏥 Penilaian Klinik</div>
                <div class="card-subtitle">{{ $chartKlinik['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap">
                <canvas id="chartKlinik"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart Dokter --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👨‍⚕️ Penilaian Dokter</div>
                <div class="card-subtitle">{{ $chartDokter['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap">
                <canvas id="chartDokter"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart Perawat --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👩‍⚕️ Penilaian Perawat</div>
                <div class="card-subtitle">{{ $chartPerawat['total'] }} responden</div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap">
                <canvas id="chartPerawat"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Recent Komplain --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">⚠️ Komplain Terbaru</div>
            <div class="card-subtitle">5 komplain terakhir</div>
        </div>
        <a href="{{ route('dashboard.admin.komplain') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
    </div>
    @if($komplain->isEmpty())
        <div class="card-body" style="text-align:center; color:var(--muted); padding:40px;">
            Belum ada komplain masuk
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No. Telp</th>
                        <th>Komplain</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($komplain as $k)
                    <tr>
                        <td><strong>{{ $k->nama }}</strong></td>
                        <td style="color:var(--muted)">{{ $k->no_telp }}</td>
                        <td style="max-width:320px;">
                            <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5;">
                                {{ $k->komplain }}
                            </span>
                        </td>
                        <td style="color:var(--muted); white-space:nowrap; font-size:12px;">
                            {{ $k->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
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
    const ctx = document.getElementById(id).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah Penilai',
                data: data.data,
                backgroundColor: [COLORS.baik.bg, COLORS.cukup.bg, COLORS.kurang.bg],
                borderColor:     [COLORS.baik.border, COLORS.cukup.border, COLORS.kurang.border],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw} penilai`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { family: 'Nunito', size: 12 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { family: 'Nunito', size: 11 }, maxRotation: 0 },
                    grid: { display: false }
                }
            }
        }
    });
}

makeChart('chartKlinik',  @json($chartKlinik));
makeChart('chartDokter',  @json($chartDokter));
makeChart('chartPerawat', @json($chartPerawat));
</script>
@endpush
