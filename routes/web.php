<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\OperasionalController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\HppController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\FotoOrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\WaTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// Publik
Route::get('/status/{token}',                           [StatusController::class, 'show'])->name('status.order');
Route::post('orders/{order:token_publik}/review',       [ReviewController::class, 'store'])->middleware('throttle:5,60')->name('orders.review.store');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── PROFIL ────────────────────────────────────────────────────────────
    Route::get('profil',   [ProfileController::class, 'edit'])->name('profil.index');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profil.update');

    // ── ORDERS ──────────────────────────────────────────────────────────
    // literal path (create) harus sebelum wildcard ({order})
    Route::get('orders/create',                [OrderController::class, 'create'])->middleware('permission:orders.manage')->name('orders.create');
    Route::get('orders',                       [OrderController::class, 'index'])->name('orders.index');
    Route::post('orders',                      [OrderController::class, 'store'])->middleware('permission:orders.manage')->name('orders.store');
    Route::get('orders/{order}',               [OrderController::class, 'show'])->middleware('permission:orders.manage')->name('orders.show');
    Route::get('orders/{order}/edit',          [OrderController::class, 'edit'])->middleware('permission:orders.manage')->name('orders.edit');
    Route::put('orders/{order}',               [OrderController::class, 'update'])->middleware('permission:orders.manage')->name('orders.update');
    Route::patch('orders/{order}/status',      [OrderController::class, 'updateStatus'])->middleware('permission:orders.manage')->name('orders.status');
    Route::patch('orders/{order}/lokasi',      [OrderController::class, 'updateLokasi'])->middleware('permission:lokasi')->name('orders.lokasi');
    Route::patch('orders/{order}/tandai-lunas',[OrderController::class, 'tandaiLunas'])->middleware('permission:orders.manage')->name('orders.lunas');
    Route::get('orders/{order}/nota',          [OrderController::class, 'cetakNota'])->middleware('permission:orders.manage')->name('orders.nota');
    Route::post('orders/{order}/kirim-wa',     [OrderController::class, 'kirimUlangWa'])->middleware('permission:orders.manage')->name('orders.wa');
    Route::post('orders/{order}/kirim-invoice',[OrderController::class, 'kirimInvoice'])->middleware('permission:orders.manage')->name('orders.invoice');
    // ── FOTO ────────────────────────────────────────────────────────────
    Route::post('orders/{order}/foto',         [FotoOrderController::class, 'store'])->middleware('permission:orders.manage')->name('orders.foto.store');
    Route::delete('fotos/{foto}',              [FotoOrderController::class, 'destroy'])->middleware('permission:orders.manage')->name('orders.foto.destroy');

    // ── PELANGGAN ─────────────────────────────────────────────────────────
    Route::get('pelanggans',                   [PelangganController::class, 'index'])->middleware('permission:pelanggan')->name('pelanggans.index');
    Route::get('pelanggans/cari',              [PelangganController::class, 'cari'])->middleware('throttle:60,1')->name('pelanggans.cari');
    Route::post('pelanggans',                  [PelangganController::class, 'store'])->middleware('permission:pelanggan')->name('pelanggans.store');
    Route::get('pelanggans/{pelanggan}',       [PelangganController::class, 'show'])->middleware('permission:pelanggan')->name('pelanggans.show');
    Route::put('pelanggans/{pelanggan}',       [PelangganController::class, 'update'])->middleware('permission:pelanggan')->name('pelanggans.update');

    // ── REVIEW (staff bisa lihat, customer submit via publik) ────────────
    Route::get('reviews',                      [ReviewController::class, 'index'])->middleware('admin-or-owner')->name('reviews.index');

    // ── LOKASI ────────────────────────────────────────────────────────────
    Route::get('lokasi',                       [LokasiController::class, 'index'])->middleware('permission:lokasi')->name('lokasi.index');
    Route::post('lokasi',                      [LokasiController::class, 'store'])->middleware('permission:lokasi')->name('lokasi.store');
    Route::put('lokasi/{lokasi}',              [LokasiController::class, 'update'])->middleware('permission:lokasi')->name('lokasi.update');
    Route::patch('lokasi/{lokasi}/toggle',     [LokasiController::class, 'toggleAktif'])->middleware('permission:lokasi')->name('lokasi.toggle');
    Route::get('lokasi/{lokasi}/harga',                       [LokasiController::class, 'harga'])->middleware('permission:lokasi')->name('lokasi.harga');
    Route::post('lokasi/{lokasi}/harga-layanan',              [LokasiController::class, 'setHargaLayanan'])->middleware('permission:lokasi')->name('lokasi.harga-layanan.set');
    Route::delete('lokasi/{lokasi}/harga-layanan/{layanan}',  [LokasiController::class, 'hapusHargaLayanan'])->middleware('permission:lokasi')->name('lokasi.harga-layanan.hapus');

    // ── HPP ───────────────────────────────────────────────────────────────
    Route::get('hpp/laporan',    [HppController::class, 'laporan'])->middleware('permission:hpp')->name('hpp.laporan');
    Route::get('hpp',            [HppController::class, 'index'])->middleware('owner')->name('hpp.index');
    Route::post('hpp',           [HppController::class, 'store'])->middleware('owner')->name('hpp.store');
    Route::put('hpp/{hpp}',      [HppController::class, 'update'])->middleware('owner')->name('hpp.update');
    Route::delete('hpp/{hpp}',   [HppController::class, 'destroy'])->middleware('owner')->name('hpp.destroy');

    // ── LAPORAN ───────────────────────────────────────────────────────────
    Route::get('laporan',              [LaporanController::class, 'index'])->middleware('permission:laporan')->name('laporan');
    Route::get('laporan/export-pdf',   [LaporanController::class, 'exportPdf'])->middleware('permission:laporan')->name('laporan.pdf');
    Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])->middleware('permission:laporan')->name('laporan.excel');

    // ── MASTER LAYANAN ────────────────────────────────────────────────────
    Route::get('layanans',                          [LayananController::class, 'index'])->middleware('permission:layanans')->name('layanans.index');
    Route::post('layanans',                         [LayananController::class, 'store'])->middleware('permission:layanans')->name('layanans.store');
    Route::put('layanans/{layanan}',                [LayananController::class, 'update'])->middleware('permission:layanans')->name('layanans.update');
    Route::patch('layanans/{layanan}/toggle-aktif', [LayananController::class, 'toggleAktif'])->middleware('permission:layanans')->name('layanans.toggle');

    // ── VOUCHER ───────────────────────────────────────────────────────────
    Route::get('vouchers/cek',               [VoucherController::class, 'cek'])->middleware(['permission:vouchers', 'throttle:30,1'])->name('vouchers.cek');
    Route::get('vouchers',                   [VoucherController::class, 'index'])->middleware('permission:vouchers')->name('vouchers.index');
    Route::post('vouchers',                  [VoucherController::class, 'store'])->middleware('permission:vouchers')->name('vouchers.store');
    Route::put('vouchers/{voucher}',         [VoucherController::class, 'update'])->middleware('permission:vouchers')->name('vouchers.update');
    Route::patch('vouchers/{voucher}/toggle',[VoucherController::class, 'toggleAktif'])->middleware('permission:vouchers')->name('vouchers.toggle');
    Route::delete('vouchers/{voucher}',      [VoucherController::class, 'destroy'])->middleware('permission:vouchers')->name('vouchers.destroy');

    // ── STOK ──────────────────────────────────────────────────────────────
    Route::get('stok',                   [StokController::class, 'index'])->middleware('permission:stok')->name('stok.index');
    Route::post('stok',                  [StokController::class, 'store'])->middleware('permission:stok')->name('stok.store');
    Route::put('stok/{stok}',            [StokController::class, 'update'])->middleware('permission:stok')->name('stok.update');
    Route::post('stok/{stok}/mutasi',    [StokController::class, 'mutasi'])->middleware('permission:stok')->name('stok.mutasi');
    Route::get('stok/{stok}/riwayat',   [StokController::class, 'riwayat'])->middleware('permission:stok')->name('stok.riwayat');

    // ── REWARD ────────────────────────────────────────────────────────────
    Route::get('rewards/poin',             [RewardController::class, 'kelolaPoin'])->middleware('permission:rewards')->name('rewards.poin');
    Route::post('rewards/poin',            [RewardController::class, 'tambahPoinManual'])->middleware('permission:rewards')->name('rewards.poin.tambah');
    Route::get('rewards',                  [RewardController::class, 'index'])->middleware('permission:rewards')->name('rewards.index');
    Route::post('rewards',                 [RewardController::class, 'store'])->middleware('permission:rewards')->name('rewards.store');
    Route::put('rewards/{reward}',         [RewardController::class, 'update'])->middleware('permission:rewards')->name('rewards.update');
    Route::patch('rewards/{reward}/toggle',[RewardController::class, 'toggleAktif'])->middleware('permission:rewards')->name('rewards.toggle');
    Route::delete('rewards/{reward}',      [RewardController::class, 'destroy'])->middleware('permission:rewards')->name('rewards.destroy');

    // ── OPERASIONAL ───────────────────────────────────────────────────────
    Route::get('operasional',                  [OperasionalController::class, 'index'])->middleware('permission:operasional')->name('operasional.index');
    Route::post('operasional',                 [OperasionalController::class, 'store'])->middleware('permission:operasional')->name('operasional.store');
    Route::delete('operasional/{operasional}', [OperasionalController::class, 'destroy'])->middleware('permission:operasional')->name('operasional.destroy');

    // ── TEMPLATE WA ───────────────────────────────────────────────────────
    Route::get('wa-templates',                         [WaTemplateController::class, 'index'])->middleware('permission:wa_template')->name('wa-templates.index');
    Route::get('wa-templates/{waTemplate}/edit',       [WaTemplateController::class, 'edit'])->middleware('permission:wa_template')->name('wa-templates.edit');
    Route::patch('wa-templates/{waTemplate}',          [WaTemplateController::class, 'update'])->middleware('permission:wa_template')->name('wa-templates.update');
    Route::post('wa-templates/{waTemplate}/reset',     [WaTemplateController::class, 'reset'])->middleware('permission:wa_template')->name('wa-templates.reset');

    // ── KARYAWAN (owner only) ─────────────────────────────────────────────
    Route::get('karyawans',                              [KaryawanController::class, 'index'])->middleware('owner')->name('karyawans.index');
    Route::post('karyawans',                             [KaryawanController::class, 'store'])->middleware('owner')->name('karyawans.store');
    Route::get('karyawans/{karyawan}',                   [KaryawanController::class, 'show'])->middleware('owner')->name('karyawans.show');
    Route::put('karyawans/{karyawan}',                   [KaryawanController::class, 'update'])->middleware('owner')->name('karyawans.update');
    Route::patch('karyawans/{karyawan}/toggle-aktif',    [KaryawanController::class, 'toggleAktif'])->middleware('owner')->name('karyawans.toggle');
    Route::patch('karyawans/{karyawan}/reset-password',  [KaryawanController::class, 'resetPassword'])->middleware('owner')->name('karyawans.password');
    Route::post('karyawans/permissions',                 [KaryawanController::class, 'savePermissions'])->middleware('owner')->name('karyawans.permissions');
});

require __DIR__ . '/auth.php';
