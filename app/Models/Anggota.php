<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Anggota extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'anggota';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nomor_anggota',
        'nomor_urut_dpd',
        'kota_kab_id',
        'kecamatan_id',
        'pagoruan_id',
        'user_id',
        'dibuat_oleh',
        'diverifikasi_dpw_oleh',
        'disetujui_dpp_oleh',
        'nama_lengkap',
        'jenis_identitas',
        'nomor_identitas',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'foto_path',
        'kk_path',
        'dokumen_identitas_path',
        'tanggal_gabung',
        'masa_berlaku_sampai',
        'golongan_darah',
        'status_pengajuan',
        'catatan_verifikasi',
        'diajukan_ke_dpw_at',
        'diverifikasi_dpw_at',
        'diajukan_ke_dpp_at',
        'disetujui_dpp_at',
        'ditolak_at',
        'barcode_data',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_gabung' => 'date',
            'masa_berlaku_sampai' => 'date',
            'diajukan_ke_dpw_at' => 'datetime',
            'diverifikasi_dpw_at' => 'datetime',
            'diajukan_ke_dpp_at' => 'datetime',
            'disetujui_dpp_at' => 'datetime',
            'ditolak_at' => 'datetime',
        ];
    }

    public function kotaKab()
    {
        return $this->belongsTo(KotaKab::class, 'kota_kab_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function pagoruan()
    {
        return $this->belongsTo(Pagoruan::class, 'pagoruan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diverifikasiDpwOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_dpw_oleh');
    }

    public function disetujuiDppOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_dpp_oleh');
    }
}
