<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pagoruan extends Model
{
    use HasFactory;

    protected $table = 'pagoruan';

    protected $fillable = [
        'nama',
        'kecamatan_id',
        'alamat',
        'telepon',
        'nama_pelatih',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}