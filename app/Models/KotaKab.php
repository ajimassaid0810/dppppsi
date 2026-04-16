<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KotaKab extends Model
{
    use HasFactory;

    protected $table = 'kota_kab';
    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'nama',
        'provinsi_id',
        'nama_ketua_dpd',
        'url_ttd_ketua',
        'is_active',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kota_kab_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'kota_kab_id');
    }
}
