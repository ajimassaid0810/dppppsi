<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  anggota: {
    nomor_anggota: string
    nama_lengkap: string
    status_pengajuan: string
    nomor_identitas: string | null
    telepon: string | null
    foto_path: string | null
    foto_url?: string | null
    masa_berlaku_sampai: string | null
    kota_kab?: {
      nama: string
      provinsi?: {
        nama: string
      } | null
    } | null
    kecamatan?: {
      nama: string
    } | null
    pagoruan?: {
      nama: string
    } | null
  }
}>()

function statusLabel(status: string) {
  const labels: Record<string, string> = {
    draft_dpd: 'Draft DPD',
    diajukan_ke_dpw: 'Diajukan ke DPW',
    diverifikasi_dpw: 'Diverifikasi DPW',
    diajukan_ke_dpp: 'Diajukan ke DPP',
    disetujui_dpp: 'Disetujui DPP',
    ditolak: 'Ditolak',
  }

  return labels[status] ?? status
}

function statusClass(status: string) {
  const classes: Record<string, string> = {
    draft_dpd: 'bg-slate-100 text-slate-700',
    diajukan_ke_dpw: 'bg-amber-100 text-amber-700',
    diverifikasi_dpw: 'bg-emerald-100 text-emerald-700',
    diajukan_ke_dpp: 'bg-blue-100 text-blue-700',
    disetujui_dpp: 'bg-[#eef8ef] text-[#0b6b31]',
    ditolak: 'bg-red-100 text-red-700',
  }

  return classes[status] ?? classes.draft_dpd
}
</script>

<template>
  <Head :title="`Verifikasi Anggota - ${anggota.nama_lengkap}`" />

  <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.18),_transparent_30%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.24),_transparent_22%),linear-gradient(180deg,_#f8fbf5_0%,_#ffffff_45%,_#eff8ef_100%)] p-6 text-slate-900">
    <div class="mx-auto max-w-5xl">
      <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-[0_30px_80px_-45px_rgba(11,107,49,0.45)] backdrop-blur md:p-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#0b6b31]">Verifikasi Publik PPSI</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight md:text-4xl">Data keanggotaan ditemukan di sistem</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
              Halaman ini dipakai untuk mengecek keaslian kartu anggota. Informasi yang ditampilkan dibatasi hanya untuk kebutuhan verifikasi.
            </p>
          </div>
          <div :class="statusClass(anggota.status_pengajuan)" class="inline-flex rounded-full px-4 py-2 text-sm font-semibold">
            {{ statusLabel(anggota.status_pengajuan) }}
          </div>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-[220px_1fr]">
          <div>
            <img
              v-if="anggota.foto_url"
              :src="anggota.foto_url"
              class="h-[280px] w-full rounded-[1.5rem] object-cover shadow-sm"
            />
            <div v-else class="flex h-[280px] w-full items-center justify-center rounded-[1.5rem] bg-slate-100 text-slate-400">
              Foto tidak tersedia
            </div>
          </div>

          <div class="space-y-5">
            <div class="rounded-[1.5rem] border border-[#d7ebd9] bg-[#f7fbf4] p-5">
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31]">Identitas Utama</p>
              <h2 class="mt-3 text-3xl font-semibold">{{ anggota.nama_lengkap }}</h2>
              <p class="mt-2 font-mono text-lg font-semibold text-[#0b6b31]">{{ anggota.nomor_anggota }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="rounded-3xl border border-slate-200 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Wilayah</p>
                <div class="mt-3 space-y-2 text-sm leading-6">
                  <p><strong>DPW:</strong> {{ anggota.kota_kab?.provinsi?.nama || '-' }}</p>
                  <p><strong>DPD:</strong> {{ anggota.kota_kab?.nama || '-' }}</p>
                  <p><strong>DPC:</strong> {{ anggota.kecamatan?.nama || '-' }}</p>
                  <p><strong>Pagoruan:</strong> {{ anggota.pagoruan?.nama || '-' }}</p>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Status Verifikasi</p>
                <div class="mt-3 space-y-2 text-sm leading-6">
                  <p><strong>Status:</strong> {{ statusLabel(anggota.status_pengajuan) }}</p>
                  <p><strong>Masa Berlaku:</strong> {{ anggota.masa_berlaku_sampai || '-' }}</p>
                  <p><strong>No. Identitas:</strong> {{ anggota.nomor_identitas || '-' }}</p>
                  <p><strong>Telepon:</strong> {{ anggota.telepon || '-' }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-3xl bg-[#0f172a] p-5 text-white">
              <p class="text-sm font-semibold text-white/80">Keterangan</p>
              <p class="mt-2 text-sm leading-7 text-white/70">
                Jika data pada kartu fisik tidak sesuai dengan halaman ini, maka kartu tersebut perlu diperiksa ulang oleh pengurus PPSI.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
