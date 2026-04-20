@extends('layouts.dashboard')
@section('title', $user ? 'Edit User' : 'Tambah User')
@section('page-title', $user ? 'Edit User' : 'Tambah User')

@section('content')
<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">{{ $user ? '✏️ Edit User: '.$user->name : '➕ Tambah User Baru' }}</div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:16px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $user ? route('dashboard.admin.users.update', $user) : route('dashboard.admin.users.store') }}">
                @csrf
                @if($user) @method('PUT') @endif

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $user?->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $user?->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password {{ $user ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input type="password" name="password" class="form-control"
                           {{ $user ? '' : 'required' }} placeholder="Minimal 8 karakter">
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="Ulangi password">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" id="roleSelect" required onchange="toggleNakes()">
                        <option value="">Pilih role...</option>
                        <option value="administrator" {{ old('role',$user?->role)=='administrator' ? 'selected':'' }}>Administrator</option>
                        <option value="management"    {{ old('role',$user?->role)=='management'    ? 'selected':'' }}>Management</option>
                        <option value="user"          {{ old('role',$user?->role)=='user'          ? 'selected':'' }}>User (Dokter / Perawat)</option>
                    </select>
                </div>

                {{-- Nakes section: only visible for role=user --}}
                <div id="nakesSection" style="display:none;">
                    <div style="padding:16px; background:var(--bg); border-radius:10px; margin-bottom:18px; border:1px solid var(--border);">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label">Tipe Nakes</label>
                            <select name="tipe_nakes" class="form-control" id="tipeNakes" onchange="toggleNakesId()">
                                <option value="">Pilih tipe...</option>
                                <option value="dokter"  {{ old('tipe_nakes',$user?->tipe_nakes)=='dokter'  ? 'selected':'' }}>Dokter</option>
                                <option value="perawat" {{ old('tipe_nakes',$user?->tipe_nakes)=='perawat' ? 'selected':'' }}>Perawat</option>
                            </select>
                        </div>

                        <div id="dokterSelect" style="display:none;">
                            <label class="form-label">Pilih Dokter</label>
                            <select name="nakes_id" class="form-control" id="dokterDropdown">
                                <option value="">Pilih nama dokter...</option>
                                @foreach($dokters as $d)
                                    <option value="{{ $d->id }}"
                                        {{ old('nakes_id',$user?->nakes_id)==$d->id && $user?->tipe_nakes=='dokter' ? 'selected':'' }}>
                                        {{ $d->nama }} {{ $d->spesialisasi ? '('.$d->spesialisasi.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="perawatSelect" style="display:none;">
                            <label class="form-label">Pilih Perawat</label>
                            <select name="nakes_id" class="form-control" id="perawatDropdown">
                                <option value="">Pilih nama perawat...</option>
                                @foreach($perawats as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('nakes_id',$user?->nakes_id)==$p->id && $user?->tipe_nakes=='perawat' ? 'selected':'' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="aktif" value="1"
                               {{ old('aktif', $user ? $user->aktif : true) ? 'checked' : '' }}
                               style="accent-color:var(--teal); width:16px; height:16px;">
                        <span class="form-label" style="margin:0;">User Aktif</span>
                    </label>
                </div>

                <div style="display:flex; gap:12px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center; padding:12px;">
                        {{ $user ? 'Simpan Perubahan' : 'Tambah User' }}
                    </button>
                    <a href="{{ route('dashboard.admin.users') }}" class="btn btn-ghost" style="padding:12px 20px;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleNakes() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('nakesSection').style.display = role === 'user' ? 'block' : 'none';
    if (role !== 'user') {
        document.getElementById('tipeNakes').value = '';
        document.getElementById('dokterSelect').style.display  = 'none';
        document.getElementById('perawatSelect').style.display = 'none';
    }
}

function toggleNakesId() {
    const tipe = document.getElementById('tipeNakes').value;
    document.getElementById('dokterSelect').style.display  = tipe === 'dokter'  ? 'block' : 'none';
    document.getElementById('perawatSelect').style.display = tipe === 'perawat' ? 'block' : 'none';
    // clear the non-active one
    if (tipe === 'dokter')  document.getElementById('perawatDropdown').value = '';
    if (tipe === 'perawat') document.getElementById('dokterDropdown').value  = '';
}

// Run on page load (for edit mode)
document.addEventListener('DOMContentLoaded', () => {
    toggleNakes();
    toggleNakesId();
});
</script>
@endpush
