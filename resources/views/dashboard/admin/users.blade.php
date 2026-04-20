@extends('layouts.dashboard')
@section('title', 'Kelola User')
@section('page-title', 'Kelola User')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">👥 Daftar User</div>
            <div class="card-subtitle">{{ $users->total() }} user terdaftar</div>
        </div>
        <a href="{{ route('dashboard.admin.users.create') }}" class="btn btn-primary">
            + Tambah User
        </a>
    </div>

    {{-- Filter --}}
    <div style="padding:16px 22px; border-bottom:1px solid var(--border); display:flex; gap:10px; flex-wrap:wrap;">
        <form method="GET" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" style="max-width:240px;"
                   placeholder="Cari nama / email...">
            <select name="role" class="form-control" style="max-width:180px;">
                <option value="">Semua Role</option>
                <option value="administrator" {{ request('role')=='administrator' ? 'selected':'' }}>Administrator</option>
                <option value="management"    {{ request('role')=='management'    ? 'selected':'' }}>Management</option>
                <option value="user"          {{ request('role')=='user'          ? 'selected':'' }}>User (Nakes)</option>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request()->hasAny(['search','role']))
                <a href="{{ route('dashboard.admin.users') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tipe Nakes</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $u->id }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%;
                                        background:linear-gradient(135deg,var(--teal),var(--teal-dark));
                                        display:flex; align-items:center; justify-content:center;
                                        font-weight:800; font-size:12px; color:white; flex-shrink:0;">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:14px;">{{ $u->name }}</div>
                                @if($u->nakes_id)
                                    <div style="font-size:11px; color:var(--muted);">
                                        {{ $u->isDokter() ? $u->dokter?->spesialisasi : 'Perawat' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--muted); font-size:13px;">{{ $u->email }}</td>
                    <td>
                        @if($u->role === 'administrator')
                            <span class="badge badge-purple">Administrator</span>
                        @elseif($u->role === 'management')
                            <span class="badge badge-sky">Management</span>
                        @else
                            <span class="badge badge-teal">User</span>
                        @endif
                    </td>
                    <td>
                        @if($u->tipe_nakes === 'dokter')
                            <span class="badge badge-teal">Dokter</span>
                        @elseif($u->tipe_nakes === 'perawat')
                            <span class="badge badge-sky">Perawat</span>
                        @else
                            <span style="color:var(--muted); font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($u->aktif)
                            <span class="badge badge-teal">● Aktif</span>
                        @else
                            <span class="badge badge-coral">○ Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('dashboard.admin.users.edit', $u) }}"
                               class="btn btn-ghost btn-sm">Edit</a>
                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('dashboard.admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:var(--muted); padding:40px;">
                        Tidak ada user ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="pagination">
        @if($users->onFirstPage())
            <span class="page-link" style="opacity:.4;">‹ Prev</span>
        @else
            <a href="{{ $users->previousPageUrl() }}" class="page-link">‹ Prev</a>
        @endif

        @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $users->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach

        @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="page-link">Next ›</a>
        @else
            <span class="page-link" style="opacity:.4;">Next ›</span>
        @endif
    </div>
    @endif
</div>
@endsection
