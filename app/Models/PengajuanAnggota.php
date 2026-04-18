<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PengajuanAnggota extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_OPTIONS = [
        'diajukan' => 'Diajukan',
        'ditinjau' => 'Ditinjau',
        'dikonversi' => 'Dikonversi',
        'ditolak' => 'Ditolak',
    ];

    protected $table = 'pengajuan_anggota';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pengajuan',
        'nama_lengkap',
        'jenis_identitas',
        'nomor_identitas',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'email',
        'golongan_darah',
        'provinsi_id',
        'kota_kab_id',
        'kecamatan_id',
        'pagoruan_id',
        'foto_path',
        'kk_path',
        'dokumen_identitas_path',
        'status',
        'catatan_admin',
        'anggota_id',
        'ditinjau_oleh',
        'ditinjau_at',
        'dikonversi_at',
        'submitted_ip',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'ditinjau_at' => 'datetime',
            'dikonversi_at' => 'datetime',
        ];
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

    public function pagoruan()
    {
        return $this->belongsTo(Pagoruan::class, 'pagoruan_id');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
