<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = ['order_id', 'total', 'metode', 'status', 'dibayar_pada'];
    protected $casts    = ['dibayar_pada' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }

    public function getTotalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
