<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $table = 'bahans';

    protected $fillable = ['nama', 'satuan', 'harga_beli', 'isi_kemasan', 'harga_satuan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
        'harga_beli' => 'integer',
        'isi_kemasan' => 'integer',
        'harga_satuan' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($bahan) {
            $isi = $bahan->isi_kemasan ?: 1;
            $bahan->harga_satuan = $bahan->harga_beli / $isi;
        });
    }

    public function stok()
    {
        return $this->hasOne(Stok::class, 'bahan_id');
    }
}
