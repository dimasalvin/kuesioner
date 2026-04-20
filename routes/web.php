<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KuesionerController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\ManagementController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ManajemenKuesionerController;
use App\Http\Controllers\Dashboard\NotificationController;

// ── Public: Kuesioner ─────────────────────────────────────────────────
Route::get('/',             [KuesionerController::class, 'index'])->name('kuesioner.index');
Route::post('/identitas',   [KuesionerController::class, 'storeIdentitas'])->name('kuesioner.store-identitas');
Route::get('/klinik',       [KuesionerController::class, 'klinik'])->name('kuesioner.klinik');
Route::post('/klinik',      [KuesionerController::class, 'storeKlinik'])->name('kuesioner.store-klinik');
Route::get('/dokter',       [KuesionerController::class, 'dokter'])->name('kuesioner.dokter');
Route::post('/dokter',      [KuesionerController::class, 'storeDokter'])->name('kuesioner.store-dokter');
Route::get('/perawat',      [KuesionerController::class, 'perawat'])->name('kuesioner.perawat');
Route::post('/perawat',     [KuesionerController::class, 'storePerawat'])->name('kuesioner.store-perawat');
Route::get('/komplain',     [KuesionerController::class, 'komplain'])->name('kuesioner.komplain');
Route::post('/komplain',    [KuesionerController::class, 'storeKomplain'])->name('kuesioner.store-komplain');
Route::get('/terima-kasih', [KuesionerController::class, 'thankyou'])->name('kuesioner.thankyou');

// ── Auth ──────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Notifications (admin + management) ───────────────────────────────
Route::middleware(['auth', 'role:administrator,management'])->group(function () {
    Route::post('/notif/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notif.markAllRead');
    Route::post('/notif/mark-read',     [NotificationController::class, 'markRead'])->name('notif.markRead');
});

// ── Dashboard: Administrator ──────────────────────────────────────────
Route::prefix('dashboard/admin')->name('dashboard.admin')->middleware(['auth','role:administrator'])->group(function () {
    Route::get('/',  [AdminController::class, 'index'])->name('');

    // Users
    Route::get('/users',             [AdminController::class, 'users'])->name('.users');
    Route::get('/users/create',      [AdminController::class, 'createUser'])->name('.users.create');
    Route::post('/users',            [AdminController::class, 'storeUser'])->name('.users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('.users.edit');
    Route::put('/users/{user}',      [AdminController::class, 'updateUser'])->name('.users.update');
    Route::delete('/users/{user}',   [AdminController::class, 'destroyUser'])->name('.users.destroy');

    // Manajemen Kuesioner
    Route::get('/manajemen-kuesioner',                        [ManajemenKuesionerController::class, 'index'])->name('.manajemen-kuesioner');
    Route::post('/manajemen-kuesioner',                       [ManajemenKuesionerController::class, 'store'])->name('.manajemen-kuesioner.store');
    Route::put('/manajemen-kuesioner/{pertanyaan}',           [ManajemenKuesionerController::class, 'update'])->name('.manajemen-kuesioner.update');
    Route::post('/manajemen-kuesioner/{pertanyaan}/toggle',   [ManajemenKuesionerController::class, 'toggleAktif'])->name('.manajemen-kuesioner.toggle');
    Route::delete('/manajemen-kuesioner/{pertanyaan}',        [ManajemenKuesionerController::class, 'destroy'])->name('.manajemen-kuesioner.destroy');
    Route::post('/manajemen-kuesioner/reorder',               [ManajemenKuesionerController::class, 'reorder'])->name('.manajemen-kuesioner.reorder');

    // Data Kuesioner & Komplain
    Route::get('/kuesioner',                [AdminController::class, 'kuesionerList'])->name('.kuesioner');
    Route::delete('/kuesioner/{kuesioner}', [AdminController::class, 'destroyKuesioner'])->name('.kuesioner.destroy');
    Route::get('/komplain',                 [AdminController::class, 'komplain'])->name('.komplain');

    // Kritik & Saran
    Route::get('/kritik-saran',             [AdminController::class, 'kritikSaran'])->name('.kritik-saran');

    Route::get('/chart/{type}',             [AdminController::class, 'chartApi'])->name('.chart');
});

// ── Dashboard: Management ─────────────────────────────────────────────
Route::prefix('dashboard/management')->name('dashboard.management')->middleware(['auth','role:management'])->group(function () {
    Route::get('/',             [ManagementController::class, 'index'])->name('');
    Route::get('/komplain',     [ManagementController::class, 'komplain'])->name('.komplain');
    Route::get('/kritik-saran', [ManagementController::class, 'kritikSaran'])->name('.kritik-saran');
    Route::get('/chart/{type}', [ManagementController::class, 'chartApi'])->name('.chart');
});

// ── Dashboard: User (Dokter / Perawat) ───────────────────────────────
Route::prefix('dashboard/user')->name('dashboard.user')->middleware(['auth','role:user'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('');
});
