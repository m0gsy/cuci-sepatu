<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $fillable = [
        'stok_id', 'user_id', 'tipe', 'jumlah',
        'stok_sebelum', 'stok_sesudah', 'keterangan',
    ];
    // stok_sebelum/sesudah ditulis sekali saat create — model ini tidak punya update route

    public function stok() { return $this->belongsTo(Stok::class); }
    public function user() { return $this->belongsTo(User::class); }
}
