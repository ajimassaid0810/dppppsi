<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Str;
use App\Models\Provinsi;
use App\Models\Role;
use App\Models\KotaKab;
use App\Models\Kecamatan;
use App\Models\Pagoruan;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id',
        'provinsi_id',
        'kota_kab_id',
        'kecamatan_id',
        'pagoruan_id',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    public function pagoruan()
    {
        return $this->belongsTo(Pagoruan::class, 'pagoruan_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kotaKab()
    {
        return $this->belongsTo(KotaKab::class, 'kota_kab_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->relationLoaded('role')
            ? $this->role?->name === $roleName
            : $this->role()->where('name', $roleName)->exists();
    }
}
