<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Timezone WIB (UTC+7) untuk seluruh aplikasi ──────────
        // Setelah ini, semua Carbon / now() / created_at otomatis UTC+7
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        // Pastikan Carbon macro untuk format Indonesia tersedia di Blade
        // Contoh: $model->created_at->indonesiaFormat() => "Senin, 01 Jan 2025 08:30"
        Carbon::macro('indonesiaFormat', function () {
            /** @var Carbon $this */
            return $this->timezone('Asia/Jakarta')
                        ->translatedFormat('l, d F Y H:i');
        });

        Carbon::macro('tanggalWib', function () {
            /** @var Carbon $this */
            return $this->timezone('Asia/Jakarta')->format('d M Y');
        });

        Carbon::macro('jamWib', function () {
            /** @var Carbon $this */
            return $this->timezone('Asia/Jakarta')->format('H:i');
        });

        Carbon::macro('diffWib', function () {
            /** @var Carbon $this */
            return $this->timezone('Asia/Jakarta')->diffForHumans();
        });
    }
}
