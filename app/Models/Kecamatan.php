<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'nama',
        'kota_kab_id',
        'nama_ketua_dpc',
        'alamat_sekretariat',
        'telepon',
        'is_active',
    ];

    public function kotaKab()
    {
        return $this->belongsTo(KotaKab::class, 'kota_kab_id');
    }

    public function pagoruan()
    {
        return $this->hasMany(Pagoruan::class, 'kecamatan_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'kecamatan_id');
    }
}
