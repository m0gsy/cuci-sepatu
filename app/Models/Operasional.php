<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    protected $fillable = ['user_id', 'nama', 'kategori', 'jumlah', 'tanggal', 'catatan'];

    protected $casts = ['tanggal' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'bahan' => 'Bahan',
            'utilitas' => 'Utilitas',
            'gaji' => 'Gaji',
            'sewa' => 'Sewa',
            'peralatan' => 'Peralatan',
            default => 'Lainnya',
        };
    }

    public function getKategoriBadgeAttribute(): string
    {
        return match ($this->kategori) {
            'bahan' => 'bg-blue-50 text-blue-700',
            'utilitas' => 'bg-amber-50 text-amber-700',
            'gaji' => 'bg-purple-50 text-purple-700',
            'sewa' => 'bg-orange-50 text-orange-700',
            'peralatan' => 'bg-teal-50 text-teal-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
