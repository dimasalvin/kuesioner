@extends('layouts.dashboard')
@section('title', 'Penilaian Tenaga Kesehatan')
@section('page-title', 'Penilaian Tenaga Kesehatan')

@section('content')
<div class="grid-2 mb-20">
    <div class="card">
        <div class="card-header">
            <div><div class="card-title">👨‍⚕️ Penilaian Dokter</div>
            <div class="card-subtitle">{{ $chartDokter['total'] }} responden</div></div>
        </div>
        <div class="card-body"><div class="chart-wrap" style="height:200px;"><canvas id="chartDokter"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div><div class="card-title">👩‍⚕️ Penilaian Perawat</div>
            <div class="card-subtitle">{{ $chartPerawat['total'] }} responden</div></div>
        </div>
        <div class="card-body"><div class="chart-wrap" style="height:200px;"><canvas id="chartPerawat"></canvas></div></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">🏅 Ranking Dokter</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Spesialisasi</th><th>Penilai</th><th>Rata-rata</th></tr></thead>
                <tbody>
                    @forelse($ratingDokter as $d)
                    <tr>
                        <td><strong style="font-size:13px;">{{ $d->nama }}</strong></td>
                        <td style="font-size:12px;color:var(--muted);">{{ $d->spesialisasi ?? '—' }}</td>
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
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">🏅 Ranking Perawat</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Penilai</th><th>Rata-rata</th></tr></thead>
                <tbody>
                    @forelse($ratingPerawat as $p)
                    <tr>
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
                    <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px;">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function makeChart(id, data) {
    new Chart(document.getElementById(id).getContext('2d'), {
        type:'bar', data:{ labels:data.labels, datasets:[{
            label:'Jumlah Penilai', data:data.data,
            backgroundColor:['rgba(43,191,164,.85)','rgba(244,200,66,.85)','rgba(255,107,107,.85)'],
            borderColor:['#1E9A87','#D4A800','#CC4444'],
            borderWidth:2,borderRadius:8,borderSkipped:false }]},
        options:{responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>` ${c.raw} penilai`}}},
            scales:{y:{beginAtZero:true,ticks:{stepSize:1,font:{family:'Nunito',size:12}},grid:{color:'rgba(0,0,0,.05)'}},
                    x:{ticks:{font:{family:'Nunito',size:11},maxRotation:0},grid:{display:false}}}}
    });
}
makeChart('chartDokter',  @json($chartDokter));
makeChart('chartPerawat', @json($chartPerawat));
</script>
@endpush
