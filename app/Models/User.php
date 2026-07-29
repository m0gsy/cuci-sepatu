<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'no_hp', 'alamat', 'aktif', 'last_login'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'last_login' => 'datetime', 'aktif' => 'boolean', 'password' => 'hashed'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'owner';
    }

    public function isCleaner(): bool
    {
        return $this->role === 'cleaner';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return in_array($permission, RolePermission::forRole($this->role));
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'Owner','admin' => 'Admin','cleaner' => 'Cleaner',default => $this->role
        };
    }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'bg-purple-50 text-purple-800 ring-1 ring-purple-200','admin' => 'bg-blue-50 text-blue-800 ring-1 ring-blue-200','cleaner' => 'bg-teal-50 text-teal-800 ring-1 ring-teal-200',default => 'bg-gray-100 text-gray-600'
        };
    }

    public function getInisialAttribute(): string
    {
        $p = explode(' ', $this->name);

        return count($p) >= 2 ? strtoupper(substr($p[0], 0, 1).substr($p[1], 0, 1)) : strtoupper(substr($this->name, 0, 2));
    }
}
