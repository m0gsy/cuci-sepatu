<?php

namespace App\Models;

use App\Http\Middleware\NormalizePhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pelanggan extends Model
{
    protected $fillable = ['nama', 'no_hp', 'alamat', 'catatan'];
    // poin dan tier dikelola via tambahPoin()/updateTier() — bukan mass-assignable

    // Tiers: reguler < silver < gold < platinum
    const TIER_THRESHOLDS = [
        'platinum' => 5000000,
        'gold' => 2000000,
        'silver' => 500000,
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function poinHistories()
    {
        return $this->hasMany(PoinHistory::class);
    }

    public function getTotalBelanjaPeriodeAttribute(): int
    {
        return $this->orders()
            ->join('pembayarans', 'orders.id', '=', 'pembayarans.order_id')
            ->where('pembayarans.status', 'selesai')
            ->sum('pembayarans.total');
    }

    public function layananFavorit(): ?string
    {
        $fav = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('layanans', 'order_items.layanan_id', '=', 'layanans.id')
            ->where('orders.pelanggan_id', $this->id)
            ->where('orders.status', '!=', 'batal')
            ->select('layanans.nama', DB::raw('SUM(order_items.jumlah_pasang) as total'))
            ->groupBy('layanans.id', 'layanans.nama')
            ->orderByDesc('total')
            ->first();

        return $fav?->nama;
    }

    public function getWaLinkAttribute(): string
    {
        return 'https://wa.me/'.NormalizePhoneNumber::normalize($this->no_hp);
    }

    /** Formatted phone for display: 628xxx → 0812-3456-7890 */
    public function getNoHpDisplayAttribute(): string
    {
        return NormalizePhoneNumber::display($this->no_hp);
    }

    // Nilai rupiah dari poin (1 poin = Rp 100)
    public function nilaiPoin(): int
    {
        return ($this->poin ?? 0) * 100;
    }

    // Tambah poin setelah order
    public function tambahPoin(
        int $poin,
        string $keterangan,
        ?int $orderId = null,
        ?string $eventKey = null
    ): bool {
        if ($poin <= 0) {
            return false;
        }

        return DB::transaction(function () use ($poin, $keterangan, $orderId, $eventKey) {
            $pelanggan = static::lockForUpdate()->findOrFail($this->id);
            if ($eventKey && PoinHistory::where('event_key', $eventKey)->exists()) {
                return false;
            }

            $pelanggan->increment('poin', $poin);
            $pelanggan->poinHistories()->create([
                'tipe' => 'tambah',
                'poin' => $poin,
                'keterangan' => $keterangan,
                'order_id' => $orderId,
                'event_key' => $eventKey,
            ]);
            $this->refresh();

            return true;
        });
    }

    public function hitungTier(): string
    {
        $total = $this->total_belanja_periode;
        foreach (self::TIER_THRESHOLDS as $tier => $min) {
            if ($total >= $min) {
                return $tier;
            }
        }

        return 'reguler';
    }

    public function updateTier(): void
    {
        $this->tier = $this->hitungTier();
        $this->save();
    }

    public function getTierBadgeAttribute(): string
    {
        return match ($this->tier ?? 'reguler') {
            'platinum' => 'bg-purple-50 text-purple-800 ring-1 ring-purple-300',
            'gold' => 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-300',
            'silver' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-300',
            default => 'bg-gray-50 text-gray-600 ring-1 ring-gray-200',
        };
    }

    // Tukar poin reward
    public function tukarPoin(
        int $poin,
        string $keterangan,
        ?int $orderId = null,
        ?string $eventKey = null
    ): bool {
        if ($poin <= 0) {
            return false;
        }

        return DB::transaction(function () use ($poin, $keterangan, $orderId, $eventKey) {
            $pelanggan = static::lockForUpdate()->findOrFail($this->id);
            if ($eventKey && PoinHistory::where('event_key', $eventKey)->exists()) {
                return false;
            }
            if ($pelanggan->poin < $poin) {
                return false;
            }

            $pelanggan->decrement('poin', $poin);
            $pelanggan->poinHistories()->create([
                'tipe' => 'tukar',
                'poin' => $poin,
                'keterangan' => $keterangan,
                'order_id' => $orderId,
                'event_key' => $eventKey,
            ]);
            $this->refresh();

            return true;
        });
    }
}
