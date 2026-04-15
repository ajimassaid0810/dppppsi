<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Anggota extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'anggota';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'alamat',
        'kelurahan_id',
        'telepon',
        'foto',
        'tanggal_gabung',
        'masa_berlaku',
        'perguruan',
        'golongan_darah',
        'barcode_data',
        'cabang_id',
    ];

    /**
     * Generate UUID for primary key if not set.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }
}