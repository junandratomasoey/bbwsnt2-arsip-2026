<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ── Refresh materialized view dashboard setiap jam ────────────────
// Jalankan: php artisan schedule:work (development)
//           crontab: * * * * * php /path/to/artisan schedule:run
Schedule::call(function () {
    try {
        DB::statement('SELECT refresh_mv_dashboard_stats()');
        Log::info('mv_dashboard_stats refreshed successfully');
    } catch (\Throwable $e) {
        Log::error('Failed to refresh mv_dashboard_stats: ' . $e->getMessage());
    }
})->hourly()->name('refresh-dashboard-stats')->withoutOverlapping();

// ── Cek peminjaman yang hampir jatuh tempo (H-3) ─────────────────
Schedule::call(function () {
    $hariPeringatan = (int) \App\Models\SystemConfig::get('loan', 'reminder_days_before', 3);

    $loans = \App\Models\Loan::where('status', 'borrowed')
        ->whereDate('tgl_kembali_rencana', now()->addDays($hariPeringatan)->toDateString())
        ->with(['borrower', 'document'])
        ->get();

    foreach ($loans as $loan) {
        \App\Models\Notification::kirim(
            $loan->borrower_id,
            'loan.reminder',
            'Pengingat: Jatuh Tempo Peminjaman',
            "Dokumen \"{$loan->document?->judul}\" harus dikembalikan dalam {$hariPeringatan} hari lagi.",
            [
                'icon'       => 'ti-clock',
                'level'      => 'warning',
                'action_url' => route('loans.show', $loan),
            ]
        );
    }

    if ($loans->count() > 0) {
        Log::info("Loan reminder sent for {$loans->count()} loans");
    }
})->dailyAt('08:00')->name('loan-reminder');

// ── Tandai peminjaman overdue ─────────────────────────────────────
Schedule::call(function () {
    $count = \App\Models\Loan::where('status', 'borrowed')
        ->where('tgl_kembali_rencana', '<', now())
        ->update(['status' => 'overdue']);

    if ($count > 0) {
        Log::info("Marked {$count} loans as overdue");
    }
})->daily()->name('mark-overdue-loans');

// ── Cek dokumen yang akan kadaluwarsa (H-30) ──────────────────────
Schedule::call(function () {
    $alertDays = (int) \App\Models\SystemConfig::get('notification', 'alert_doc_expiry_days', 30);

    $docs = \App\Models\Document::whereNotNull('tgl_kedaluwarsa')
        ->whereDate('tgl_kedaluwarsa', now()->addDays($alertDays)->toDateString())
        ->whereNull('deleted_at')
        ->get();

    foreach ($docs as $doc) {
        // Notifikasi ke admin satker yang unit_kerja-nya sama
        $adminIds = \App\Models\User::role('admin_satker')
            ->where('unit_kerja_id', $doc->unit_kerja_id)
            ->pluck('id')->toArray();

        if (!empty($adminIds)) {
            \App\Models\Notification::kirim(
                $adminIds,
                'document.expiry_warning',
                'Dokumen Akan Kadaluwarsa',
                "Dokumen \"{$doc->judul}\" akan kadaluwarsa pada {$doc->tgl_kedaluwarsa->format('d M Y')}.",
                [
                    'icon'       => 'ti-alert-triangle',
                    'level'      => 'warning',
                    'action_url' => route('documents.show', $doc),
                ]
            );
        }
    }
})->weeklyOn(1, '09:00')->name('document-expiry-check');

// ── Artisan inspire (bawaan Laravel, biarkan saja) ────────────────
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
