@extends('layouts.dashboard')
@section('title', 'Data Kuesioner')
@section('page-title', 'Data Kuesioner')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Semua Data Kuesioner</div>
            <div class="card-subtitle">{{ $list->total() }} entri</div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pasien</th>
                    <th>No. Telp</th>
                    <th>Dokter Dinilai</th>
                    <th>Perawat Dinilai</th>
                    <th>Komplain</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $k)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $k->id }}</td>
                    <td><strong>{{ $k->nama }}</strong></td>
                    <td style="color:var(--muted); font-size:13px;">{{ $k->no_telp }}</td>
                    <td style="font-size:13px;">
                        {{ $k->dokterRel?->dokter?->nama ?? '—' }}
                    </td>
                    <td style="font-size:13px;">
                        {{ $k->perawatRel?->perawat?->nama ?? '—' }}
                    </td>
                    <td>
                        @if($k->has_complain)
                            <span class="badge badge-coral">Ada Komplain</span>
                        @else
                            <span class="badge badge-teal">Tidak Ada</span>
                        @endif
                    </td>
                    <td style="color:var(--muted); font-size:12px; white-space:nowrap;">
                        {{ $k->created_at->tanggalWib() }}
                    </td>
                    <td>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:var(--muted); padding:40px;">
                        Belum ada data kuesioner
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($list->hasPages())
    <div class="pagination">
        @if($list->onFirstPage())
            <span class="page-link" style="opacity:.4;">‹ Prev</span>
        @else
            <a href="{{ $list->previousPageUrl() }}" class="page-link">‹ Prev</a>
        @endif
        @foreach($list->getUrlRange(max(1,$list->currentPage()-2), min($list->lastPage(),$list->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page==$list->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($list->hasMorePages())
            <a href="{{ $list->nextPageUrl() }}" class="page-link">Next ›</a>
        @else
            <span class="page-link" style="opacity:.4;">Next ›</span>
        @endif
    </div>
    @endif
</div>
@endsection
