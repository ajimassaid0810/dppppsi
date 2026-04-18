export interface PublicSite {
  id?: number | null
  site_name: string
  site_tagline?: string | null
  email?: string | null
  telepon?: string | null
  alamat?: string | null
  footer_text?: string | null
  logo_url?: string | null
  favicon_url?: string | null
  seo_title?: string | null
  seo_description?: string | null
  seo_keywords?: string | null
  social_links?: {
    facebook?: string | null
    instagram?: string | null
    youtube?: string | null
    linkedin?: string | null
  } | null
}

export interface LandingHeroItem {
  id?: number | null
  badge?: string | null
  title: string
  highlighted_text?: string | null
  description?: string | null
  primary_cta_label?: string | null
  primary_cta_url?: string | null
  secondary_cta_label?: string | null
  secondary_cta_url?: string | null
  background_image_url?: string | null
  hero_image_url?: string | null
  video_url?: string | null
}

export interface LandingHistoryEntry {
  id: number | null
  title: string
  subtitle?: string | null
  body: string
  icon?: string | null
  image_url?: string | null
}

export interface LandingOrganizationEntry {
  id: number | null
  name: string
  position: string
  group_name?: string | null
  short_bio?: string | null
  document_url?: string | null
  photo_url?: string | null
  media_type?: 'image' | 'pdf' | null
}

export interface LandingActivityEntry {
  id: number | null
  title: string
  summary: string
  event_date?: string | null
  location?: string | null
  detail_url?: string | null
  image_url?: string | null
}

export interface LandingSupportEntry {
  id: number | null
  name: string
  website_url?: string | null
  logo_url?: string | null
}

export interface LandingKtaEntry {
  id: number | null
  title: string
  description?: string | null
  checklist_items?: string[]
  cta_label?: string | null
  cta_url?: string | null
  banner_image_url?: string | null
}

export interface PublicPengajuanRecord {
  id: string
  kode_pengajuan: string
  nama_lengkap: string
  jenis_identitas: string
  nomor_identitas: string
  tanggal_lahir?: string | null
  alamat: string
  telepon: string
  email?: string | null
  golongan_darah?: string | null
  status: string
  catatan_admin?: string | null
  submitted_at?: string | null
  ditinjau_at?: string | null
  dikonversi_at?: string | null
  anggota_id?: string | null
  anggota_public_url?: string | null
  foto_url?: string | null
  kk_url?: string | null
  dokumen_identitas_url?: string | null
  provinsi?: { id: number; nama: string } | null
  kota_kab?: { id: number; nama: string } | null
  kecamatan?: { id: number; nama: string } | null
  pagoruan?: { id: number; nama: string } | null
  reviewer?: { id: string; username: string } | null
}

export function formatPublicDate(value?: string | null, withTime = false) {
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
    ...(withTime
      ? {
          hour: '2-digit',
          minute: '2-digit',
        }
      : {}),
  }).format(date)
}

export function pengajuanStatusLabel(status: string) {
  const labels: Record<string, string> = {
    diajukan: 'Diajukan',
    ditinjau: 'Ditinjau',
    dikonversi: 'Dikonversi',
    ditolak: 'Ditolak',
  }

  return labels[status] ?? status
}

export function pengajuanStatusClass(status: string) {
  const classes: Record<string, string> = {
    diajukan: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    ditinjau: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
    dikonversi: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    ditolak: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
  }

  return classes[status] ?? classes.diajukan
}
