<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    protected $fillable = ['kode', 'nama', 'template'];

    public static function get(string $kode): ?self
    {
        return static::where('kode', $kode)->first();
    }
}
