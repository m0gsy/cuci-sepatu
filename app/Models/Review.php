<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['order_id', 'pelanggan_id', 'rating', 'ulasan'];

    public function order()     { return $this->belongsTo(Order::class); }
    public function pelanggan() { return $this->belongsTo(Pelanggan::class); }

    public function getBintangAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
