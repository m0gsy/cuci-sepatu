<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananRecipe extends Model
{
    protected $table = 'layanan_recipes';
    protected $fillable = ['layanan_id', 'bahan_id', 'jumlah_penggunaan'];
    protected $casts = [
        'jumlah_penggunaan' => 'decimal:2'
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }

    public function getUnitCostAttribute()
    {
        return $this->bahan->harga_satuan ?? 0;
    }

    public function getTotalCostAttribute()
    {
        return $this->jumlah_penggunaan * $this->unit_cost;
    }
}
