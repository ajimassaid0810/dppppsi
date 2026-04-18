export interface SelectOption {
  value: string
  label: string
}

export interface ProvinsiItem {
  id: number
  kode?: string | null
  nama: string
  nama_ketua_dpw?: string | null
  url_ttd_ketua?: string | null
  ttd_public_url?: string | null
}

export interface KotaKabItem {
  id: number
  kode?: string | null
  nama: string
  provinsi?: ProvinsiItem | null
}

export interface KecamatanItem {
  id: number
  kode?: string | null
  nama: string
}

export interface PagoruanItem {
  id: number
  kode?: string | null
  nama: string
}

export interface AnggotaFormOptions {
  provinsi: ProvinsiItem[]
  jenis_identitas: SelectOption[]
  golongan_darah: SelectOption[]
  status_pengajuan: SelectOption[]
}

export interface AnggotaRecord {
  id: string
  nomor_anggota: string
  nomor_urut_dpd: number
  nama_lengkap: string
  jenis_identitas: string
  nomor_identitas: string | null
  tanggal_lahir: string | null
  alamat: string | null
  telepon: string | null
  golongan_darah: string | null
  tanggal_gabung: string | null
  masa_berlaku_sampai: string | null
  status_pengajuan: string
  catatan_verifikasi: string | null
  foto_path: string | null
  foto_url?: string | null
  kk_path: string | null
  kk_url?: string | null
  dokumen_identitas_path: string | null
  dokumen_identitas_url?: string | null
  kota_kab_id: number
  kecamatan_id: number | null
  pagoruan_id: number | null
  kota_kab?: KotaKabItem | null
  kecamatan?: KecamatanItem | null
  pagoruan?: PagoruanItem | null
}

export function statusLabel(status: string) {
  const labels: Record<string, string> = {
    Pengajuan_anggota: 'Pengajuan Anggota',
    draft_dpd: 'Draft DPD',
    diajukan_ke_dpw: 'Diajukan ke DPW',
    diverifikasi_dpw: 'Diverifikasi DPW',
    diajukan_ke_dpp: 'Diajukan ke DPP',
    disetujui_dpp: 'Disetujui DPP',
    ditolak: 'Ditolak',
  }

  return labels[status] ?? status
}

export function statusClass(status: string) {
  const classes: Record<string, string> = {
    Pengajuan_anggota: 'bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300',
    draft_dpd: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    diajukan_ke_dpw: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    diverifikasi_dpw: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    diajukan_ke_dpp: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
    disetujui_dpp: 'bg-[#eef8ef] text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]',
    ditolak: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
  }

  return classes[status] ?? classes.draft_dpd
}

export function formatDisplayDate(value?: string | null) {
  if (!value) {
    return '-'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

export function formatFileSize(bytes: number) {
  if (bytes < 1024) {
    return `${bytes} B`
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}
