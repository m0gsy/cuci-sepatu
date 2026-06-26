<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    protected $fillable = ['nama', 'satuan', 'stok_saat_ini', 'stok_minimum', 'harga_satuan', 'catatan'];

    public function mutasis()
    {
        return $this->hasMany(StokMutasi::class);
    }

    public function getStatusStokAttribute(): string
    {
        if ($this->stok_saat_ini <= 0) return 'habis';
        if ($this->stok_saat_ini <= $this->stok_minimum) return 'menipis';
        return 'aman';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status_stok) {
            'habis'   => 'bg-red-50 text-red-700 ring-1 ring-red-200',
            'menipis' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            default   => 'bg-green-50 text-green-700 ring-1 ring-green-200',
        };
    }

    public function getNilaiStokAttribute(): int
    {
        return (int)($this->stok_saat_ini * $this->harga_satuan);
    }
}
