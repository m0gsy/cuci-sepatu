<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $fillable = ['kode', 'nama', 'deskripsi', 'harga_custom', 'harga_tambahan', 'aktif'];
    protected $casts    = ['aktif' => 'boolean', 'harga_custom' => 'boolean'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Harga per layanan yang di-override untuk lokasi ini
    public function layanans()
    {
        return $this->belongsToMany(Layanan::class, 'lokasi_layanan')
                    ->withPivot('harga')
                    ->withTimestamps();
    }

    // Ambil harga spesifik untuk layanan tertentu (null = tidak ada override)
    public function hargaUntukLayanan(int $layananId): ?int
    {
        $item = $this->layanans->firstWhere('id', $layananId);
        return $item ? $item->pivot->harga : null;
    }

    public function getOrderAktifCountAttribute(): int
    {
        return $this->orders()->whereIn('status', ['antri', 'proses'])->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        $count = $this->order_aktif_count;
        if ($count === 0) return 'bg-green-50 text-green-700 ring-1 ring-green-200';
        if ($count <= 3)  return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
        return 'bg-red-50 text-red-700 ring-1 ring-red-200';
    }

    // Harga efektif: cek per-layanan dulu, lalu fallback ke harga_tambahan
    public function hitungHarga(int $hargaLayanan, ?int $layananId = null): int
    {
        if ($layananId !== null) {
            $hargaSpesifik = $this->hargaUntukLayanan($layananId);
            if ($hargaSpesifik !== null) return $hargaSpesifik;
        }
        if (!$this->harga_custom) return $hargaLayanan;
        return $hargaLayanan + $this->harga_tambahan;
    }

    public function getHargaTambahanFormatAttribute(): string
    {
        if ($this->harga_tambahan === 0) return 'Sama';
        $prefix = $this->harga_tambahan > 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format($this->harga_tambahan, 0, ',', '.');
    }
}
