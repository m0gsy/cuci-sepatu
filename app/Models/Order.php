<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    // 7-step PRD tracking flow
    const STATUSES = [
        'diterima', 'inspeksi', 'dicuci', 'kering', 'finishing', 'siap_diambil', 'selesai',
    ];

    // Legacy statuses (backward compat)
    const LEGACY_STATUSES = ['antri', 'proses', 'diambil'];

    protected $fillable = [
        // no_order dan token_publik di-generate otomatis di boot(), tidak boleh di-set manual
        'user_id', 'pelanggan_id', 'lokasi_id',
        'nama_pelanggan', 'no_hp', 'layanan_id', 'jenis_sepatu', 'merek', 'warna', 'kondisi',
        'jumlah_pasang', 'harga_satuan', 'catatan', 'catatan_lokasi',
        'voucher_id', 'diskon',
        'status', 'estimasi_selesai', 'selesai_pada', 'hpp',
    ];

    protected $casts = [
        'estimasi_selesai' => 'date',
        'selesai_pada'     => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            // Lock-based atomic count to prevent duplicate no_order on concurrent creates
            $urutan = DB::transaction(function () {
                return static::whereDate('created_at', today())->lockForUpdate()->count() + 1;
            });
            $order->no_order     = 'ORD-' . date('Ymd') . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
            $order->token_publik = Str::random(32);
            if (!isset($order->status)) {
                $order->status = 'diterima';
            }
            if (empty($order->hpp) && $order->layanan_id) {
                $totalHpp   = HppLayanan::where('layanan_id', $order->layanan_id)->sum('biaya');
                $order->hpp = $totalHpp * ($order->jumlah_pasang ?? 1);
            }
        });
    }

    public function layanan()    { return $this->belongsTo(Layanan::class); }
    public function pembayaran() { return $this->hasOne(Pembayaran::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function pelanggan()  { return $this->belongsTo(Pelanggan::class); }
    public function lokasi()     { return $this->belongsTo(Lokasi::class); }
    public function voucher()    { return $this->belongsTo(Voucher::class); }
    public function review()     { return $this->hasOne(Review::class); }

    public function getHargaEfektifAttribute(): int
    {
        if ($this->harga_satuan !== null) {
            return $this->harga_satuan;
        }
        // backward compat: order lama sebelum kolom harga_satuan ada
        $hargaLayanan = $this->layanan->harga ?? 0;
        if ($this->lokasi) {
            return $this->lokasi->hitungHarga($hargaLayanan, $this->layanan_id);
        }
        return $hargaLayanan;
    }

    public function getGrossSalesAttribute(): int
    {
        return $this->harga_efektif * $this->jumlah_pasang;
    }

    public function getNetSalesAttribute(): int
    {
        return $this->pembayaran?->total ?? $this->gross_sales;
    }

    public function getGrossProfitAttribute(): int
    {
        return $this->net_sales - $this->hpp;
    }

    public function getGrossMarginAttribute(): float
    {
        if ($this->net_sales === 0) return 0;
        return round(($this->gross_profit / $this->net_sales) * 100, 2);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'diterima'    => ['label' => 'Diterima',    'class' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
            'inspeksi'    => ['label' => 'Inspeksi',    'class' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200'],
            'dicuci'      => ['label' => 'Dicuci',      'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'],
            'kering'      => ['label' => 'Pengeringan', 'class' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200'],
            'finishing'   => ['label' => 'Finishing',   'class' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200'],
            'siap_diambil'=> ['label' => 'Siap Diambil','class' => 'bg-green-50 text-green-700 ring-1 ring-green-200'],
            'selesai'     => ['label' => 'Diambil',     'class' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'],
            // legacy
            'antri'       => ['label' => 'Antri',       'class' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200'],
            'proses'      => ['label' => 'Proses',      'class' => 'bg-blue-50 text-blue-800 ring-1 ring-blue-200'],
            'diambil'     => ['label' => 'Diambil',     'class' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'],
            default       => ['label' => $this->status, 'class' => ''],
        };
    }

    public function getStatusBerikutAttribute(): ?string
    {
        $flow = self::STATUSES;
        $idx  = array_search($this->status, $flow);
        if ($idx === false) {
            // legacy mapping
            return match($this->status) {
                'antri'   => 'inspeksi',
                'proses'  => 'finishing',
                default   => null,
            };
        }
        return isset($flow[$idx + 1]) ? $flow[$idx + 1] : null;
    }

    public function getTerlambatAttribute(): bool
    {
        $aktif = ['diterima', 'inspeksi', 'dicuci', 'kering', 'finishing', 'antri', 'proses'];
        return in_array($this->status, $aktif) && ($this->estimasi_selesai?->isPast() ?? false);
    }

    public function getPoinAttribute(): int
    {
        return intdiv($this->pembayaran?->total ?? 0, 10000);
    }

    public function isSudahSelesai(): bool
    {
        return in_array($this->status, ['siap_diambil', 'selesai', 'diambil']);
    }
}
