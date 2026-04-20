@extends('layouts.dashboard')
@section('title', 'Manajemen Kuesioner')
@section('page-title', 'Manajemen Kuesioner')

@push('styles')
<style>
.tab-bar { display:flex; gap:4px; margin-bottom:24px; background:var(--surface);
           padding:6px; border-radius:12px; border:1px solid var(--border);
           width:fit-content; }
.tab-btn { padding:8px 20px; border-radius:9px; font-size:13px; font-weight:700;
           border:none; background:none; cursor:pointer; color:var(--muted);
           font-family:'Nunito',sans-serif; transition:all .15s; white-space:nowrap; }
.tab-btn.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
.tab-btn:hover:not(.active) { background:var(--bg); color:var(--text); }

/* Drag-drop list */
.q-drag-list { display:flex; flex-direction:column; gap:8px; }
.q-drag-item {
    display:flex; align-items:center; gap:12px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; padding:12px 16px;
    transition:box-shadow .15s, border-color .15s;
    cursor:default;
}
.q-drag-item:hover { border-color:var(--teal); box-shadow:0 2px 12px rgba(43,191,164,.1); }
.q-drag-item.dragging { opacity:.5; box-shadow:0 8px 24px rgba(0,0,0,.15);
                         border-color:var(--teal); }
.q-drag-item.drag-over { border-color:var(--teal); border-style:dashed; background:var(--teal-light); }
.drag-handle { cursor:grab; color:var(--muted); font-size:18px; padding:4px;
               flex-shrink:0; user-select:none; }
.drag-handle:active { cursor:grabbing; }
.q-nomor { font-size:11px; font-weight:800; color:var(--muted); min-width:24px;
           text-align:center; flex-shrink:0; }
.q-teks { flex:1; font-size:14px; font-weight:600; color:var(--text); line-height:1.4;
          min-width:0; }
.q-teks.inactive { color:var(--muted); text-decoration:line-through; }

/* Toggle switch */
.toggle-sw { position:relative; width:40px; height:22px; flex-shrink:0; }
.toggle-sw input { opacity:0; width:0; height:0; position:absolute; }
.toggle-track {
    position:absolute; inset:0; border-radius:11px;
    background:var(--border); cursor:pointer; transition:background .2s;
}
.toggle-track::after {
    content:''; position:absolute; width:16px; height:16px; border-radius:50%;
    background:white; top:3px; left:3px; transition:transform .2s;
    box-shadow:0 1px 3px rgba(0,0,0,.2);
}
input:checked + .toggle-track { background:var(--teal); }
input:checked + .toggle-track::after { transform:translateX(18px); }

/* Edit inline */
.q-edit-form { display:none; flex:1; gap:8px; align-items:center; }
.q-edit-form.show { display:flex; }
.q-edit-form input { flex:1; padding:7px 11px; border:2px solid var(--teal);
                     border-radius:8px; font-size:14px; font-family:'Nunito',sans-serif;
                     color:var(--text); }
.q-edit-form input:focus { outline:none; }

/* Add form */
.add-form { display:flex; gap:10px; align-items:center; margin-top:16px; padding-top:16px;
            border-top:2px dashed var(--border); }
.add-form input { flex:1; padding:11px 14px; border:2px solid var(--border);
                  border-radius:9px; font-size:14px; font-family:'Nunito',sans-serif;
                  color:var(--text); }
.add-form input:focus { outline:none; border-color:var(--teal);
                        box-shadow:0 0 0 3px rgba(43,191,164,.1); }

/* Save order btn */
#saveOrderBtn {
    display:none; position:fixed; bottom:24px; right:28px;
    padding:12px 24px; background:var(--teal); color:white;
    border:none; border-radius:12px; font-size:14px; font-weight:800;
    font-family:'Nunito',sans-serif; cursor:pointer;
    box-shadow:0 4px 20px rgba(43,191,164,.45);
    animation:float .4s cubic-bezier(.34,1.56,.64,1);
    z-index:100;
}
@keyframes float { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
#saveOrderBtn:hover { background:var(--teal-dark); }
</style>
@endpush

@section('content')

{{-- Tab bar --}}
<div class="tab-bar">
    @foreach($kategoriLabel as $key => $label)
    <a href="{{ route('dashboard.admin.manajemen-kuesioner', ['kategori' => $key]) }}"
       class="tab-btn {{ $kategori === $key ? 'active' : '' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">{{ $kategoriLabel[$kategori] }} — Daftar Pertanyaan</div>
            <div class="card-subtitle">
                {{ $pertanyaan->count() }} pertanyaan •
                {{ $pertanyaan->where('aktif', true)->count() }} aktif •
                Seret untuk mengatur urutan
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <span style="font-size:12px;color:var(--muted);">
                💡 Geser <span style="font-size:16px;">⠿</span> untuk reorder
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="q-drag-list" id="dragList">
            @forelse($pertanyaan as $i => $p)
            <div class="q-drag-item" data-id="{{ $p->id }}" draggable="true">
                <span class="drag-handle" title="Geser untuk mengubah urutan">⠿</span>
                <span class="q-nomor">{{ $i + 1 }}</span>

                {{-- Teks normal --}}
                <span class="q-teks {{ !$p->aktif ? 'inactive' : '' }}" id="teks-{{ $p->id }}">
                    {{ $p->teks }}
                </span>

                {{-- Form edit inline --}}
                <form class="q-edit-form" id="editForm-{{ $p->id }}"
                      method="POST"
                      action="{{ route('dashboard.admin.manajemen-kuesioner.update', $p) }}">
                    @csrf @method('PUT')
                    <input type="text" name="teks" value="{{ $p->teks }}"
                           id="editInput-{{ $p->id }}" required>
                    <input type="hidden" name="aktif" value="{{ $p->aktif ? '1' : '0' }}">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick="cancelEdit({{ $p->id }})">Batal</button>
                </form>

                {{-- Actions --}}
                <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;"
                     id="actions-{{ $p->id }}">

                    {{-- Toggle aktif --}}
                    <label class="toggle-sw" title="{{ $p->aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                        <input type="checkbox"
                               {{ $p->aktif ? 'checked' : '' }}
                               onchange="toggleAktif({{ $p->id }}, this)">
                        <span class="toggle-track"></span>
                    </label>

                    {{-- Edit --}}
                    <button class="btn btn-ghost btn-sm" onclick="startEdit({{ $p->id }})"
                            title="Edit pertanyaan">✏️</button>

                    {{-- Hapus --}}
                    <form method="POST"
                          action="{{ route('dashboard.admin.manajemen-kuesioner.destroy', $p) }}"
                          onsubmit="return confirm('Hapus pertanyaan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:var(--muted);padding:32px;">
                Belum ada pertanyaan. Tambahkan di bawah.
            </div>
            @endforelse
        </div>

        {{-- Add pertanyaan baru --}}
        <form method="POST" action="{{ route('dashboard.admin.manajemen-kuesioner.store') }}"
              class="add-form">
            @csrf
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <input type="text" name="teks" placeholder="+ Tulis pertanyaan baru..." required>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>
</div>

{{-- Floating save order button --}}
<button id="saveOrderBtn" onclick="saveOrder()">💾 Simpan Urutan</button>
@endsection

@push('scripts')
<script>
var dragSrc = null;
var orderChanged = false;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Drag & Drop ──────────────────────────────────────────────────────
document.querySelectorAll('.q-drag-item').forEach(function(item) {
    item.addEventListener('dragstart', function(e) {
        dragSrc = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    item.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        document.querySelectorAll('.q-drag-item').forEach(function(i) {
            i.classList.remove('drag-over');
        });
        refreshNumbers();
    });
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (this !== dragSrc) this.classList.add('drag-over');
    });
    item.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });
    item.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        if (dragSrc && this !== dragSrc) {
            var list = document.getElementById('dragList');
            var items = Array.from(list.children);
            var srcIdx  = items.indexOf(dragSrc);
            var destIdx = items.indexOf(this);
            if (srcIdx < destIdx) {
                list.insertBefore(dragSrc, this.nextSibling);
            } else {
                list.insertBefore(dragSrc, this);
            }
            orderChanged = true;
            document.getElementById('saveOrderBtn').style.display = 'block';
            refreshNumbers();
        }
    });
});

function refreshNumbers() {
    document.querySelectorAll('.q-drag-item').forEach(function(item, i) {
        var nomor = item.querySelector('.q-nomor');
        if (nomor) nomor.textContent = i + 1;
    });
}

function saveOrder() {
    var ids = Array.from(document.querySelectorAll('.q-drag-item'))
                  .map(function(el) { return parseInt(el.dataset.id); });

    fetch('{{ route("dashboard.admin.manajemen-kuesioner.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('saveOrderBtn').style.display = 'none';
        orderChanged = false;
        showToast('✓ Urutan berhasil disimpan!');
    });
}

// ── Toggle aktif ─────────────────────────────────────────────────────
function toggleAktif(id, checkbox) {
    fetch('{{ url("dashboard/admin/manajemen-kuesioner") }}/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var teks = document.getElementById('teks-' + id);
        if (data.aktif) {
            teks.classList.remove('inactive');
        } else {
            teks.classList.add('inactive');
        }
        showToast(data.message);
    });
}

// ── Edit inline ──────────────────────────────────────────────────────
function startEdit(id) {
    document.getElementById('teks-' + id).style.display     = 'none';
    document.getElementById('editForm-' + id).classList.add('show');
    document.getElementById('actions-' + id).style.display  = 'none';
    document.getElementById('editInput-' + id).focus();
}

function cancelEdit(id) {
    document.getElementById('teks-' + id).style.display     = '';
    document.getElementById('editForm-' + id).classList.remove('show');
    document.getElementById('actions-' + id).style.display  = '';
}

// ── Toast ─────────────────────────────────────────────────────────────
function showToast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:70px;right:28px;background:#1A2B3C;color:white;'
        + 'padding:10px 18px;border-radius:10px;font-size:13px;font-weight:700;'
        + 'z-index:999;animation:dropIn .3s ease;box-shadow:0 4px 16px rgba(0,0,0,.2);';
    document.body.appendChild(t);
    setTimeout(function() { t.remove(); }, 2500);
}
</script>
@endpush
