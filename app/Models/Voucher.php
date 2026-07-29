<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'kode', 'tipe', 'nilai', 'expired_at', 'min_transaksi',
        'kuota', 'aktif', 'deskripsi',
    ];
    // terpakai dikelola via increment() saja — bukan mass-assignable

    protected $casts = [
        'expired_at' => 'date',
        'aktif' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hitungDiskon(int $total): int
    {
        if ($this->tipe === 'persen') {
            return min($total, (int) round($total * min($this->nilai, 100) / 100));
        }

        return min($this->nilai, $total);
    }

    public function masihBerlaku(int $total = 0): bool
    {
        if (! $this->aktif) {
            return false;
        }
        if ($this->expired_at && $this->expired_at->isBefore(today())) {
            return false;
        }
        if ($this->kuota !== null && $this->terpakai >= $this->kuota) {
            return false;
        }
        if ($total > 0 && $total < $this->min_transaksi) {
            return false;
        }

        return true;
    }

    public function getNilaiFormatAttribute(): string
    {
        return $this->tipe === 'persen'
            ? "{$this->nilai}%"
            : 'Rp '.number_format($this->nilai, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        if (! $this->aktif) {
            return 'bg-gray-100 text-gray-500';
        }
        if ($this->expired_at && $this->expired_at->isBefore(today())) {
            return 'bg-red-50 text-red-700';
        }
        if ($this->kuota !== null && $this->terpakai >= $this->kuota) {
            return 'bg-orange-50 text-orange-700';
        }

        return 'bg-green-50 text-green-700';
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->aktif) {
            return 'Nonaktif';
        }
        if ($this->expired_at && $this->expired_at->isBefore(today())) {
            return 'Expired';
        }
        if ($this->kuota !== null && $this->terpakai >= $this->kuota) {
            return 'Habis';
        }

        return 'Aktif';
    }
}
