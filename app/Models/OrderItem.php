<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'layanan_id', 'jenis_barang_id', 'jumlah_pasang', 'harga_satuan',
        'merek', 'warna', 'kondisi', 'hpp', 'gross_profit', 'gross_margin',
    ];

    protected $casts = [
        'jumlah_pasang' => 'integer',
        'harga_satuan' => 'integer',
        'hpp' => 'integer',
        'gross_profit' => 'integer',
        'gross_margin' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($item) {
            $totalHarga = $item->harga_satuan * $item->jumlah_pasang;
            $item->gross_profit = $totalHarga - $item->hpp;
            $item->gross_margin = $totalHarga > 0 ? round(($item->gross_profit / $totalHarga) * 100, 2) : 0;
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class, 'jenis_barang_id');
    }

    public function getHargaTotalAttribute()
    {
        return $this->harga_satuan * $this->jumlah_pasang;
    }

    public function getHargaTotalFormatAttribute()
    {
        return 'Rp '.number_format($this->harga_total, 0, ',', '.');
    }

    public function getNetSalesAttribute()
    {
        $order = $this->order;
        if (! $order) {
            return $this->harga_total;
        }
        $grossOrder = $order->gross_sales;
        if ($grossOrder === 0) {
            return 0;
        }
        $diskonOrder = ($order->diskon ?? 0) + ($order->diskon_poin ?? 0);
        $fraction = $diskonOrder / $grossOrder;

        return (int) round($this->harga_total * (1 - $fraction));
    }
}
