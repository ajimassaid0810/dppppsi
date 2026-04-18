<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provinsi_id' => $this->nullableInteger('provinsi_id'),
            'kota_kab_id' => $this->nullableInteger('kota_kab_id'),
            'kecamatan_id' => $this->nullableInteger('kecamatan_id'),
            'pagoruan_id' => $this->nullableInteger('pagoruan_id'),
            'golongan_darah' => $this->nullableString('golongan_darah'),
            'status_pengajuan' => $this->nullableString('status_pengajuan') ?? 'draft_dpd',
            'nomor_identitas' => $this->nullableString('nomor_identitas'),
            'telepon' => $this->nullableString('telepon'),
            'alamat' => $this->nullableString('alamat'),
            'catatan_verifikasi' => $this->nullableString('catatan_verifikasi'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_identitas' => ['required', Rule::in(array_keys(Anggota::JENIS_IDENTITAS_OPTIONS))],
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'alamat' => ['nullable', 'string'],
            'provinsi_id' => ['required', 'integer', 'exists:provinsi,id'],
            'kota_kab_id' => [
                'required',
                'integer',
                Rule::exists('kota_kab', 'id')->where(
                    fn ($query) => $query->where('provinsi_id', $this->input('provinsi_id'))
                ),
            ],
            'kecamatan_id' => [
                'nullable',
                'integer',
                Rule::exists('kecamatan', 'id')->where(fn ($query) => $query->where('kota_kab_id', $this->input('kota_kab_id'))),
            ],
            'pagoruan_id' => [
                'nullable',
                'integer',
                Rule::exists('pagoruan', 'id')->where(fn ($query) => $query->where('kecamatan_id', $this->input('kecamatan_id'))),
            ],
            'telepon' => ['nullable', 'string', 'max:50'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'dokumen_identitas' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'golongan_darah' => ['nullable', Rule::in(array_keys(Anggota::GOLONGAN_DARAH_OPTIONS))],
            'tanggal_gabung' => ['nullable', 'date', 'after_or_equal:tanggal_lahir'],
            'masa_berlaku_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_gabung'],
            'status_pengajuan' => ['required', Rule::in(array_keys(Anggota::STATUS_PENGAJUAN_OPTIONS))],
            'catatan_verifikasi' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'jenis_identitas' => 'jenis identitas',
            'nomor_identitas' => 'nomor identitas',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'provinsi_id' => 'provinsi',
            'kota_kab_id' => 'DPD kota/kab',
            'kecamatan_id' => 'DPC kecamatan',
            'pagoruan_id' => 'pagoruan',
            'telepon' => 'telepon',
            'foto' => 'foto',
            'kk' => 'KK',
            'dokumen_identitas' => 'dokumen identitas',
            'golongan_darah' => 'golongan darah',
            'tanggal_gabung' => 'tanggal gabung',
            'masa_berlaku_sampai' => 'masa berlaku sampai',
            'status_pengajuan' => 'status pengajuan',
            'catatan_verifikasi' => 'catatan verifikasi',
        ];
    }

    public function messages(): array
    {
        return [
            'provinsi_id.required' => 'DPW provinsi wajib dipilih.',
            'provinsi_id.exists' => 'DPW provinsi yang dipilih tidak valid.',
            'kota_kab_id.required' => 'DPD kota/kab wajib dipilih.',
            'kota_kab_id.exists' => 'DPD kota/kab tidak sesuai dengan DPW yang dipilih.',
            'kecamatan_id.exists' => 'DPC kecamatan tidak sesuai dengan DPD yang dipilih.',
            'pagoruan_id.exists' => 'Pagoruan tidak sesuai dengan DPC yang dipilih.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'kk.mimes' => 'File KK harus berupa JPG, JPEG, PNG, atau PDF.',
            'kk.max' => 'Ukuran file KK maksimal 4 MB.',
            'dokumen_identitas.mimes' => 'Dokumen identitas harus berupa JPG, JPEG, PNG, atau PDF.',
            'dokumen_identitas.max' => 'Ukuran dokumen identitas maksimal 4 MB.',
            'tanggal_gabung.after_or_equal' => 'Tanggal gabung tidak boleh sebelum tanggal lahir.',
            'masa_berlaku_sampai.after_or_equal' => 'Masa berlaku harus sama atau setelah tanggal gabung.',
        ];
    }

    private function nullableInteger(string $key): ?int
    {
        $value = $this->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
