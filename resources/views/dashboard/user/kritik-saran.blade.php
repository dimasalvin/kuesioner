@extends('layouts.dashboard')
@section('title', 'Kritik & Saran Saya')
@section('page-title', 'Kritik & Saran Saya')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">💬 Kritik & Saran untuk {{ $nakes ? $nakes->nama : Auth::user()->name }}</div>
            <div class="card-subtitle">{{ $kritik->total() }} masukan dari pasien</div>
        </div>
    </div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
        @forelse($kritik as $k)
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $k->pasien_nama }}</div>
                <div style="font-size:11px;color:var(--muted);">
                    {{ \Carbon\Carbon::parse($k->created_at)->tanggalWib() }}
                    • {{ \Carbon\Carbon::parse($k->created_at)->jamWib() }}
                </div>
            </div>
            <div style="padding:12px 14px;background:var(--teal-light);border-radius:9px;
                        border-left:3px solid var(--teal);font-size:14px;line-height:1.7;">
                {{ $k->kritik_saran }}
            </div>
        </div>
        @empty
        <div style="text-align:center;color:var(--muted);padding:48px;">
            💬 Belum ada kritik & saran masuk
        </div>
        @endforelse

        @if($kritik->hasPages())
        <div class="pagination" style="padding:12px 0 0;border-top:1px solid var(--border);margin-top:8px;">
            @if($kritik->onFirstPage())<span class="page-link" style="opacity:.4;">‹ Prev</span>
            @else<a href="{{ $kritik->previousPageUrl() }}" class="page-link">‹ Prev</a>@endif
            @foreach($kritik->getUrlRange(max(1,$kritik->currentPage()-2),min($kritik->lastPage(),$kritik->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page==$kritik->currentPage()?'active':'' }}">{{ $page }}</a>
            @endforeach
            @if($kritik->hasMorePages())<a href="{{ $kritik->nextPageUrl() }}" class="page-link">Next ›</a>
            @else<span class="page-link" style="opacity:.4;">Next ›</span>@endif
        </div>
        @endif
    </div>
</div>
@endsection
