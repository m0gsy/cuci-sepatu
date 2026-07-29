<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['nama', 'poin_dibutuhkan', 'deskripsi', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];
}
