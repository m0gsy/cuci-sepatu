<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoinHistory extends Model
{
    protected $fillable = ['pelanggan_id', 'order_id', 'tipe', 'poin', 'keterangan'];

    public function pelanggan() { return $this->belongsTo(Pelanggan::class); }
    public function order()     { return $this->belongsTo(Order::class); }
}
