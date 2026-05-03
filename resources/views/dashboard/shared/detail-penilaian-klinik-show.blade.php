@extends('layouts.dashboard')
@section('title', 'Detail Penilaian Klinik')
@section('page-title', 'Detail Penilaian Klinik')

@section('content')

@php
$user = auth()->user();
if ($user->isAdmin())       $backRoute = route('dashboard.admin.detail-penilaian-klinik-list');
elseif ($user->isManagement()) $backRoute = route('dashboard.management.detail-penilaian-klinik-list');
else                        $backRoute = route('dashboard.user.detail-penilaian', ['tipe'=>'klinik']);

// Hitung rata-rata dari jawaban (keyed by pertanyaan_id)
$totalSkor = 0; $jumlah = 0;
foreach($pertanyaan as $p) {
    if(isset($jawaban[$p->id])) { $totalSkor += $jawaban[$p->id]->nilai; $jumlah++; }
}
$rataRata = $jumlah > 0 ? round($totalSkor/$jumlah, 2) : 0;
$stars    = round($rataRata);
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="{{ $backRoute }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <div style="font-size:14px;color:var(--muted);">
        Penilaian klinik dari <strong>{{ $kuesioner->nama }}</strong>
    </div>
</div>

{{-- Summary --}}
<div style="background:linear-gradient(135deg,var(--sky),#3A82C8);border-radius:var(--radius);
            padding:20px 24px;margin-bottom:20px;color:white;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
        <div style="font-size:12px;opacity:.8;margin-bottom:4px;">🏥 Fasilitas Klinik</div>
        <div style="font-family:'Caveat',cursive;font-size:26px;line-height:1.2;">Penilaian Klinik</div>
        <div style="font-size:12px;opacity:.7;margin-top:8px;">
            Pasien: {{ $kuesioner->nama }} •
            {{ $kuesioner->created_at->indonesiaFormat() }}
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:42px;font-weight:800;line-height:1;">{{ $rataRata }}</div>
        <div style="font-size:22px;margin-top:2px;">
            @for($i=1;$i<=5;$i++)
                <span style="color:{{ $i<=$stars?'#F4C842':'rgba(255,255,255,.3)' }}">★</span>
            @endfor
        </div>
        <div style="font-size:11px;opacity:.75;margin-top:4px;">dari {{ $jumlah }} pertanyaan</div>
    </div>
</div>

{{-- Breakdown per pertanyaan --}}
<div class="card">
    <div class="card-header"><div class="card-title">📝 Jawaban Per Pertanyaan</div></div>
    <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($pertanyaan as $p)
            @php
                $exists = isset($jawaban[$p->id]);
                $val    = $exists ? $jawaban[$p->id]->nilai : 0;
                $pct    = $exists ? ($val/5)*100 : 0;
                $color  = $val>=4?'var(--teal)':($val>=3?'var(--gold)':'var(--coral)');
            @endphp
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="font-size:11px;font-weight:800;color:var(--muted);min-width:22px;text-align:right;padding-top:2px;">
                    {{ $loop->iteration }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:6px;line-height:1.4;">{{ $p->teks }}</div>
                    @if(!$exists)
                    <div style="font-size:12px;color:var(--muted);font-style:italic;">— Pertanyaan belum ada saat kuesioner diisi</div>
                    @else
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
                            <div style="width:{{ $pct }}%;height:100%;background:{{ $color }};border-radius:4px;"></div>
                        </div>
                        <div style="display:flex;gap:2px;flex-shrink:0;">
                            @for($s=1;$s<=5;$s++)
                            <span style="font-size:16px;color:{{ $s<=$val?'#F4C842':'#D0D8E0' }}">★</span>
                            @endfor
                        </div>
                        <span style="font-size:12px;font-weight:800;min-width:20px;color:{{ $color }};">{{ $val }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
