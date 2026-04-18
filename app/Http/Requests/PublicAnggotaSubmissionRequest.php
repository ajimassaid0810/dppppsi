<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicAnggotaSubmissionRequest extends FormRequest
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
            'nama_lengkap' => $this->nullableString('nama_lengkap'),
            'nomor_identitas' => $this->nullableString('nomor_identitas'),
            'alamat' => $this->nullableString('alamat'),
            'telepon' => $this->nullableString('telepon'),
            'email' => $this->nullableString('email'),
            'golongan_darah' => $this->nullableString('golongan_darah'),
            'jenis_identitas' => $this->nullableString('jenis_identitas'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_identitas' => ['required', Rule::in(array_keys(Anggota::JENIS_IDENTITAS_OPTIONS))],
            'nomor_identitas' => ['required', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'golongan_darah' => ['nullable', Rule::in(array_keys(Anggota::GOLONGAN_DARAH_OPTIONS))],
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
                Rule::exists('kecamatan', 'id')->where(
                    fn ($query) => $query->where('kota_kab_id', $this->input('kota_kab_id'))
                ),
            ],
            'pagoruan_id' => [
                'nullable',
                'integer',
                Rule::exists('pagoruan', 'id')->where(
                    fn ($query) => $query->where('kecamatan_id', $this->input('kecamatan_id'))
                ),
            ],
            'foto' => ['required', 'image', 'max:2048'],
            'kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'dokumen_identitas' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
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
            'telepon' => 'telepon',
            'email' => 'email',
            'golongan_darah' => 'golongan darah',
            'provinsi_id' => 'DPW provinsi',
            'kota_kab_id' => 'DPD kota/kab',
            'kecamatan_id' => 'DPC kecamatan',
            'pagoruan_id' => 'pagoruan',
            'foto' => 'foto',
            'kk' => 'KK',
            'dokumen_identitas' => 'dokumen identitas',
        ];
    }

    public function messages(): array
    {
        return [
            'kota_kab_id.exists' => 'DPD kota/kab tidak sesuai dengan DPW yang dipilih.',
            'kecamatan_id.exists' => 'DPC kecamatan tidak sesuai dengan DPD yang dipilih.',
            'pagoruan_id.exists' => 'Pagoruan tidak sesuai dengan DPC yang dipilih.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'kk.mimes' => 'File KK harus berupa JPG, JPEG, PNG, atau PDF.',
            'kk.max' => 'Ukuran file KK maksimal 4 MB.',
            'dokumen_identitas.mimes' => 'Dokumen identitas harus berupa JPG, JPEG, PNG, atau PDF.',
            'dokumen_identitas.max' => 'Ukuran dokumen identitas maksimal 4 MB.',
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
