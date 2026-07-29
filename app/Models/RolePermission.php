<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RolePermission extends Model
{
    protected $fillable = ['role', 'permission'];

    public static function allPermissions(): array
    {
        return [
            ['key' => 'orders.manage', 'label' => 'Input / edit order & pembayaran'],
            ['key' => 'pelanggan',     'label' => 'Data pelanggan'],
            ['key' => 'lokasi',        'label' => 'Lokasi sepatu'],
            ['key' => 'laporan',       'label' => 'Laporan penjualan'],
            ['key' => 'hpp',           'label' => 'Profit / Loss & HPP'],
            ['key' => 'layanans',      'label' => 'Master layanan & harga'],
            ['key' => 'vouchers',      'label' => 'Voucher'],
            ['key' => 'rewards',       'label' => 'Reward & Poin'],
            ['key' => 'stok',          'label' => 'Stok bahan'],
            ['key' => 'operasional',   'label' => 'Operasional'],
            ['key' => 'wa_template',   'label' => 'Template WhatsApp'],
        ];
    }

    public static function forRole(string $role): array
    {
        return Cache::remember("role_perms_{$role}", 300, function () use ($role) {
            return static::where('role', $role)->pluck('permission')->toArray();
        });
    }

    public static function bustCache(string $role): void
    {
        Cache::forget("role_perms_{$role}");
    }
}
