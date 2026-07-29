<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    protected $table = 'jenis_barangs';

    protected $fillable = ['nama', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];
}
