<?php

use App\Http\Controllers\BahanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HppController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KategoriLayananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\OperasionalController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\VoucherController;
use App\Http\Middleware\NormalizePhoneNumber;
use Illuminate\Support\Facades\Route;

// ── PUBLIC MARKETING PAGES ──────────────────────────────────────────────────
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact.show');
Route::post('/contact', [PublicPageController::class, 'contactSubmit'])->middleware('throttle:5,60')->name('contact.submit');
Route::get('/privacy-policy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('/refund-policy', [PublicPageController::class, 'refund'])->name('refund');

// Publik — order tracking, review & invoice download
Route::get('/status/{token}', [StatusController::class, 'show'])->middleware('throttle:30,1')->name('status.order');
Route::get('/status/{token}/invoice', [StatusController::class, 'downloadInvoice'])->middleware('throttle:30,1')->name('status.invoice');
Route::post('orders/{order:token_publik}/review', [ReviewController::class, 'store'])->middleware('throttle:5,60')->name('orders.review.store');

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── PROFIL ────────────────────────────────────────────────────────────
    Route::get('profil', [ProfileController::class, 'edit'])->name('profil.index');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::get('contact-messages', [PublicPageController::class, 'contactMessages'])
        ->middleware('owner')->name('contact-messages.index');

    // ── ORDERS ──────────────────────────────────────────────────────────
    // literal path (create) harus sebelum wildcard ({order})
    Route::get('orders/create', [OrderController::class, 'create'])->middleware('permission:orders.manage')->name('orders.create');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('orders', [OrderController::class, 'store'])->middleware(['permission:orders.manage', NormalizePhoneNumber::class])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->middleware('permission:orders.manage')->name('orders.edit');
    Route::put('orders/{order}', [OrderController::class, 'update'])->middleware(['permission:orders.manage', NormalizePhoneNumber::class])->name('orders.update');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.manage')->name('orders.status');
    Route::patch('orders/{order}/lokasi', [OrderController::class, 'updateLokasi'])->middleware('permission:lokasi')->name('orders.lokasi');
    Route::patch('orders/{order}/tandai-lunas', [OrderController::class, 'tandaiLunas'])->middleware('permission:orders.manage')->name('orders.lunas');
    Route::get('orders/{order}/nota', [OrderController::class, 'cetakNota'])->middleware('permission:orders.manage')->name('orders.nota');
    Route::get('orders/{order}/invoice', [OrderController::class, 'downloadInvoice'])->middleware('permission:orders.manage')->name('orders.invoice');
    // ── PELANGGAN ─────────────────────────────────────────────────────────
    Route::get('pelanggans', [PelangganController::class, 'index'])->middleware('permission:pelanggan')->name('pelanggans.index');
    Route::get('pelanggans/cari', [PelangganController::class, 'cari'])->middleware(['permission:orders.manage', 'throttle:60,1'])->name('pelanggans.cari');
    Route::get('pelanggans/poin', [PelangganController::class, 'getPoinByPhone'])->middleware(['permission:orders.manage', 'throttle:60,1'])->name('pelanggans.poin');
    Route::post('pelanggans', [PelangganController::class, 'store'])->middleware(['permission:pelanggan', NormalizePhoneNumber::class])->name('pelanggans.store');
    Route::get('pelanggans/{pelanggan}', [PelangganController::class, 'show'])->middleware('permission:pelanggan')->name('pelanggans.show');
    Route::put('pelanggans/{pelanggan}', [PelangganController::class, 'update'])->middleware(['permission:pelanggan', NormalizePhoneNumber::class])->name('pelanggans.update');

    // ── REVIEW (staff bisa lihat, customer submit via publik) ────────────
    Route::get('reviews', [ReviewController::class, 'index'])->middleware('admin-or-owner')->name('reviews.index');

    // ── LOKASI ────────────────────────────────────────────────────────────
    Route::get('lokasi', [LokasiController::class, 'index'])->middleware('permission:lokasi')->name('lokasi.index');
    Route::post('lokasi', [LokasiController::class, 'store'])->middleware('permission:lokasi')->name('lokasi.store');
    Route::put('lokasi/{lokasi}', [LokasiController::class, 'update'])->middleware('permission:lokasi')->name('lokasi.update');
    Route::patch('lokasi/{lokasi}/toggle', [LokasiController::class, 'toggleAktif'])->middleware('permission:lokasi')->name('lokasi.toggle');
    Route::get('lokasi/{lokasi}/harga', [LokasiController::class, 'harga'])->middleware('permission:lokasi')->name('lokasi.harga');
    Route::post('lokasi/{lokasi}/harga-layanan', [LokasiController::class, 'setHargaLayanan'])->middleware('permission:lokasi')->name('lokasi.harga-layanan.set');
    Route::delete('lokasi/{lokasi}/harga-layanan/{layanan}', [LokasiController::class, 'hapusHargaLayanan'])->middleware('permission:lokasi')->name('lokasi.harga-layanan.hapus');

    // ── HPP ───────────────────────────────────────────────────────────────
    Route::get('hpp/laporan', [HppController::class, 'laporan'])->middleware('permission:hpp')->name('hpp.laporan');
    Route::get('hpp', [HppController::class, 'index'])->middleware('owner')->name('hpp.index');
    Route::post('hpp', [HppController::class, 'store'])->middleware('owner')->name('hpp.store');
    Route::put('hpp/{hpp}', [HppController::class, 'update'])->middleware('owner')->name('hpp.update');
    Route::delete('hpp/{hpp}', [HppController::class, 'destroy'])->middleware('owner')->name('hpp.destroy');

    // ── LAPORAN ───────────────────────────────────────────────────────────
    Route::get('laporan', [LaporanController::class, 'index'])->middleware('permission:laporan')->name('laporan');
    Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])->middleware('permission:laporan')->name('laporan.pdf');
    Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])->middleware('permission:laporan')->name('laporan.excel');

    // ── MASTER LAYANAN ────────────────────────────────────────────────────
    Route::get('layanans', [LayananController::class, 'index'])->middleware('permission:layanans')->name('layanans.index');
    Route::post('layanans', [LayananController::class, 'store'])->middleware('permission:layanans')->name('layanans.store');
    Route::put('layanans/{layanan}', [LayananController::class, 'update'])->middleware('permission:layanans')->name('layanans.update');
    Route::patch('layanans/{layanan}/toggle-aktif', [LayananController::class, 'toggleAktif'])->middleware('permission:layanans')->name('layanans.toggle');

    // ── KATEGORI LAYANAN ──────────────────────────────────────────────────
    Route::get('kategori-layanans', [KategoriLayananController::class, 'index'])->middleware('permission:layanans')->name('kategori-layanans.index');
    Route::post('kategori-layanans', [KategoriLayananController::class, 'store'])->middleware('permission:layanans')->name('kategori-layanans.store');
    Route::put('kategori-layanans/{kategoriLayanan}', [KategoriLayananController::class, 'update'])->middleware('permission:layanans')->name('kategori-layanans.update');
    Route::patch('kategori-layanans/{kategoriLayanan}/toggle', [KategoriLayananController::class, 'toggle'])->middleware('permission:layanans')->name('kategori-layanans.toggle');

    // ── JENIS BARANG ──────────────────────────────────────────────────────
    Route::get('jenis-barangs', [JenisBarangController::class, 'index'])->middleware('permission:layanans')->name('jenis-barangs.index');
    Route::post('jenis-barangs', [JenisBarangController::class, 'store'])->middleware('permission:layanans')->name('jenis-barangs.store');
    Route::put('jenis-barangs/{jenisBarang}', [JenisBarangController::class, 'update'])->middleware('permission:layanans')->name('jenis-barangs.update');
    Route::patch('jenis-barangs/{jenisBarang}/toggle', [JenisBarangController::class, 'toggle'])->middleware('permission:layanans')->name('jenis-barangs.toggle');

    // ── DAFTAR BAHAN BAKU ──────────────────────────────────────────────────
    Route::get('bahans', [BahanController::class, 'index'])->middleware('permission:stok')->name('bahans.index');
    Route::post('bahans', [BahanController::class, 'store'])->middleware('permission:stok')->name('bahans.store');
    Route::put('bahans/{bahan}', [BahanController::class, 'update'])->middleware('permission:stok')->name('bahans.update');
    Route::patch('bahans/{bahan}/toggle', [BahanController::class, 'toggle'])->middleware('permission:stok')->name('bahans.toggle');

    // ── VOUCHER ───────────────────────────────────────────────────────────
    Route::get('vouchers/cek', [VoucherController::class, 'cek'])->middleware(['permission:vouchers', 'throttle:30,1'])->name('vouchers.cek');
    Route::get('vouchers', [VoucherController::class, 'index'])->middleware('permission:vouchers')->name('vouchers.index');
    Route::post('vouchers', [VoucherController::class, 'store'])->middleware('permission:vouchers')->name('vouchers.store');
    Route::put('vouchers/{voucher}', [VoucherController::class, 'update'])->middleware('permission:vouchers')->name('vouchers.update');
    Route::patch('vouchers/{voucher}/toggle', [VoucherController::class, 'toggleAktif'])->middleware('permission:vouchers')->name('vouchers.toggle');
    Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->middleware('permission:vouchers')->name('vouchers.destroy');

    // ── STOK ──────────────────────────────────────────────────────────────
    Route::get('stok', [StokController::class, 'index'])->middleware('permission:stok')->name('stok.index');
    Route::put('stok/{stok}', [StokController::class, 'update'])->middleware('permission:stok')->name('stok.update');
    Route::post('stok/{stok}/mutasi', [StokController::class, 'mutasi'])->middleware('permission:stok')->name('stok.mutasi');
    Route::get('stok/{stok}/riwayat', [StokController::class, 'riwayat'])->middleware('permission:stok')->name('stok.riwayat');

    // ── REWARD ────────────────────────────────────────────────────────────
    Route::get('rewards/poin', [RewardController::class, 'kelolaPoin'])->middleware('permission:rewards')->name('rewards.poin');
    Route::post('rewards/poin', [RewardController::class, 'tambahPoinManual'])->middleware('permission:rewards')->name('rewards.poin.tambah');
    Route::get('rewards', [RewardController::class, 'index'])->middleware('permission:rewards')->name('rewards.index');
    Route::post('rewards', [RewardController::class, 'store'])->middleware('permission:rewards')->name('rewards.store');
    Route::put('rewards/{reward}', [RewardController::class, 'update'])->middleware('permission:rewards')->name('rewards.update');
    Route::patch('rewards/{reward}/toggle', [RewardController::class, 'toggleAktif'])->middleware('permission:rewards')->name('rewards.toggle');
    Route::delete('rewards/{reward}', [RewardController::class, 'destroy'])->middleware('permission:rewards')->name('rewards.destroy');

    // ── OPERASIONAL ───────────────────────────────────────────────────────
    Route::get('operasional', [OperasionalController::class, 'index'])->middleware('permission:operasional')->name('operasional.index');
    Route::post('operasional', [OperasionalController::class, 'store'])->middleware('permission:operasional')->name('operasional.store');
    Route::delete('operasional/{operasional}', [OperasionalController::class, 'destroy'])->middleware('permission:operasional')->name('operasional.destroy');



    // ── KARYAWAN (owner only) ─────────────────────────────────────────────
    Route::get('karyawans', [KaryawanController::class, 'index'])->middleware('owner')->name('karyawans.index');
    Route::post('karyawans', [KaryawanController::class, 'store'])->middleware('owner')->name('karyawans.store');
    Route::get('karyawans/{karyawan}', [KaryawanController::class, 'show'])->middleware('owner')->name('karyawans.show');
    Route::put('karyawans/{karyawan}', [KaryawanController::class, 'update'])->middleware('owner')->name('karyawans.update');
    Route::patch('karyawans/{karyawan}/toggle-aktif', [KaryawanController::class, 'toggleAktif'])->middleware('owner')->name('karyawans.toggle');
    Route::patch('karyawans/{karyawan}/reset-password', [KaryawanController::class, 'resetPassword'])->middleware('owner')->name('karyawans.password');
    Route::post('karyawans/permissions', [KaryawanController::class, 'savePermissions'])->middleware('owner')->name('karyawans.permissions');
});

require __DIR__.'/auth.php';
