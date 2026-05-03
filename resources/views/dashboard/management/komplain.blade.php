@extends('layouts.dashboard')
@section('title', 'Komplain Pasien')
@section('page-title', 'Komplain Pasien')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">⚠️ Daftar Komplain</div>
            <div class="card-subtitle">{{ $komplain->total() }} komplain masuk</div>
        </div>
    </div>
    <div style="padding:16px 22px; border-bottom:1px solid var(--border);">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" style="max-width:300px;"
                   placeholder="Cari nama pasien atau isi komplain...">
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request('search'))
                <a href="{{ request()->url() }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Nama</th><th>No. Telp</th><th>Komplain</th><th>Tanggal</th></tr></thead>
            <tbody>
                @forelse($komplain as $k)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ ($komplain->currentPage() - 1) * $komplain->perPage() + $loop->iteration }}</td>
                    <td><strong>{{ $k->nama }}</strong></td>
                    <td style="color:var(--muted); font-size:13px;">{{ $k->no_telp }}</td>
                    <td style="max-width:400px;">
                        <div style="padding:10px 14px; background:var(--coral-light); border-radius:8px;
                                    border-left:3px solid var(--coral); font-size:13px; line-height:1.6;">
                            {{ $k->komplain }}
                        </div>
                    </td>
                    <td style="color:var(--muted); font-size:12px; white-space:nowrap;">
                        {{ $k->created_at->tanggalWib() }}<br>
                        <span style="font-size:11px;">{{ $k->created_at->jamWib() }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--muted); padding:48px;">🎉 Tidak ada komplain</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($komplain->hasPages())
    <div class="pagination">
        @if($komplain->onFirstPage())<span class="page-link" style="opacity:.4;">‹ Prev</span>
        @else<a href="{{ $komplain->previousPageUrl() }}" class="page-link">‹ Prev</a>@endif
        @foreach($komplain->getUrlRange(max(1,$komplain->currentPage()-2),min($komplain->lastPage(),$komplain->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page==$komplain->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($komplain->hasMorePages())<a href="{{ $komplain->nextPageUrl() }}" class="page-link">Next ›</a>
        @else<span class="page-link" style="opacity:.4;">Next ›</span>@endif
    </div>
    @endif
</div>
@endsection
