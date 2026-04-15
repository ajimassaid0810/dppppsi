<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'nama',
        'nama_ketua_dpw',
        'url_ttd_ketua'
    ];

    public function kotaKab()
    {
        return $this->hasMany(KotaKab::class, 'provinsi_id');
    }
}