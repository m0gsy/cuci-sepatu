<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppLayanan extends Model
{
    protected $table    = 'hpp_layanans';
    protected $fillable = ['layanan_id', 'komponen', 'biaya'];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }
}
