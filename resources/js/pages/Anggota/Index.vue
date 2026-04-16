<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'

interface AnggotaRow {
  id: string
  nomor_anggota: string
  nama_lengkap: string
  jenis_identitas: string
  nomor_identitas: string | null
  tanggal_lahir: string
  alamat: string | null
  telepon: string | null
  golongan_darah: string | null
  tanggal_gabung: string | null
  masa_berlaku_sampai: string | null
  status_pengajuan: string
  foto_path: string | null
  foto_url?: string | null
  kk_path: string | null
  dokumen_identitas_path: string | null
  kota_kab?: {
    nama: string
    kode?: string | null
    provinsi?: {
      nama: string
      kode?: string | null
    } | null
  } | null
  kecamatan?: {
    nama: string
    kode?: string | null
  } | null
  pagoruan?: {
    nama: string
    kode?: string | null
  } | null
}

const props = defineProps<{
  anggota: {
    data: AnggotaRow[]
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
]

const page = usePage<{ flash?: { success?: string; error?: string } }>()
const flash = page.props.flash ?? {}

const showModal = ref(false)
const selectedAnggota = ref<AnggotaRow | null>(null)

function openModal(row: AnggotaRow) {
  selectedAnggota.value = row
  showModal.value = true
}

function closeModal() {
  selectedAnggota.value = null
  showModal.value = false
}

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
    draft_dpd: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    diajukan_ke_dpw: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    diverifikasi_dpw: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    diajukan_ke_dpp: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
    disetujui_dpp: 'bg-[#eef8ef] text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]',
    ditolak: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
  }

  return classes[status] ?? classes.draft_dpd
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daftar Anggota" />

    <div class="space-y-6 p-6">
      <div v-if="flash.success" class="rounded bg-green-100 p-3 text-green-700 dark:bg-green-950/40 dark:text-green-300">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="rounded bg-red-100 p-3 text-red-700 dark:bg-red-950/40 dark:text-red-300">
        {{ flash.error }}
      </div>

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daftar Anggota</h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Data anggota sekarang memakai nomor anggota resmi, DPD, DPC, dan Pagoruan.</p>
        </div>
        <Link href="/anggota/create">
          <Button>+ Tambah Anggota</Button>
        </Link>
      </div>

      <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0f1b16]">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-100 text-slate-700 dark:bg-[#163425] dark:text-slate-100">
            <tr>
              <th class="px-4 py-3">No. Anggota</th>
              <th class="px-4 py-3">Nama</th>
              <th class="px-4 py-3">DPD</th>
              <th class="px-4 py-3">DPC</th>
              <th class="px-4 py-3">Pagoruan</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="dark:text-slate-100">
            <tr v-if="anggota.data.length === 0">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-slate-400">
                Belum ada anggota terdaftar.
              </td>
            </tr>
            <tr v-for="row in anggota.data" :key="row.id" class="border-t border-slate-200 dark:border-slate-800">
              <td class="px-4 py-3 font-mono text-[13px] font-semibold">{{ row.nomor_anggota }}</td>
              <td class="px-4 py-3">
                <p class="font-medium">{{ row.nama_lengkap }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ row.nomor_identitas || '-' }}</p>
              </td>
              <td class="px-4 py-3">{{ row.kota_kab?.nama ?? '-' }}</td>
              <td class="px-4 py-3">{{ row.kecamatan?.nama ?? '-' }}</td>
              <td class="px-4 py-3">{{ row.pagoruan?.nama ?? '-' }}</td>
              <td class="px-4 py-3">
                <span :class="statusClass(row.status_pengajuan)" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                  {{ statusLabel(row.status_pengajuan) }}
                </span>
              </td>
              <td class="px-4 py-3 text-right space-x-2">
                <Button size="sm" @click="openModal(row)">Detail</Button>
                <Link :href="`/anggota/${row.id}/edit`">
                  <Button variant="outline" size="sm">Edit</Button>
                </Link>
                <Link as="button" method="delete" :href="`/anggota/${row.id}`" class="inline-flex">
                  <Button variant="destructive" size="sm">Hapus</Button>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-center space-x-1">
        <Link
          v-for="link in anggota.links"
          :key="link.label"
          :href="link.url || ''"
          class="rounded border px-3 py-1"
          :class="[link.active ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-100 dark:border-slate-700 dark:bg-[#10261c] dark:text-slate-100 dark:hover:bg-[#163425]', link.url ? '' : 'opacity-50 cursor-not-allowed']"
          v-html="link.label"
        />
      </div>
    </div>

    <div v-if="showModal && selectedAnggota" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="relative max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-xl dark:bg-[#0f1b16] dark:text-slate-100">
        <button class="absolute right-4 top-4 text-2xl text-slate-400 hover:text-slate-600 dark:hover:text-white" @click="closeModal">×</button>

        <div class="grid gap-6 md:grid-cols-[180px_1fr]">
          <div class="space-y-3">
            <img
              v-if="selectedAnggota.foto_url"
              :src="selectedAnggota.foto_url"
              class="h-52 w-full rounded-2xl object-cover"
            />
            <div v-else class="flex h-52 w-full items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
              Tidak ada foto
            </div>
          </div>

          <div class="space-y-3">
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ selectedAnggota.nama_lengkap }}</h2>
            <p class="font-mono text-sm text-[#0b6b31] dark:text-[#9fe7a8]">{{ selectedAnggota.nomor_anggota }}</p>
            <div class="grid gap-3 text-sm md:grid-cols-2">
              <p><strong>Identitas:</strong> {{ selectedAnggota.jenis_identitas.toUpperCase() }} - {{ selectedAnggota.nomor_identitas || '-' }}</p>
              <p><strong>Tanggal Lahir:</strong> {{ selectedAnggota.tanggal_lahir }}</p>
              <p><strong>Telepon:</strong> {{ selectedAnggota.telepon || '-' }}</p>
              <p><strong>Golongan Darah:</strong> {{ selectedAnggota.golongan_darah || '-' }}</p>
              <p><strong>DPW:</strong> {{ selectedAnggota.kota_kab?.provinsi?.nama || '-' }}</p>
              <p><strong>DPD:</strong> {{ selectedAnggota.kota_kab?.nama || '-' }}</p>
              <p><strong>DPC:</strong> {{ selectedAnggota.kecamatan?.nama || '-' }}</p>
              <p><strong>Pagoruan:</strong> {{ selectedAnggota.pagoruan?.nama || '-' }}</p>
              <p><strong>Status:</strong> {{ statusLabel(selectedAnggota.status_pengajuan) }}</p>
              <p><strong>Masa Berlaku:</strong> {{ selectedAnggota.masa_berlaku_sampai || '-' }}</p>
            </div>
            <p class="text-sm leading-6"><strong>Alamat:</strong> {{ selectedAnggota.alamat || '-' }}</p>

            <div class="flex flex-wrap gap-3 pt-2">
              <a v-if="selectedAnggota.kk_path" :href="`/storage/${selectedAnggota.kk_path}`" target="_blank" class="text-sm font-medium text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat KK</a>
              <a v-if="selectedAnggota.dokumen_identitas_path" :href="`/storage/${selectedAnggota.dokumen_identitas_path}`" target="_blank" class="text-sm font-medium text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat Identitas</a>
              <Link :href="`/anggota/${selectedAnggota.id}/print`" class="text-sm font-medium text-[#0b6b31] underline dark:text-[#9fe7a8]">Print Kartu</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
