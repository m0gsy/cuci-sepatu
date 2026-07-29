<?php

namespace App\Models;

use App\Http\Middleware\NormalizePhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    // Canonical lifecycle statuses, including the cancellation terminal.
    const STATUSES = [
        'draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai', 'batal',
    ];

    protected $fillable = [
        // no_order dan token_publik di-generate otomatis di boot(), tidak boleh di-set manual
        'user_id', 'pelanggan_id', 'lokasi_id',
        'nama_pelanggan', 'no_hp', 'catatan', 'catatan_lokasi',
        'voucher_id', 'diskon', 'poin_digunakan', 'diskon_poin',
        'idempotency_key', 'poin_diberikan_pada', 'voucher_dikembalikan_pada',
        'jumlah_pasang', 'hpp', 'layanan_id', 'jenis_sepatu',
        'status', 'estimasi_selesai', 'selesai_pada',
    ];

    protected $casts = [
        'estimasi_selesai' => 'datetime',
        'selesai_pada' => 'datetime',
        'poin_diberikan_pada' => 'datetime',
        'voucher_dikembalikan_pada' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->no_order = 'ORD-'.date('Ymd').'-'.Str::upper(Str::random(6));
            $order->token_publik = Str::random(32);
            if (! isset($order->status)) {
                $order->status = 'draft';
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Fallback relation for backward compatibility
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    /** Formatted phone for display: 628xxx → 0812-3456-7890 */
    public function getNoHpDisplayAttribute(): string
    {
        return NormalizePhoneNumber::display($this->no_hp);
    }

    public function getHargaEfektifAttribute(): int
    {
        if ($this->items->isNotEmpty()) {
            return (int) $this->items->first()->harga_satuan;
        }
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
        if ($this->items->isNotEmpty()) {
            return (int) $this->items->sum(fn ($i) => $i->harga_satuan * $i->jumlah_pasang);
        }

        return $this->harga_efektif * ($this->attributes['jumlah_pasang'] ?? 1);
    }

    public function getNetSalesAttribute(): int
    {
        return max(0, $this->gross_sales - ($this->diskon ?? 0) - ($this->diskon_poin ?? 0));
    }

    public function getHppAttribute(): int
    {
        if ($this->items->isNotEmpty()) {
            return (int) $this->items->sum('hpp');
        }

        return $this->attributes['hpp'] ?? 0;
    }

    public function getJumlahPasangAttribute(): int
    {
        if ($this->items->isNotEmpty()) {
            return (int) $this->items->sum('jumlah_pasang');
        }

        return $this->attributes['jumlah_pasang'] ?? 0;
    }

    public function getGrossProfitAttribute(): int
    {
        return $this->net_sales - $this->hpp;
    }

    public function getGrossMarginAttribute(): float
    {
        if ($this->net_sales === 0) {
            return 0;
        }

        return round(($this->gross_profit / $this->net_sales) * 100, 2);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['label' => 'Draft',               'class' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
            'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200'],
            'diproses' => ['label' => 'Diproses',            'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'],
            'siap_diambil' => ['label' => 'Siap Diambil',        'class' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'],
            'selesai' => ['label' => 'Selesai',             'class' => 'bg-green-50 text-green-700 ring-1 ring-green-200'],
            'batal' => ['label' => 'Batal',               'class' => 'bg-red-50 text-red-700 ring-1 ring-red-200'],
            default => ['label' => $this->status,         'class' => ''],
        };
    }

    public function getStatusBerikutAttribute(): ?string
    {
        return match ($this->status) {
            'draft' => 'menunggu_pembayaran',
            'menunggu_pembayaran' => 'diproses',
            'diproses' => 'siap_diambil',
            'siap_diambil' => 'selesai',
            default => null,
        };
    }

    public function getTerlambatAttribute(): bool
    {
        $aktif = ['draft', 'menunggu_pembayaran', 'diproses'];

        return in_array($this->status, $aktif) && ($this->estimasi_selesai?->isPast() ?? false);
    }

    public function getPoinAttribute(): int
    {
        return intdiv($this->pembayaran?->total ?? $this->net_sales, 10000);
    }

    public function isSudahSelesai(): bool
    {
        return in_array($this->status, ['siap_diambil', 'selesai', 'batal']);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, match ($this->status) {
            'draft' => ['menunggu_pembayaran', 'diproses', 'batal'],
            'menunggu_pembayaran' => ['diproses', 'batal'],
            'diproses' => ['siap_diambil'],
            'siap_diambil' => ['selesai'],
            default => [],
        }, true);
    }
}
