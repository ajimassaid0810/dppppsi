<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'nama',
        'nama_ketua_dpw',
        'url_ttd_ketua',
        'is_active',
    ];

    public function kotaKab()
    {
        return $this->hasMany(KotaKab::class, 'provinsi_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'provinsi_id');
    }
}
