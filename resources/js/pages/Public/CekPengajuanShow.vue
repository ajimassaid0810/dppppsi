<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Check, Clock3, ShieldCheck, XCircle } from 'lucide-vue-next'

import PublicLayout from '@/components/public/PublicLayout.vue'
import type { PublicPengajuanRecord, PublicSite } from '@/pages/Public/types'
import { formatPublicDate, pengajuanStatusClass, pengajuanStatusLabel } from '@/pages/Public/types'

const props = defineProps<{
  site: PublicSite
  pengajuan: PublicPengajuanRecord
}>()

const navLinks = [
  { label: 'Home', href: '/' },
  { label: 'Daftar Anggota', href: '/pendaftaran-anggota' },
  { label: 'Cek Pengajuan', href: '/cek-pengajuan' },
]

const timeline = computed(() => {
  const status = props.pengajuan.status

  return [
    { title: 'Pengajuan Terkirim', caption: formatPublicDate(props.pengajuan.submitted_at, true), active: true, icon: Check },
    { title: 'Sedang Ditinjau', caption: props.pengajuan.ditinjau_at ? formatPublicDate(props.pengajuan.ditinjau_at, true) : 'Menunggu verifikasi admin', active: ['ditinjau', 'dikonversi', 'ditolak'].includes(status), icon: Clock3 },
    {
      title: status === 'ditolak' ? 'Pengajuan Ditolak' : 'Konversi Menjadi Anggota',
      caption: status === 'dikonversi' ? formatPublicDate(props.pengajuan.dikonversi_at, true) : status === 'ditolak' ? 'Admin memberikan keputusan penolakan' : 'Belum dikonversi',
      active: ['dikonversi', 'ditolak'].includes(status),
      icon: status === 'ditolak' ? XCircle : ShieldCheck,
    },
  ]
})
</script>

<template>
  <PublicLayout :site="site" title="Status Pengajuan" :nav-links="navLinks">
    <section class="mx-auto max-w-7xl px-4 py-12 md:px-6">
      <div class="grid gap-8 xl:grid-cols-[0.85fr_1.15fr]">
        <aside class="space-y-6">
          <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(160deg,#0f172a_0%,#0b6b31_55%,#123322_100%)] p-6 text-white shadow-[0_28px_90px_-50px_rgba(11,107,49,0.84)] dark:border-white/10 dark:shadow-none">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Kode Pengajuan</p>
            <h1 class="mt-3 font-['Bebas_Neue'] text-4xl uppercase leading-none tracking-[0.08em] md:text-5xl">{{ pengajuan.kode_pengajuan }}</h1>
            <div :class="pengajuanStatusClass(pengajuan.status)" class="mt-5 inline-flex rounded-full px-4 py-2 text-sm font-semibold">{{ pengajuanStatusLabel(pengajuan.status) }}</div>
            <p class="mt-5 text-sm leading-7 text-white/75">Simpan kode ini. Jika perlu mengecek ulang, buka lagi halaman ini melalui menu cek pengajuan.</p>
          </div>

          <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Timeline Status</p>
            <div class="mt-5 space-y-4">
              <div v-for="item in timeline" :key="item.title" class="flex items-start gap-4 rounded-[1.4rem] border px-4 py-4" :class="item.active ? 'border-[#d7ebd9] bg-[#eef8ef] dark:border-[#234a33] dark:bg-[#123322]' : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-[#102019]'">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl" :class="item.active ? 'bg-[#0b6b31] text-white dark:bg-[#9fe7a8] dark:text-[#102019]' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-300'">
                  <component :is="item.icon" class="size-5" />
                </div>
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                  <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ item.caption }}</p>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <section class="space-y-6">
          <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Data Pengajuan</p>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
              <div class="space-y-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                <p><strong>Nama Lengkap:</strong> {{ pengajuan.nama_lengkap }}</p>
                <p><strong>Jenis Identitas:</strong> {{ pengajuan.jenis_identitas }}</p>
                <p><strong>Nomor Identitas:</strong> {{ pengajuan.nomor_identitas }}</p>
                <p><strong>Tanggal Lahir:</strong> {{ formatPublicDate(pengajuan.tanggal_lahir) }}</p>
                <p><strong>Telepon:</strong> {{ pengajuan.telepon }}</p>
                <p><strong>Email:</strong> {{ pengajuan.email || '-' }}</p>
              </div>
              <div class="space-y-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                <p><strong>DPW:</strong> {{ pengajuan.provinsi?.nama || '-' }}</p>
                <p><strong>DPD:</strong> {{ pengajuan.kota_kab?.nama || '-' }}</p>
                <p><strong>DPC:</strong> {{ pengajuan.kecamatan?.nama || '-' }}</p>
                <p><strong>Pagoruan:</strong> {{ pengajuan.pagoruan?.nama || '-' }}</p>
                <p><strong>Dikirim:</strong> {{ formatPublicDate(pengajuan.submitted_at, true) }}</p>
              </div>
            </div>

            <div class="mt-5 rounded-[1.4rem] border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-7 text-slate-600 dark:border-slate-700 dark:bg-[#102019] dark:text-slate-300">
              <strong>Alamat:</strong> {{ pengajuan.alamat }}
            </div>
          </div>

          <div v-if="pengajuan.catatan_admin" class="rounded-[1.8rem] border border-amber-200 bg-amber-50 p-6 text-sm leading-7 text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-200">
            <p class="text-xs font-semibold uppercase tracking-[0.24em]">Catatan Admin</p>
            <p class="mt-3">{{ pengajuan.catatan_admin }}</p>
          </div>

          <div v-if="pengajuan.status === 'dikonversi' && pengajuan.anggota_public_url" class="rounded-[1.8rem] border border-[#d7ebd9] bg-[#eef8ef] p-6 dark:border-[#234a33] dark:bg-[#123322]">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Anggota Resmi</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-white">Pengajuan Anda sudah dikonversi</h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-200">Data Anda telah masuk sebagai anggota resmi. Gunakan link berikut untuk membuka halaman publik verifikasi keanggotaan.</p>
            <Link :href="pengajuan.anggota_public_url" class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#0b6b31] px-6 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]">
              Buka Kartu / Verifikasi
              <ArrowRight class="size-4" />
            </Link>
          </div>

          <div class="flex flex-col gap-4 sm:flex-row">
            <Link href="/cek-pengajuan" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#0b6b31] hover:text-[#0b6b31] dark:border-slate-700 dark:text-slate-200 dark:hover:border-[#9fe7a8] dark:hover:text-[#9fe7a8]">Cek Kode Lain</Link>
            <Link href="/pendaftaran-anggota" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-6 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]">Pendaftaran Baru</Link>
          </div>
        </section>
      </div>
    </section>
  </PublicLayout>
</template>
