@extends('layouts.dashboard')
@section('title', 'Penilaian Saya')
@section('page-title', 'Penilaian Saya')

@section('content')

{{-- Header greeting --}}
<div style="background:linear-gradient(135deg, var(--teal), var(--teal-dark)); border-radius:var(--radius);
            padding:24px 28px; margin-bottom:24px; color:white; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:16px;">
    <div>
        <div style="font-size:13px; opacity:.8; margin-bottom:4px; font-weight:600;">
            {{ $tipe === 'dokter' ? '👨‍⚕️ Dokter' : '👩‍⚕️ Perawat' }}
        </div>
        <div style="font-family:'Caveat',cursive; font-size:28px; line-height:1.2;">{{ $nakes->nama }}</div>
        @if(isset($nakes->spesialisasi) && $nakes->spesialisasi)
            <div style="font-size:13px; opacity:.75; margin-top:4px;">{{ $nakes->spesialisasi }}</div>
        @endif
    </div>
    <div style="text-align:right;">
        <div style="font-size:40px; font-weight:800; line-height:1;">{{ number_format($rataRata, 2) }}</div>
        <div style="font-size:12px; opacity:.8;">rata-rata dari {{ $total }} penilai</div>
        <div style="font-size:22px; margin-top:4px;">
            @for($i=1;$i<=5;$i++)
                {{ $i <= round($rataRata) ? '★' : '☆' }}
            @endfor
        </div>
    </div>
</div>

{{-- Stat mini cards --}}
<div class="stats-grid mb-20">
    <div class="stat-card teal">
        <div class="stat-icon">📋</div>
        <div class="stat-value">{{ $total }}</div>
        <div class="stat-label">Total Penilai</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">⭐</div>
        <div class="stat-value">{{ number_format($rataRata, 2) }}</div>
        <div class="stat-label">Rata-rata Bintang</div>
    </div>
    <div class="stat-card teal" style="border-top-color:var(--teal)">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $chart['data'][0] }}</div>
        <div class="stat-label">Penilaian Baik</div>
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
            <div>
                <div class="card-title">📊 Distribusi Penilaian</div>
                <div class="card-subtitle">Berdasarkan {{ $total }} penilai</div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap" style="height:220px;">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Kritik & Saran --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">💬 Kritik & Saran Terbaru</div>
        </div>
        <div class="card-body" style="padding:16px;">
            @if($kritik->isEmpty())
                <div style="text-align:center; color:var(--muted); padding:24px 0;">
                    Belum ada kritik & saran
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($kritik as $k)
                    <div style="padding:12px 14px; background:var(--teal-light); border-radius:10px;
                                border-left:3px solid var(--teal); font-size:13px; line-height:1.6;
                                color:var(--text);">
                        "{{ $k }}"
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Per-Question breakdown --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">📝 Rata-rata Per Pertanyaan</div>
    </div>
    <div class="card-body">
        @php
        $pertanyaan = $tipe === 'dokter' ? [
            1=>'Nakes informatif dalam menjelaskan penyakit',
            2=>'Mendengarkan keluhan dengan baik',
            3=>'Memeriksa dengan teliti',
            4=>'Memberikan diagnosis yang jelas',
            5=>'Penjelasan resep mudah dipahami',
            6=>'Bersikap profesional',
            7=>'Menghormati privasi pasien',
            8=>'Tersedia sesuai jadwal',
            9=>'Waktu konsultasi cukup',
            10=>'Memberikan saran gaya hidup',
            11=>'Pasien puas dengan tindakan medis',
            12=>'Memberikan informed consent',
            13=>'Mudah dihubungi',
            14=>'Bekerja sama dengan tim',
            15=>'Nakes ramah dan bersahabat',
        ] : [
            1=>'Nakes informatif dalam menjelaskan prosedur',
            2=>'Cepat tanggap terhadap kebutuhan pasien',
            3=>'Bersikap empati',
            4=>'Melakukan tindakan dengan hati-hati',
            5=>'Menjelaskan prosedur dengan jelas',
            6=>'Menjaga kebersihan saat tindakan',
            7=>'Menghormati privasi pasien',
            8=>'Memperkenalkan diri sebelum tindakan',
            9=>'Komunikatif',
            10=>'Memberikan dukungan psikologis',
            11=>'Tepat waktu',
            12=>'Memberikan edukasi kesehatan',
            13=>'Bekerja sama dengan tim',
            14=>'Pasien merasa aman',
            15=>'Nakes ramah dan bersahabat',
        ];
        @endphp

        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($pertanyaan as $no => $teks)
            @php $val = $perQ[$no] ?? 0; $pct = ($val / 5) * 100; @endphp
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="font-size:11px; font-weight:800; color:var(--muted); min-width:20px; text-align:right;">
                    {{ $no }}
                </div>
                <div style="flex:1; font-size:13px; color:var(--text); min-width:0;">
                    <div style="margin-bottom:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $teks }}
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="flex:1; height:7px; background:var(--border); border-radius:4px; overflow:hidden;">
                            <div style="width:{{ $pct }}%; height:100%; border-radius:4px;
                                        background:{{ $val >= 3.5 ? 'var(--teal)' : ($val >= 2.5 ? 'var(--gold)' : 'var(--coral)') }};
                                        transition:width .5s ease;">
                            </div>
                        </div>
                        <span style="font-size:12px; font-weight:800; min-width:28px; text-align:right;
                                     color:{{ $val >= 3.5 ? 'var(--teal-dark)' : ($val >= 2.5 ? '#996B00' : 'var(--coral)') }};">
                            {{ $val }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
new Chart(document.getElementById('myChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($chart['labels']),
        datasets: [{
            label: 'Jumlah Penilai',
            data: @json($chart['data']),
            backgroundColor: ['rgba(43,191,164,.85)', 'rgba(244,200,66,.85)', 'rgba(255,107,107,.85)'],
            borderColor:     ['#1E9A87', '#D4A800', '#CC4444'],
            borderWidth: 2, borderRadius: 8, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ` ${c.raw} penilai` } } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { family:'Nunito', size:12 } }, grid: { color:'rgba(0,0,0,.05)' } },
            x: { ticks: { font: { family:'Nunito', size:11 }, maxRotation:0 }, grid: { display:false } }
        }
    }
});
</script>
@endpush
