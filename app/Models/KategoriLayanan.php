<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriLayanan extends Model
{
    protected $table = 'kategori_layanans';

    protected $fillable = ['nama', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function layanans()
    {
        return $this->hasMany(Layanan::class, 'kategori_layanan_id');
    }
}
