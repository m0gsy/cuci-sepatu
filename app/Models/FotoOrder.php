<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoOrder extends Model
{
    protected $fillable = ['order_id', 'tipe', 'path', 'keterangan'];

    public function order() { return $this->belongsTo(Order::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getTipeLabelAttribute(): string
    {
        return match($this->tipe) {
            'before' => 'Sebelum',
            'during' => 'Proses',
            'after'  => 'Sesudah',
            default  => $this->tipe,
        };
    }
}
