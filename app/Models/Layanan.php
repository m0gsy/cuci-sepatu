<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $fillable = ['nama', 'harga', 'estimasi_hari', 'aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hppKomponen()
    {
        return $this->hasMany(HppLayanan::class);
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    // Total HPP dari semua komponen
    public function getTotalHppAttribute(): int
    {
        return $this->hppKomponen->sum('biaya');
    }

    // Gross margin standar per layanan
    public function getGrossMarginAttribute(): float
    {
        if ($this->harga === 0) return 0;
        return round((($this->harga - $this->total_hpp) / $this->harga) * 100, 1);
    }
}
