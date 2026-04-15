<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = ['id','nama', 'kota_kab_id'];

    public function kotaKab()
    {
        return $this->belongsTo(KotaKab::class, 'kota_kab_id');
    }

    public function kelurahan()
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id');
    }
}
