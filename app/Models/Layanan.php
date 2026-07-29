<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $fillable = ['kategori_layanan_id', 'nama', 'harga', 'estimasi_nilai', 'estimasi_satuan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_layanan_id');
    }

    public function recipes()
    {
        return $this->hasMany(LayananRecipe::class, 'layanan_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'layanan_id');
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp '.number_format($this->harga, 0, ',', '.');
    }

    // Total HPP dari recipe components
    public function getTotalHppAttribute(): int
    {
        return (int) round($this->recipes->sum(function ($recipe) {
            return $recipe->jumlah_penggunaan * ($recipe->bahan->harga_satuan ?? 0);
        }));
    }

    // Gross margin standar per layanan
    public function getGrossMarginAttribute(): float
    {
        if ($this->harga === 0) {
            return 0;
        }

        return round((($this->harga - $this->total_hpp) / $this->harga) * 100, 1);
    }
}
