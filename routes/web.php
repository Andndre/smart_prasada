<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChunkUploadController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\PanoramaController;
use App\Http\Controllers\Admin\VideoPeninggalanController;
use App\Http\Controllers\ArMarkerCameraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KritikSaranController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LaporanPeninggalanController;
use App\Http\Controllers\MapsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('guest.home');
});

// Language switching route
Route::get('/language/{locale}', [LanguageController::class, 'changeLanguage'])->name('language.change');

Route::group(['middleware' => ['auth', 'user']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('guest.home');
    Route::get('/statistik', [HomeController::class, 'statistik'])->name('guest.statistik');
    Route::get('/statistik/rapor', [HomeController::class, 'rapor'])->name('guest.statistik.rapor');
    Route::get('/panduan', [HomeController::class, 'panduan'])->name('guest.panduan');
    Route::get('/pengaturan', [HomeController::class, 'pengaturan'])->name('guest.pengaturan');
    Route::get('/pengembang', [HomeController::class, 'pengembang'])->name('guest.pengembang');
    Route::get('/marker', [HomeController::class, 'arMarker'])->name('guest.ar-marker');
    Route::get('/ar-marker/camera', [ArMarkerCameraController::class, 'index'])->name('ar-marker.camera');
    Route::get('/ar-marker/katalog', [HomeController::class, 'arMarkerKatalog'])->name('ar-marker.katalog');

    // Maps routes
    Route::get('/maps', [HomeController::class, 'maps'])->name('guest.maps');
    Route::get('/maps/view', [MapsController::class, 'view'])->name('guest.maps.view');
    Route::get('/maps/peninggalan', [MapsController::class, 'peninggalan'])->name('guest.maps.peninggalan');

    // E-Learning routes
    Route::get('/kunjungi-peninggalan', [HomeController::class, 'kunjungiPeninggalan'])->name('guest.elearning');
    Route::get('/kunjungi-peninggalan/era/{era_id}', [HomeController::class, 'kunjungiPeninggalanEra'])
        ->whereNumber('era_id')
        ->name('guest.elearning.era');
    Route::get('/kunjungi-peninggalan/materi/{materi_id}', [HomeController::class, 'elearningMateri'])->name('guest.elearning.materi');
    Route::get('/kunjungi-peninggalan/materi/{materi_id}/pretest', [HomeController::class, 'elearningPretest'])->name('guest.elearning.pretest');
    Route::post('/kunjungi-peninggalan/materi/{materi_id}/pretest', [HomeController::class, 'submitPretest'])->name('guest.elearning.pretest.submit');
    Route::get('/kunjungi-peninggalan/materi/{materi_id}/posttest', [HomeController::class, 'elearningPosttest'])->name('guest.elearning.posttest');
    Route::post('/kunjungi-peninggalan/materi/{materi_id}/posttest', [HomeController::class, 'submitPosttest'])->name('guest.elearning.posttest.submit');
    Route::get('/kunjungi-peninggalan/ebook/{ebook_id}', [HomeController::class, 'elearningEbook'])->name('guest.elearning.ebook');

    // Mark e-book as read (AJAX)
    Route::post('/kunjungi-peninggalan/ebook/{ebook_id}/read', [HomeController::class, 'markEbookRead'])->name('guest.elearning.ebook.read');

    // Tugas routes
    Route::get('/kunjungi-peninggalan/materi/{materi_id}/tugas', [HomeController::class, 'elearningTugas'])->name('guest.elearning.tugas');

    // Situs detail route
    Route::get('/situs/{situs_id}', [HomeController::class, 'situsDetail'])->name('guest.situs.detail');

    // Kritik dan Saran routes
    Route::get('/kritik-saran', [KritikSaranController::class, 'index'])->name('guest.kritik-saran');
    Route::post('/kritik-saran', [KritikSaranController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('guest.kritik-saran.store');

    // Laporan Peninggalan routes
    Route::get('/laporan-peninggalan', [LaporanPeninggalanController::class, 'index'])->name('guest.laporan-peninggalan');
    Route::get('/laporan-peninggalan/create', [LaporanPeninggalanController::class, 'create'])->name('guest.laporan-peninggalan.create');
    Route::post('/laporan-peninggalan', [LaporanPeninggalanController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('guest.laporan-peninggalan.store');
    Route::get('/laporan-peninggalan/{id}', [LaporanPeninggalanController::class, 'show'])->name('guest.laporan-peninggalan.show');
    Route::post('/laporan-peninggalan/{id}/like', [LaporanPeninggalanController::class, 'toggleLike'])->name('guest.laporan-peninggalan.like');
    Route::post('/laporan-peninggalan/{id}/comment', [LaporanPeninggalanController::class, 'storeComment'])->name('guest.laporan-peninggalan.comment');

    // Video Peninggalan routes
    Route::get('/video-peninggalan', [HomeController::class, 'videoPeninggalan'])->name('guest.video-peninggalan');
    Route::get('/video-peninggalan/{id}', [HomeController::class, 'videoPeninggalanShow'])->name('guest.video-peninggalan.show');
});

// AR routes - Using token-based authentication
Route::middleware('ar.token')->get('/situs/{situs_id}/ar/{museum_id}', [HomeController::class, 'arMuseum'])->name('ar.museum');

// Public 360 Panorama Viewer routes (no auth required)
Route::get('/panorama/{situsId}', [HomeController::class, 'panoramaViewer'])->name('panorama.viewer');
Route::get('/api/panorama/{situsId}', [PanoramaController::class, 'viewer'])->name('api.panorama.viewer');

Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Admin management routes
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::get('/admin/materi', [AdminController::class, 'materi'])->name('admin.materi');
    Route::get('/admin/materi/create', [AdminController::class, 'createMateri'])->name('admin.materi.create');
    Route::post('/admin/materi', [AdminController::class, 'storeMateri'])->name('admin.materi.store');
    Route::get('/admin/materi/{id}', [AdminController::class, 'showMateri'])->name('admin.materi.show');
    Route::get('/admin/materi/{id}/edit', [AdminController::class, 'editMateri'])->name('admin.materi.edit');
    Route::put('/admin/materi/{id}', [AdminController::class, 'updateMateri'])->name('admin.materi.update');
    Route::delete('/admin/materi/{id}', [AdminController::class, 'destroyMateri'])->name('admin.materi.destroy');
    Route::post('/admin/materi/update-order', [AdminController::class, 'updateMateriOrder'])->name('admin.materi.update-order');

    // Pretest routes
    Route::get('/admin/materi/{materi_id}/pretest', [AdminController::class, 'pretest'])->name('admin.pretest');
    Route::get('/admin/materi/{materi_id}/pretest/create', [AdminController::class, 'createPretest'])->name('admin.pretest.create');
    Route::post('/admin/materi/{materi_id}/pretest', [AdminController::class, 'storePretest'])->name('admin.pretest.store');
    Route::get('/admin/materi/{materi_id}/pretest/{pretest_id}', [AdminController::class, 'showPretest'])->name('admin.pretest.show');
    Route::get('/admin/materi/{materi_id}/pretest/{pretest_id}/edit', [AdminController::class, 'editPretest'])->name('admin.pretest.edit');
    Route::put('/admin/materi/{materi_id}/pretest/{pretest_id}', [AdminController::class, 'updatePretest'])->name('admin.pretest.update');
    Route::delete('/admin/materi/{materi_id}/pretest/{pretest_id}', [AdminController::class, 'destroyPretest'])->name('admin.pretest.destroy');

    // Posttest routes
    Route::get('/admin/materi/{materi_id}/posttest', [AdminController::class, 'posttest'])->name('admin.posttest');
    Route::get('/admin/materi/{materi_id}/posttest/create', [AdminController::class, 'createPosttest'])->name('admin.posttest.create');
    Route::post('/admin/materi/{materi_id}/posttest', [AdminController::class, 'storePosttest'])->name('admin.posttest.store');
    Route::get('/admin/materi/{materi_id}/posttest/{posttest_id}', [AdminController::class, 'showPosttest'])->name('admin.posttest.show');
    Route::get('/admin/materi/{materi_id}/posttest/{posttest_id}/edit', [AdminController::class, 'editPosttest'])->name('admin.posttest.edit');
    Route::put('/admin/materi/{materi_id}/posttest/{posttest_id}', [AdminController::class, 'updatePosttest'])->name('admin.posttest.update');
    Route::delete('/admin/materi/{materi_id}/posttest/{posttest_id}', [AdminController::class, 'destroyPosttest'])->name('admin.posttest.destroy');

    // Tugas routes
    Route::get('/admin/materi/{materi_id}/tugas', [AdminController::class, 'tugas'])->name('admin.tugas');
    Route::get('/admin/materi/{materi_id}/tugas/create', [AdminController::class, 'createTugas'])->name('admin.tugas.create');
    Route::post('/admin/materi/{materi_id}/tugas', [AdminController::class, 'storeTugas'])->name('admin.tugas.store');
    Route::get('/admin/materi/{materi_id}/tugas/{tugas_id}/edit', [AdminController::class, 'editTugas'])->name('admin.tugas.edit');
    Route::put('/admin/materi/{materi_id}/tugas/{tugas_id}', [AdminController::class, 'updateTugas'])->name('admin.tugas.update');
    Route::delete('/admin/materi/{materi_id}/tugas/{tugas_id}', [AdminController::class, 'destroyTugas'])->name('admin.tugas.destroy');

    // Situs Peninggalan routes
    Route::get('/admin/situs', [AdminController::class, 'situs'])->name('admin.situs');
    Route::get('/admin/situs/create', [AdminController::class, 'createSitus'])->name('admin.situs.create');
    Route::post('/admin/situs', [AdminController::class, 'storeSitus'])->name('admin.situs.store');
    Route::get('/admin/situs/{situs_id}', [AdminController::class, 'showSitus'])->name('admin.situs.show');
    Route::get('/admin/situs/{situs_id}/edit', [AdminController::class, 'editSitus'])->name('admin.situs.edit');
    Route::put('/admin/situs/{situs_id}', [AdminController::class, 'updateSitus'])->name('admin.situs.update');
    Route::delete('/admin/situs/{situs_id}', [AdminController::class, 'destroySitus'])->name('admin.situs.destroy');

    // Virtual Living Museum routes
    Route::get('/admin/virtual-living-museum', [AdminController::class, 'virtualMuseum'])->name('admin.virtual-museum');
    Route::get('/admin/virtual-living-museum/create', [AdminController::class, 'createVirtualMuseum'])->name('admin.virtual-museum.create');
    Route::post('/admin/virtual-living-museum', [AdminController::class, 'storeVirtualMuseum'])->name('admin.virtual-museum.store');
    Route::get('/admin/virtual-living-museum/{museum_id}', [AdminController::class, 'showVirtualMuseum'])->name('admin.virtual-museum.show');
    Route::get('/admin/virtual-living-museum/{museum_id}/edit', [AdminController::class, 'editVirtualMuseum'])->name('admin.virtual-museum.edit');
    Route::put('/admin/virtual-living-museum/{museum_id}', [AdminController::class, 'updateVirtualMuseum'])->name('admin.virtual-museum.update');
    Route::delete('/admin/virtual-living-museum/{museum_id}', [AdminController::class, 'destroyVirtualMuseum'])->name('admin.virtual-museum.destroy');

    // Virtual Living Museum Object routes
    Route::get('/admin/virtual-museum/{museum_id}/objects/create', [AdminController::class, 'createVirtualMuseumObject'])->name('admin.virtual-museum-object.create');
    Route::post('/admin/virtual-museum/{museum_id}/objects', [AdminController::class, 'storeVirtualMuseumObject'])->name('admin.virtual-museum-object.store');
    Route::get('/admin/virtual-museum-object/{object_id}', [AdminController::class, 'showVirtualMuseumObject'])->name('admin.virtual-museum-object.show');
    Route::get('/admin/virtual-museum-object/{object_id}/edit', [AdminController::class, 'editVirtualMuseumObject'])->name('admin.virtual-museum-object.edit');
    Route::put('/admin/virtual-museum-object/{object_id}', [AdminController::class, 'updateVirtualMuseumObject'])->name('admin.virtual-museum-object.update');
    Route::delete('/admin/virtual-museum-object/{object_id}', [AdminController::class, 'destroyVirtualMuseumObject'])->name('admin.virtual-museum-object.destroy');

    // Chunked Upload (GLB / large files)
    Route::post('/admin/chunk-upload/chunk', [ChunkUploadController::class, 'storeChunk'])->name('admin.chunk-upload.chunk');
    Route::post('/admin/chunk-upload/finalize', [ChunkUploadController::class, 'finalize'])->name('admin.chunk-upload.finalize');
    Route::get('/admin/chunk-upload/status', [ChunkUploadController::class, 'status'])->name('admin.chunk-upload.status');
    Route::delete('/admin/chunk-upload/abort', [ChunkUploadController::class, 'abort'])->name('admin.chunk-upload.abort');

    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/reports/{id}', [AdminController::class, 'showReport'])->name('admin.reports.show');
    Route::delete('/admin/reports/{id}', [AdminController::class, 'destroyReport'])->name('admin.reports.destroy');
    Route::get('/admin/feedback', [AdminController::class, 'feedback'])->name('admin.feedback');
    Route::delete('/admin/feedback/{id}', [AdminController::class, 'destroyFeedback'])->name('admin.feedback.destroy');

    Route::get('/admin/ebook/{materi_id}', [AdminController::class, 'ebook'])->name('admin.ebook');
    Route::get('/admin/ebook/{materi_id}/create', [AdminController::class, 'createEbook'])->name('admin.ebook.create');
    Route::post('/admin/ebook/{materi_id}', [AdminController::class, 'storeEbook'])->name('admin.ebook.store');
    Route::get('/admin/ebook/{materi_id}/{ebook_id}/edit', [AdminController::class, 'editEbook'])->name('admin.ebook.edit');
    Route::put('/admin/ebook/{materi_id}/{ebook_id}', [AdminController::class, 'updateEbook'])->name('admin.ebook.update');
    Route::delete('/admin/ebook/{ebook_id}', [AdminController::class, 'destroyEbook'])->name('admin.ebook.destroy');
    Route::get('/admin/riwayat-pengembang', [AdminController::class, 'riwayatPengembang'])->name('admin.riwayat-pengembang');
    Route::get('/admin/riwayat-pengembang/create', [AdminController::class, 'createRiwayatPengembang'])->name('admin.riwayat-pengembang.create');
    Route::post('/admin/riwayat-pengembang', [AdminController::class, 'storeRiwayatPengembang'])->name('admin.riwayat-pengembang.store');
    Route::get('/admin/riwayat-pengembang/{id}/edit', [AdminController::class, 'editRiwayatPengembang'])->name('admin.riwayat-pengembang.edit');
    Route::put('/admin/riwayat-pengembang/{id}', [AdminController::class, 'updateRiwayatPengembang'])->name('admin.riwayat-pengembang.update');
    Route::delete('/admin/riwayat-pengembang/{id}', [AdminController::class, 'destroyRiwayatPengembang'])->name('admin.riwayat-pengembang.destroy');

    // Katalog
    Route::get('admin/katalog', [KatalogController::class, 'edit'])->name('admin.katalog.edit');
    Route::put('admin/katalog', [KatalogController::class, 'update'])->name('admin.katalog.update');

    // Video Peninggalan
    Route::get('admin/video-peninggalan', [VideoPeninggalanController::class, 'index'])->name('admin.video-peninggalan.index');
    Route::get('admin/video-peninggalan/create', [VideoPeninggalanController::class, 'create'])->name('admin.video-peninggalan.create');
    Route::post('admin/video-peninggalan', [VideoPeninggalanController::class, 'store'])->name('admin.video-peninggalan.store');
    Route::get('admin/video-peninggalan/{id}/edit', [VideoPeninggalanController::class, 'edit'])->name('admin.video-peninggalan.edit');
    Route::put('admin/video-peninggalan/{id}', [VideoPeninggalanController::class, 'update'])->name('admin.video-peninggalan.update');
    Route::delete('admin/video-peninggalan/{id}', [VideoPeninggalanController::class, 'destroy'])->name('admin.video-peninggalan.destroy');

    // Panorama / 360 VR Tour routes
    Route::prefix('admin/panorama')->name('admin.panorama.')->group(function () {
        // Scene CRUD
        Route::get('scenes/{situsId}', [PanoramaController::class, 'index'])->name('scenes.index');
        Route::post('scenes', [PanoramaController::class, 'storeScene'])->name('scenes.store');
        Route::put('scenes/{id}', [PanoramaController::class, 'updateScene'])->name('scenes.update');
        Route::delete('scenes/{id}', [PanoramaController::class, 'destroyScene'])->name('scenes.destroy');
        Route::post('scenes/reorder', [PanoramaController::class, 'reorderScenes'])->name('scenes.reorder');

        // Hotspot CRUD
        Route::post('hotspots', [PanoramaController::class, 'storeHotspot'])->name('hotspots.store');
        Route::put('hotspots/{id}', [PanoramaController::class, 'updateHotspot'])->name('hotspots.update');
        Route::delete('hotspots/{id}', [PanoramaController::class, 'destroyHotspot'])->name('hotspots.destroy');

        // Hotspot Templates CRUD
        Route::get('templates', [PanoramaController::class, 'indexTemplates'])->name('templates.index');
        Route::post('templates', [PanoramaController::class, 'storeTemplate'])->name('templates.store');
        Route::put('templates/{id}', [PanoramaController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('templates/{id}', [PanoramaController::class, 'destroyTemplate'])->name('templates.destroy');

        // Image Upload
        Route::post('upload', [PanoramaController::class, 'uploadImage'])->name('upload');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
