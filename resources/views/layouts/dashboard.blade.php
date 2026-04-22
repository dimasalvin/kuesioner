<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Klinik</title>
    <link rel="icon" type="image/png" href="{{ asset('images/foto-klinik.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/foto-klinik.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --teal:#2BBFA4; --teal-dark:#1E9A87; --teal-light:#E6F9F5;
            --coral:#FF6B6B; --coral-light:#FFF0F0;
            --sky:#5BA4E5; --sky-light:#EEF5FD;
            --gold:#F4C842;
            --purple:#7C6BE8; --purple-light:#F0EEFF;
            --bg:#F0F4F8; --surface:#fff;
            --border:#E2E8F0; --text:#1A2B3C; --muted:#7A90A8;
            --sidebar-w:240px;
            --radius:14px; --shadow:0 4px 20px rgba(0,0,0,.07);
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        html,body { height:100%; font-family:'Nunito',sans-serif; color:var(--text);
                    background:var(--bg); -webkit-font-smoothing:antialiased; }

        /* ── Sidebar ── */
        .sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
                   background:var(--text); color:white; display:flex; flex-direction:column;
                   z-index:50; transition:transform .3s ease; }
        .sidebar-logo { padding:24px 20px 20px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-logo .title { font-family:'Caveat',cursive; font-size:22px; color:white; }
        .sidebar-logo .sub   { font-size:11px; color:rgba(255,255,255,.45); margin-top:2px; }
        .sidebar-user { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.08);
                        display:flex; align-items:center; gap:12px; }
        .user-avatar { width:36px; height:36px; border-radius:50%;
                       background:linear-gradient(135deg,var(--teal),var(--teal-dark));
                       display:flex; align-items:center; justify-content:center;
                       font-weight:800; font-size:14px; flex-shrink:0; }
        .user-info .name { font-size:13px; font-weight:700; color:white; line-height:1.3; }
        .user-info .role { font-size:10px; font-weight:700; text-transform:uppercase;
                           letter-spacing:.06em; color:var(--teal); margin-top:2px; }
        nav.sidebar-nav { flex:1; padding:16px 12px; overflow-y:auto; }
        .nav-section-label { font-size:10px; font-weight:800; text-transform:uppercase;
                              letter-spacing:.1em; color:rgba(255,255,255,.3);
                              padding:10px 8px 6px; margin-top:8px; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:10px 12px;
                    border-radius:10px; text-decoration:none; color:rgba(255,255,255,.65);
                    font-size:14px; font-weight:600; transition:all .15s; margin-bottom:2px; }
        .nav-item:hover { background:rgba(255,255,255,.08); color:white; }
        .nav-item.active { background:var(--teal); color:white; box-shadow:0 4px 12px rgba(43,191,164,.35); }
        .nav-item .icon { font-size:16px; width:20px; text-align:center; flex-shrink:0; }
        .sidebar-bottom { padding:16px 12px; border-top:1px solid rgba(255,255,255,.08); }
        .logout-btn { display:flex; align-items:center; gap:10px; padding:10px 12px;
                      border-radius:10px; color:rgba(255,255,255,.55); font-size:14px;
                      font-weight:600; background:none; border:none; width:100%;
                      text-align:left; cursor:pointer; font-family:'Nunito',sans-serif;
                      transition:all .15s; }
        .logout-btn:hover { background:rgba(255,100,100,.15); color:var(--coral); }

        /* ── Main ── */
        .main-wrap { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }

        /* ── Topbar ── */
        .topbar { background:var(--surface); border-bottom:1px solid var(--border);
                  padding:0 28px; height:60px; display:flex; align-items:center;
                  justify-content:space-between; position:sticky; top:0; z-index:40;
                  box-shadow:0 1px 4px rgba(0,0,0,.04); }
        .topbar-title { font-size:17px; font-weight:800; color:var(--text); }
        .topbar-right  { display:flex; align-items:center; gap:16px; }
        .topbar-date   { font-size:12px; color:var(--muted); }
        .hamburger { display:none; background:none; border:none; cursor:pointer; padding:4px; }
        .hamburger span { display:block; width:22px; height:2px; background:var(--text);
                          margin:5px 0; border-radius:2px; }

        /* ── NOTIFICATION BELL ── */
        .notif-wrapper { position:relative; }
        .notif-bell {
            width:38px; height:38px; border-radius:50%; background:var(--bg);
            border:1px solid var(--border); display:flex; align-items:center;
            justify-content:center; cursor:pointer; font-size:18px;
            transition:background .15s; position:relative;
        }
        .notif-bell:hover { background:var(--teal-light); border-color:var(--teal); }
        .notif-badge {
            position:absolute; top:-3px; right:-3px;
            min-width:18px; height:18px; border-radius:9px;
            background:var(--coral); color:white;
            font-size:10px; font-weight:800; line-height:18px;
            text-align:center; padding:0 4px;
            border:2px solid var(--surface);
            display:none;
        }
        .notif-badge.show { display:block; }

        /* Dropdown */
        .notif-dropdown {
            position:absolute; top:calc(100% + 10px); right:0;
            width:360px; background:var(--surface);
            border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.16);
            border:1px solid var(--border); overflow:hidden;
            display:none; z-index:200;
        }
        .notif-dropdown.open { display:block;
            animation:dropIn .2s cubic-bezier(.34,1.56,.64,1); }
        @keyframes dropIn {
            from { opacity:0; transform:translateY(-8px) scale(.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        .notif-header {
            padding:14px 18px 10px;
            display:flex; align-items:center; justify-content:space-between;
            border-bottom:1px solid var(--border);
        }
        .notif-header-title { font-size:15px; font-weight:800; }
        .notif-mark-all {
            font-size:12px; color:var(--teal); font-weight:700;
            background:none; border:none; cursor:pointer; font-family:'Nunito',sans-serif;
        }
        .notif-mark-all:hover { text-decoration:underline; }
        .notif-list { max-height:380px; overflow-y:auto; }
        .notif-list::-webkit-scrollbar { width:3px; }
        .notif-list::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
        .notif-item {
            display:flex; gap:12px; padding:12px 18px;
            border-bottom:1px solid var(--border); cursor:pointer;
            transition:background .15s; text-decoration:none; color:inherit;
        }
        .notif-item:last-child { border-bottom:none; }
        .notif-item:hover { background:var(--bg); }
        .notif-item.unread { background:#FFF8F8; }
        .notif-item.unread:hover { background:#FFF0F0; }
        .notif-dot-wrap { flex-shrink:0; display:flex; align-items:flex-start; padding-top:4px; }
        .notif-dot { width:10px; height:10px; border-radius:50%; background:var(--coral); flex-shrink:0; }
        .notif-dot.read { background:transparent; border:2px solid var(--border); }
        .notif-icon-wrap {
            width:40px; height:40px; border-radius:50%; flex-shrink:0;
            background:var(--coral-light); display:flex; align-items:center;
            justify-content:center; font-size:18px;
        }
        .notif-body { flex:1; min-width:0; }
        .notif-title { font-size:13px; font-weight:700; color:var(--text);
                       white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .notif-msg   { font-size:12px; color:var(--muted); margin-top:2px;
                       display:-webkit-box; -webkit-line-clamp:2;
                       -webkit-box-orient:vertical; overflow:hidden; line-height:1.5; }
        .notif-time  { font-size:11px; color:var(--teal); font-weight:700; margin-top:4px; }
        .notif-empty { padding:32px; text-align:center; color:var(--muted); font-size:13px; }
        .notif-footer { padding:10px; border-top:1px solid var(--border); text-align:center; }
        .notif-footer a { font-size:13px; color:var(--teal); font-weight:700; text-decoration:none; }

        /* ── Page Content ── */
        .page-content { flex:1; padding:28px; }

        /* ── Stat Cards ── */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
                      gap:16px; margin-bottom:28px; }
        .stat-card { background:var(--surface); border-radius:var(--radius); padding:20px;
                     box-shadow:var(--shadow); border:1px solid var(--border);
                     display:flex; flex-direction:column; gap:8px; transition:transform .2s; }
        .stat-card:hover { transform:translateY(-2px); }
        .stat-icon  { font-size:22px; line-height:1; }
        .stat-value { font-size:28px; font-weight:800; color:var(--text); line-height:1; }
        .stat-label { font-size:12px; color:var(--muted); font-weight:600; }
        .stat-card.teal   { border-top:3px solid var(--teal); }
        .stat-card.coral  { border-top:3px solid var(--coral); }
        .stat-card.sky    { border-top:3px solid var(--sky); }
        .stat-card.purple { border-top:3px solid var(--purple); }
        .stat-card.gold   { border-top:3px solid var(--gold); }

        /* ── Card ── */
        .card { background:var(--surface); border-radius:var(--radius);
                box-shadow:var(--shadow); border:1px solid var(--border); overflow:hidden; }
        .card-header { padding:18px 22px; border-bottom:1px solid var(--border);
                       display:flex; align-items:center; justify-content:space-between; }
        .card-title    { font-size:15px; font-weight:800; color:var(--text); }
        .card-subtitle { font-size:12px; color:var(--muted); margin-top:2px; }
        .card-body     { padding:22px; }
        .chart-wrap    { position:relative; height:260px; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
        .grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px; }
        .mb-20  { margin-bottom:20px; }
        .mb-28  { margin-bottom:28px; }

        /* ── Table ── */
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { padding:11px 16px; text-align:left; background:var(--bg);
             border-bottom:2px solid var(--border); font-size:11px; font-weight:800;
             text-transform:uppercase; letter-spacing:.06em; color:var(--muted); white-space:nowrap; }
        td { padding:12px 16px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--bg); }

        /* ── Badges ── */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px;
                 border-radius:20px; font-size:11px; font-weight:800;
                 text-transform:uppercase; letter-spacing:.05em; }
        .badge-teal   { background:var(--teal-light);   color:var(--teal-dark); }
        .badge-coral  { background:var(--coral-light);  color:var(--coral); }
        .badge-sky    { background:var(--sky-light);    color:var(--sky); }
        .badge-purple { background:var(--purple-light); color:var(--purple); }
        .badge-gold   { background:#FFF9E6; color:#996B00; }

        /* ── Buttons ── */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
               border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;
               text-decoration:none; border:none; font-family:'Nunito',sans-serif;
               transition:all .15s; }
        .btn-primary { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }
        .btn-primary:hover { background:var(--teal-dark); }
        .btn-danger  { background:var(--coral-light); color:var(--coral); }
        .btn-danger:hover  { background:var(--coral); color:white; }
        .btn-ghost   { background:var(--bg); color:var(--muted); border:1px solid var(--border); }
        .btn-ghost:hover { color:var(--text); background:var(--border); }
        .btn-sm      { padding:5px 11px; font-size:12px; }

        /* ── Form ── */
        .form-group   { margin-bottom:18px; }
        .form-label   { display:block; font-size:12px; font-weight:800; color:var(--muted);
                        text-transform:uppercase; letter-spacing:.06em; margin-bottom:7px; }
        .form-control { width:100%; padding:11px 14px; border:2px solid var(--border);
                        border-radius:9px; font-size:14px; font-family:'Nunito',sans-serif;
                        color:var(--text); background:var(--surface); transition:border-color .2s; }
        .form-control:focus { outline:none; border-color:var(--teal);
                              box-shadow:0 0 0 3px rgba(43,191,164,.1); }

        /* ── Alerts ── */
        .alert { padding:12px 16px; border-radius:9px; font-size:13px; font-weight:600; margin-bottom:20px; }
        .alert-success { background:var(--teal-light); color:var(--teal-dark); border:1px solid var(--teal); }
        .alert-danger  { background:var(--coral-light); color:var(--coral); border:1px solid var(--coral); }

        /* ── Pagination ── */
        .pagination { display:flex; gap:6px; align-items:center; padding:16px 22px; border-top:1px solid var(--border); }
        .page-link { padding:6px 12px; border-radius:7px; font-size:13px; font-weight:700;
                     text-decoration:none; color:var(--muted); background:var(--bg); border:1px solid var(--border); }
        .page-link.active { background:var(--teal); color:white; border-color:var(--teal); }
        .page-link:hover:not(.active) { background:var(--border); color:var(--text); }

        /* ── Mobile ── */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); box-shadow:4px 0 24px rgba(0,0,0,.2); }
            .main-wrap { margin-left:0; }
            .hamburger { display:block; }
            .grid-2,.grid-3 { grid-template-columns:1fr; }
            .page-content { padding:16px; }
            .topbar { padding:0 16px; }
            .notif-dropdown { width:320px; right:-60px; }
            .topbar-date { display:none; }
        }
        .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:45; }
        .overlay.show { display:block; }
    </style>
    @stack('styles')
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="title">🏥 Klinik</div>
        <div class="sub">Dashboard Manajemen</div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="user-info">
            <div class="name">{{ Str::limit(auth()->user()->name, 20) }}</div>
            <div class="role">{{ auth()->user()->role }}</div>
        </div>
    </div>
    <nav class="sidebar-nav">

        @if(auth()->user()->isAdmin())
            <div class="nav-section-label">Administrator</div>
            <a href="{{ route('dashboard.admin') }}" class="nav-item {{ request()->routeIs('dashboard.admin') && !request()->routeIs('dashboard.admin.*') ? 'active' : '' }}">
                <span class="icon">📊</span> Dashboard
            </a>
            <a href="{{ route('dashboard.admin.users') }}" class="nav-item {{ request()->routeIs('dashboard.admin.users*') ? 'active' : '' }}">
                <span class="icon">👥</span> Kelola User
            </a>
            <a href="{{ route('dashboard.admin.manajemen-kuesioner') }}" class="nav-item {{ request()->routeIs('dashboard.admin.manajemen-kuesioner*') ? 'active' : '' }}">
                <span class="icon">⚙️</span> Kelola Kuesioner
            </a>
            <a href="{{ route('dashboard.admin.detail-penilaian') }}" class="nav-item {{ request()->routeIs('dashboard.admin.detail-penilaian*') ? 'active' : '' }}">
                <span class="icon">📋</span> Detail Penilaian
            </a>
            <a href="{{ route('dashboard.admin.komplain') }}" class="nav-item {{ request()->routeIs('dashboard.admin.komplain*') ? 'active' : '' }}">
                <span class="icon">⚠️</span> Komplain
            </a>
            <a href="{{ route('dashboard.admin.kritik-saran') }}" class="nav-item {{ request()->routeIs('dashboard.admin.kritik-saran*') ? 'active' : '' }}">
                <span class="icon">💬</span> Kritik & Saran
            </a>

        @elseif(auth()->user()->isManagement())
            <div class="nav-section-label">Management</div>
            <a href="{{ route('dashboard.management') }}" class="nav-item {{ request()->routeIs('dashboard.management') && !request()->routeIs('dashboard.management.*') ? 'active' : '' }}">
                <span class="icon">📊</span> Dashboard
            </a>
            <a href="{{ route('dashboard.management.penilaian-nakes') }}" class="nav-item {{ request()->routeIs('dashboard.management.penilaian-nakes*') ? 'active' : '' }}">
                <span class="icon">🏅</span> Penilaian Nakes
            </a>
            <a href="{{ route('dashboard.management.detail-penilaian') }}" class="nav-item {{ request()->routeIs('dashboard.management.detail-penilaian*') ? 'active' : '' }}">
                <span class="icon">📋</span> Detail Penilaian
            </a>
            <a href="{{ route('dashboard.management.komplain') }}" class="nav-item {{ request()->routeIs('dashboard.management.komplain*') ? 'active' : '' }}">
                <span class="icon">⚠️</span> Komplain
            </a>
            <a href="{{ route('dashboard.management.kritik-saran') }}" class="nav-item {{ request()->routeIs('dashboard.management.kritik-saran*') ? 'active' : '' }}">
                <span class="icon">💬</span> Kritik & Saran
            </a>

        @else
            <div class="nav-section-label">{{ auth()->user()->isDokter() ? 'Dokter' : 'Perawat' }}</div>
            <a href="{{ route('dashboard.user.penilaian-klinik') }}" class="nav-item {{ request()->routeIs('dashboard.user.penilaian-klinik*') ? 'active' : '' }}">
                <span class="icon">🏥</span> Penilaian Klinik
            </a>
            <a href="{{ route('dashboard.user') }}" class="nav-item {{ request()->routeIs('dashboard.user') && !request()->routeIs('dashboard.user.*') ? 'active' : '' }}">
                <span class="icon">⭐</span> Penilaian Saya
            </a>
            <a href="{{ route('dashboard.user.detail-penilaian') }}" class="nav-item {{ request()->routeIs('dashboard.user.detail-penilaian*') ? 'active' : '' }}">
                <span class="icon">📋</span> Detail Penilaian
            </a>
            <a href="{{ route('dashboard.user.kritik-saran') }}" class="nav-item {{ request()->routeIs('dashboard.user.kritik-saran*') ? 'active' : '' }}">
                <span class="icon">💬</span> Kritik & Saran
            </a>
        @endif

    </nav>
    <div class="sidebar-bottom">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <span class="icon">🚪</span> Keluar
            </button>
        </form>
    </div>
</aside>

<!-- Main -->
<div class="main-wrap">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="hamburger" onclick="openSidebar()">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-title">@yield('page-title','Dashboard')</div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date">{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</div>

            {{-- Notification Bell (admin & management only) --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isManagement())
            @php
                $notifList   = \App\Services\NotificationService::getForUser(auth()->id(), 15);
                $unreadCount = \App\Services\NotificationService::unreadCount(auth()->id());
            @endphp
            <div class="notif-wrapper" id="notifWrapper">
                <div class="notif-bell" id="notifBell" onclick="toggleNotif(event)">
                    🔔
                    <span class="notif-badge {{ $unreadCount > 0 ? 'show' : '' }}" id="notifBadge">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                </div>

                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span class="notif-header-title">🔔 Notifikasi</span>
                        @if($unreadCount > 0)
                        <button class="notif-mark-all" onclick="markAllRead()">
                            Tandai semua dibaca
                        </button>
                        @endif
                    </div>

                    <div class="notif-list" id="notifList">
                        @php
                            $routeKomplain = auth()->user()->isAdmin()
                                ? route('dashboard.admin.komplain')
                                : route('dashboard.management.komplain');
                        @endphp
                        @forelse($notifList as $notif)
                        <div class="notif-item {{ $notif->isUnread() ? 'unread' : '' }}"
                             onclick="openNotif({{ $notif->id }}, '{{ $routeKomplain }}')"
                             style="cursor:pointer;">
                            <div class="notif-dot-wrap">
                                <div class="notif-dot {{ $notif->isUnread() ? '' : 'read' }}"></div>
                            </div>
                            <div class="notif-icon-wrap">⚠️</div>
                            <div class="notif-body">
                                <div class="notif-title">{{ $notif->judul }}</div>
                                <div class="notif-msg">{{ $notif->pesan }}</div>
                                <div class="notif-time">{{ $notif->created_at->diffWib() }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="notif-empty">✅ Tidak ada notifikasi</div>
                        @endforelse
                    </div>

                    <div class="notif-footer">
                        <a href="{{ $routeKomplain }}">Lihat semua komplain →</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->has('error'))
            <div class="alert alert-danger">{{ session('error') ?? $errors->first('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

<script>
var notifOpen = false;

function toggleNotif(e) {
    e.stopPropagation();
    notifOpen = !notifOpen;
    document.getElementById('notifDropdown').classList.toggle('open', notifOpen);
    if (notifOpen) {
        setTimeout(function() { markAllRead(); }, 1500);
    }
}

function markAllRead() {
    fetch('{{ route("notif.markAllRead") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(function() {
        document.getElementById('notifBadge').classList.remove('show');
        document.querySelectorAll('.notif-item.unread').forEach(function(el) {
            el.classList.remove('unread');
        });
        document.querySelectorAll('.notif-dot').forEach(function(el) {
            el.classList.add('read');
        });
        var btn = document.querySelector('.notif-mark-all');
        if (btn) btn.style.display = 'none';
    });
}

function openNotif(id, url) {
    fetch('{{ route("notif.markRead") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id: id })
    }).then(function() {
        window.location.href = url;
    });
}

document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('notifWrapper');
    if (notifOpen && wrapper && !wrapper.contains(e.target)) {
        notifOpen = false;
        document.getElementById('notifDropdown').classList.remove('open');
    }
});

function openSidebar()  {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
}
</script>
@stack('scripts')
</body>
</html>
