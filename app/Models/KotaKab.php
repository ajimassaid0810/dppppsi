<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KotaKab extends Model
{
    use HasFactory;

    protected $table = 'kota_kab';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = ['id','nama', 'provinsi_id'];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kota_kab_id');
    }
}
