<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ExecutiveDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitKerjaController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\Asset\AssetController;
use App\Http\Controllers\Asset\AssetTypeController;
use App\Http\Controllers\Asset\AssetConditionController;
use App\Http\Controllers\Asset\AssetGeometryController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\MilestoneController;
use App\Http\Controllers\Project\ProgressController;
use App\Http\Controllers\Project\OpRecordController;
use App\Http\Controllers\Project\OpScheduleController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Document\DocumentTypeController;
use App\Http\Controllers\Document\PhysicalLocationController;
use App\Http\Controllers\Document\LoanController;
use App\Http\Controllers\Knowledge\ArticleController;
use App\Http\Controllers\Knowledge\LibraryController;
use App\Http\Controllers\GIS\GisController;
use App\Http\Controllers\Report\ReportController;

require __DIR__.'/auth.php';

// ══════════════════════════════════════════════════════════════
// SEMUA ROUTE — wajib login + akun aktif
// ══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'user.aktif', 'user.not_locked'])->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/executive', [ExecutiveDashboardController::class, 'index'])
        ->name('dashboard.executive')
        ->middleware('permission:dashboard.executive');

    // ══════════════════════════════════════════════════════════════
    // SUPERADMIN — administrasi sistem
    // ══════════════════════════════════════════════════════════════
    Route::middleware('role:superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {

        // Users
        Route::resource('users', UserController::class);
        Route::post('users/{user}/approve',     [UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/tolak',       [UserController::class, 'tolak'])->name('users.tolak');
        Route::post('users/{user}/nonaktifkan', [UserController::class, 'nonaktifkan'])->name('users.nonaktifkan');
        Route::post('users/{user}/aktifkan',    [UserController::class, 'aktifkan'])->name('users.aktifkan');
        Route::post('users/{user}/role',        [UserController::class, 'assignRole'])->name('users.assign-role');

        // Roles
        Route::resource('roles', RoleController::class);

        // Unit Kerja
        Route::resource('unit-kerja', UnitKerjaController::class);
        Route::get('unit-kerja/{unitKerja}/ppk/create', [UnitKerjaController::class, 'createPpk'])->name('unit-kerja.ppk.create');
        Route::post('unit-kerja/{unitKerja}/ppk',       [UnitKerjaController::class, 'storePpk'])->name('unit-kerja.ppk.store');

        // Jenis Aset & Jenis Dokumen
        Route::resource('asset-types',    AssetTypeController::class);
        Route::resource('document-types', DocumentTypeController::class);

        // Konfigurasi sistem
        Route::resource('system-config', SystemConfigController::class)->only(['index', 'edit', 'update']);

        // Activity log
        Route::get('activity-log', function () {
            $logs = \App\Models\AuditLog::orderByDesc('created_at')->paginate(50);
            return view('superadmin.activity-log', compact('logs'));
        })->name('activity-log');

        // Approvals dashboard
        Route::get('approvals', [UserController::class, 'approvals'])->name('approvals');
    });

    // ── Admin Satker ─────────────────────────────────────────────────
    Route::middleware('role:superadmin|admin_satker')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users',                 [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}',          [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/tolak',   [UserController::class, 'tolak'])->name('users.tolak');
        Route::get('approvals',             [UserController::class, 'approvals'])->name('approvals');
    });

    // ══════════════════════════════════════════════════════════════
    // ASET INFRASTRUKTUR
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:asset.view')->prefix('assets')->name('assets.')->group(function () {

        Route::get('/',               [AssetController::class, 'index'])->name('index');
        Route::get('/create',         [AssetController::class, 'create'])->name('create')->middleware('permission:asset.create');
        Route::post('/',              [AssetController::class, 'store'])->name('store')->middleware('permission:asset.create');
        Route::get('/{asset}',        [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit',   [AssetController::class, 'edit'])->name('edit')->middleware('permission:asset.edit');
        Route::put('/{asset}',        [AssetController::class, 'update'])->name('update')->middleware('permission:asset.edit');
        Route::delete('/{asset}',     [AssetController::class, 'destroy'])->name('destroy')->middleware('permission:asset.delete');

        // Kondisi & inspeksi
        Route::get('/{asset}/conditions',        [AssetConditionController::class, 'index'])->name('conditions.index');
        Route::get('/{asset}/conditions/create', [AssetConditionController::class, 'create'])->name('conditions.create')->middleware('permission:asset_condition.create');
        Route::post('/{asset}/conditions',       [AssetConditionController::class, 'store'])->name('conditions.store')->middleware('permission:asset_condition.create');
        Route::get('/{asset}/conditions/{condition}/edit', [AssetConditionController::class, 'edit'])->name('conditions.edit')->middleware('permission:asset_condition.edit');
        Route::put('/{asset}/conditions/{condition}',      [AssetConditionController::class, 'update'])->name('conditions.update')->middleware('permission:asset_condition.edit');
        Route::delete('/{asset}/conditions/{condition}',   [AssetConditionController::class, 'destroy'])->name('conditions.destroy')->middleware('permission:asset.delete');

        // Geometri GIS
        Route::get('/{asset}/geometry',    [AssetGeometryController::class, 'index'])->name('geometry.index');
        Route::post('/{asset}/geometry',   [AssetGeometryController::class, 'store'])->name('geometry.store')->middleware('permission:asset_geometry.edit');
        Route::put('/{asset}/geometry/{geometry}',    [AssetGeometryController::class, 'update'])->name('geometry.update')->middleware('permission:asset_geometry.edit');
        Route::delete('/{asset}/geometry/{geometry}', [AssetGeometryController::class, 'destroy'])->name('geometry.destroy')->middleware('permission:asset_geometry.edit');
    });

    // ══════════════════════════════════════════════════════════════
    // PROYEK
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:project.view')->prefix('projects')->name('projects.')->group(function () {

        Route::get('/',               [ProjectController::class, 'index'])->name('index');
        Route::get('/create',         [ProjectController::class, 'create'])->name('create')->middleware('permission:project.create');
        Route::post('/',              [ProjectController::class, 'store'])->name('store')->middleware('permission:project.create');
        Route::get('/{project}',      [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit')->middleware('permission:project.edit');
        Route::put('/{project}',      [ProjectController::class, 'update'])->name('update')->middleware('permission:project.edit');
        Route::delete('/{project}',   [ProjectController::class, 'destroy'])->name('destroy')->middleware('permission:project.delete');

        // Milestones
        Route::get('/{project}/milestones',               [MilestoneController::class, 'index'])->name('milestones.index');
        Route::post('/{project}/milestones',              [MilestoneController::class, 'store'])->name('milestones.store')->middleware('permission:project.edit');
        Route::put('/{project}/milestones/{milestone}',   [MilestoneController::class, 'update'])->name('milestones.update')->middleware('permission:project.edit');
        Route::delete('/{project}/milestones/{milestone}',[MilestoneController::class, 'destroy'])->name('milestones.destroy')->middleware('permission:project.edit');

        // Progress / Kurva S
        Route::get('/{project}/progress',             [ProgressController::class, 'index'])->name('progress.index');
        Route::post('/{project}/progress',            [ProgressController::class, 'store'])->name('progress.store')->middleware('permission:project_progress.create');
        Route::put('/{project}/progress/{progress}',  [ProgressController::class, 'update'])->name('progress.update')->middleware('permission:project_progress.edit');
        Route::delete('/{project}/progress/{progress}',[ProgressController::class,'destroy'])->name('progress.destroy')->middleware('permission:project.delete');
    });

    // ══════════════════════════════════════════════════════════════
    // OPERASI & PEMELIHARAAN
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:op_record.view')->prefix('op')->name('op.')->group(function () {

        // Jadwal OP per aset
        Route::get('/schedules',                    [OpScheduleController::class, 'index'])->name('schedules.index');
        Route::get('/schedules/create',             [OpScheduleController::class, 'create'])->name('schedules.create')->middleware('permission:op_schedule.create');
        Route::post('/schedules',                   [OpScheduleController::class, 'store'])->name('schedules.store')->middleware('permission:op_schedule.create');
        Route::get('/schedules/{schedule}',         [OpScheduleController::class, 'show'])->name('schedules.show');
        Route::get('/schedules/{schedule}/edit',    [OpScheduleController::class, 'edit'])->name('schedules.edit')->middleware('permission:op_schedule.edit');
        Route::put('/schedules/{schedule}',         [OpScheduleController::class, 'update'])->name('schedules.update')->middleware('permission:op_schedule.edit');
        Route::post('/schedules/{schedule}/approve',[OpScheduleController::class, 'approve'])->name('schedules.approve')->middleware('permission:op_schedule.approve');

        // Rekaman OP
        Route::get('/records',                 [OpRecordController::class, 'index'])->name('records.index');
        Route::get('/records/create',          [OpRecordController::class, 'create'])->name('records.create')->middleware('permission:op_record.create');
        Route::post('/records',                [OpRecordController::class, 'store'])->name('records.store')->middleware('permission:op_record.create');
        Route::get('/records/{record}',        [OpRecordController::class, 'show'])->name('records.show');
        Route::get('/records/{record}/edit',   [OpRecordController::class, 'edit'])->name('records.edit')->middleware('permission:op_record.edit');
        Route::put('/records/{record}',        [OpRecordController::class, 'update'])->name('records.update')->middleware('permission:op_record.edit');
        Route::delete('/records/{record}',     [OpRecordController::class, 'destroy'])->name('records.destroy')->middleware('permission:op_record.delete');

        // Peta sebaran OP
        Route::get('/map', [GisController::class, 'opMap'])->name('map');
    });

    // ══════════════════════════════════════════════════════════════
    // DOKUMEN & ARSIP
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:document.view')->prefix('documents')->name('documents.')->group(function () {

        Route::get('/',               [DocumentController::class, 'index'])->name('index');
        Route::get('/create',         [DocumentController::class, 'create'])->name('create')->middleware('permission:document.create');
        Route::post('/',              [DocumentController::class, 'store'])->name('store')->middleware('permission:document.create');
        Route::get('/{document}',     [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit',[DocumentController::class, 'edit'])->name('edit')->middleware('permission:document.edit');
        Route::put('/{document}',     [DocumentController::class, 'update'])->name('update')->middleware('permission:document.edit');
        Route::delete('/{document}',  [DocumentController::class, 'destroy'])->name('destroy')->middleware('permission:document.delete');

        // Actions
        Route::get('/{document}/download',        [DocumentController::class, 'download'])->name('download')->middleware('permission:document.download');
        Route::get('/{document}/preview',         [DocumentController::class, 'preview'])->name('preview');
        Route::get('/{document}/qr',              [DocumentController::class, 'qr'])->name('qr');
        Route::get('/{document}/versions',        [DocumentController::class, 'versions'])->name('versions');
        Route::post('/{document}/approve',        [DocumentController::class, 'approve'])->name('approve')->middleware('permission:document.approve');
        Route::post('/{document}/new-version',    [DocumentController::class, 'newVersion'])->name('new-version')->middleware('permission:document.edit');
    });

    // Lokasi fisik arsip
    Route::middleware('permission:physical_location.view')->prefix('locations')->name('locations.')->group(function () {
        Route::get('/',             [PhysicalLocationController::class, 'index'])->name('index');
        Route::get('/create',       [PhysicalLocationController::class, 'create'])->name('create')->middleware('permission:physical_location.create');
        Route::post('/',            [PhysicalLocationController::class, 'store'])->name('store')->middleware('permission:physical_location.create');
        Route::get('/{location}',   [PhysicalLocationController::class, 'show'])->name('show');
        Route::get('/{location}/edit',[PhysicalLocationController::class,'edit'])->name('edit')->middleware('permission:physical_location.edit');
        Route::put('/{location}',   [PhysicalLocationController::class, 'update'])->name('update')->middleware('permission:physical_location.edit');
        Route::delete('/{location}',[PhysicalLocationController::class, 'destroy'])->name('destroy')->middleware('permission:physical_location.delete');
        Route::get('/{location}/qr',[PhysicalLocationController::class, 'generateQr'])->name('qr');
    });

    // Peminjaman
    Route::middleware('permission:loan.view')->prefix('loans')->name('loans.')->group(function () {
        Route::get('/',             [LoanController::class, 'index'])->name('index');
        Route::get('/create',       [LoanController::class, 'create'])->name('create')->middleware('permission:loan.create');
        Route::post('/',            [LoanController::class, 'store'])->name('store')->middleware('permission:loan.create');
        Route::get('/{loan}',       [LoanController::class, 'show'])->name('show');
        Route::delete('/{loan}',    [LoanController::class, 'destroy'])->name('destroy')->middleware('permission:loan.delete');

        Route::post('/{loan}/approve',    [LoanController::class, 'approve'])->name('approve')->middleware('permission:loan.approve');
        Route::post('/{loan}/tolak',      [LoanController::class, 'tolak'])->name('tolak')->middleware('permission:loan.approve');
        Route::post('/{loan}/kembalikan', [LoanController::class, 'kembalikan'])->name('kembalikan')->middleware('permission:loan.approve');
    });

    // ══════════════════════════════════════════════════════════════
    // KNOWLEDGE BASE & PERPUSTAKAAN
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:knowledge.view')->prefix('knowledge')->name('knowledge.')->group(function () {
        Route::get('/',              [ArticleController::class, 'index'])->name('index');
        Route::get('/create',        [ArticleController::class, 'create'])->name('create')->middleware('permission:knowledge.create');
        Route::post('/',             [ArticleController::class, 'store'])->name('store')->middleware('permission:knowledge.create');
        Route::get('/{article:slug}',[ArticleController::class, 'show'])->name('show');
        Route::get('/{article:slug}/edit', [ArticleController::class, 'edit'])->name('edit')->middleware('permission:knowledge.edit');
        Route::put('/{article}',     [ArticleController::class, 'update'])->name('update')->middleware('permission:knowledge.edit');
        Route::delete('/{article}',  [ArticleController::class, 'destroy'])->name('destroy')->middleware('permission:knowledge.delete');
        Route::post('/{article}/publish', [ArticleController::class, 'publish'])->name('publish')->middleware('permission:knowledge.publish');
        Route::post('/{article}/helpful', [ArticleController::class, 'helpful'])->name('helpful');
    });

    Route::middleware('permission:library.view')->prefix('library')->name('library.')->group(function () {
        Route::get('/',             [LibraryController::class, 'index'])->name('index');
        Route::get('/create',       [LibraryController::class, 'create'])->name('create')->middleware('permission:library.create');
        Route::post('/',            [LibraryController::class, 'store'])->name('store')->middleware('permission:library.create');
        Route::get('/{item}',       [LibraryController::class, 'show'])->name('show');
        Route::get('/{item}/edit',  [LibraryController::class, 'edit'])->name('edit')->middleware('permission:library.edit');
        Route::put('/{item}',       [LibraryController::class, 'update'])->name('update')->middleware('permission:library.edit');
        Route::delete('/{item}',    [LibraryController::class, 'destroy'])->name('destroy')->middleware('permission:library.edit');

        // Peminjaman buku
        Route::post('/{item}/pinjam',           [LibraryController::class, 'pinjam'])->name('pinjam')->middleware('permission:library_loan.create');
        Route::post('/loans/{loan}/kembalikan',  [LibraryController::class, 'kembalikan'])->name('loans.kembalikan')->middleware('permission:library_loan.approve');
        Route::get('/loans',                     [LibraryController::class, 'loans'])->name('loans.index');
    });

    // ══════════════════════════════════════════════════════════════
    // GIS — Peta sebaran aset
    // ══════════════════════════════════════════════════════════════
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/',              [GisController::class, 'index'])->name('index');
        Route::get('/assets',        [GisController::class, 'assetMap'])->name('assets');

        // GeoJSON API endpoints (untuk Leaflet)
        Route::get('/geojson/assets',   [GisController::class, 'geojsonAssets'])->name('geojson.assets');
        Route::get('/geojson/op',       [GisController::class, 'geojsonOp'])->name('geojson.op');
        Route::get('/geojson/projects', [GisController::class, 'geojsonProjects'])->name('geojson.projects');
    });

    // ══════════════════════════════════════════════════════════════
    // LAPORAN
    // ══════════════════════════════════════════════════════════════
    Route::middleware('permission:report.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/',               [ReportController::class, 'index'])->name('index');
        Route::get('/assets',         [ReportController::class, 'assets'])->name('assets');
        Route::get('/op',             [ReportController::class, 'op'])->name('op');
        Route::get('/projects',       [ReportController::class, 'projects'])->name('projects');
        Route::get('/documents',      [ReportController::class, 'documents'])->name('documents');
        Route::get('/loans',          [ReportController::class, 'loans'])->name('loans');

        // Export
        Route::middleware('permission:report.export')->group(function () {
            Route::get('/export/assets',    [ReportController::class, 'exportAssets'])->name('export.assets');
            Route::get('/export/op',        [ReportController::class, 'exportOp'])->name('export.op');
            Route::get('/export/projects',  [ReportController::class, 'exportProjects'])->name('export.projects');
            Route::get('/export/documents', [ReportController::class, 'exportDocuments'])->name('export.documents');
        });
    });

    // ── Notifikasi ───────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                             fn() => view('notifications.index', [
            'notifications' => auth()->user()->notifications()->paginate(20),
        ]))->name('index');
        Route::post('/{notification}/read',         function ($id) {
            \App\Models\Notification::find($id)?->markAsRead();
            return back();
        })->name('read');
        Route::post('/read-all', function () {
            auth()->user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);
            return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
        })->name('read-all');
    });

    // ── Search global ────────────────────────────────────────────────
    Route::get('/search', function (\Illuminate\Http\Request $request) {
        $q = $request->input('q', '');
        if (strlen($q) < 2) return redirect()->back();

        $results = [
            'assets'    => \App\Models\Asset::search($q)->limit(5)->get(),
            'documents' => \App\Models\Document::search($q)->limit(5)->get(),
            'knowledge' => \App\Models\KnowledgeArticle::search($q)->published()->limit(5)->get(),
        ];
        return view('search.results', compact('results', 'q'));
    })->name('search');

    // ── Profile ──────────────────────────────────────────────────────
    Route::get('/profile',       fn() => view('profile.index', ['user' => auth()->user()]))->name('profile.index');
    Route::get('/profile/edit',  fn() => view('profile.edit',  ['user' => auth()->user()]))->name('profile.edit');
    Route::put('/profile',       [UserController::class, 'updateProfile'])->name('profile.update');
});
