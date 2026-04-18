<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import type { AnggotaRecord, SelectOption } from '@/pages/Anggota/types'
import { formatDisplayDate, statusClass, statusLabel } from '@/pages/Anggota/types'

const props = defineProps<{
  anggota: {
    data: AnggotaRecord[]
    current_page: number
    per_page: number
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    status?: string | null
  }
  statusOptions: SelectOption[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
]

const page = usePage<{ flash?: { success?: string; error?: string } }>()
const flash = computed(() => page.props.flash ?? {})
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const selectedAnggota = ref<AnggotaRecord | null>(null)

const currentPageCount = computed(() => props.anggota.data.length)

function applyFilters() {
  router.get(
    '/anggota',
    {
      search: search.value || undefined,
      status: status.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
    },
  )
}

function resetFilters() {
  search.value = ''
  status.value = ''
  router.get('/anggota', {}, { preserveState: true, replace: true })
}

function confirmDelete(id: string, nama: string) {
  if (window.confirm(`Hapus anggota "${nama}"?`)) {
    router.delete(`/anggota/${id}`, { preserveScroll: true })
  }
}

function openDetail(row: AnggotaRecord) {
  selectedAnggota.value = row
}

function closeDetail() {
  selectedAnggota.value = null
}

function rowNumber(index: number) {
  return (props.anggota.current_page - 1) * props.anggota.per_page + index + 1
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daftar Anggota" />

    <div class="space-y-6 p-6">
      <div v-if="flash.success" class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
        {{ flash.error }}
      </div>

      <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.22),_transparent_30%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.18),_transparent_22%),linear-gradient(135deg,_#f8fbf5_0%,_#ffffff_48%,_#eef8ef_100%)] p-6 shadow-[0_28px_80px_-44px_rgba(11,107,49,0.48)] dark:border-white/10 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_28%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.1),_transparent_18%),linear-gradient(135deg,_#0b1713_0%,_#102019_48%,_#16251d_100%)] dark:shadow-none">
        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
          <div>
            <p class="inline-flex rounded-full border border-[#d7ebd9] bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]">
              Modul Keanggotaan
            </p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white md:text-4xl">Daftar Anggota</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
              Kelola data anggota, cek status pengajuan, dan buka detail dokumen pendukung dari satu halaman.
            </p>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-3xl border border-white/80 bg-white/85 p-5 backdrop-blur dark:border-white/10 dark:bg-white/5">
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Total Anggota</p>
              <p class="mt-3 text-4xl font-semibold text-slate-950 dark:text-white">{{ anggota.total }}</p>
              <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">Semua anggota yang saat ini tercatat di sistem.</p>
            </div>
            <div class="rounded-3xl border border-[#d7ebd9] bg-[#0b6b31] p-5 text-white dark:border-[#1f4932] dark:bg-[#0d2419]">
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Halaman Aktif</p>
              <p class="mt-3 text-4xl font-semibold">{{ currentPageCount }}</p>
              <p class="mt-2 text-sm text-white/75">Jumlah data yang tampil pada halaman ini.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div class="grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Cari Anggota</label>
              <input
                v-model="search"
                type="text"
                placeholder="Nomor anggota, nama, telepon, atau wilayah"
                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Status Pengajuan</label>
              <select
                v-model="status"
                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
              >
                <option value="">Semua status</option>
                <option v-for="item in statusOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row">
            <Button variant="outline" @click="resetFilters">Reset</Button>
            <Button @click="applyFilters">Terapkan Filter</Button>
            <Button as-child>
              <Link href="/anggota/create">Tambah Anggota</Link>
            </Button>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[980px] text-left">
            <thead class="bg-slate-100 text-sm text-slate-700 dark:bg-[#163425] dark:text-slate-100">
              <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Anggota</th>
                <th class="px-4 py-3">Nomor</th>
                <th class="px-4 py-3">DPD</th>
                <th class="px-4 py-3">DPC</th>
                <th class="px-4 py-3">Pagoruan</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Dokumen</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-sm dark:text-slate-100">
              <tr v-if="anggota.data.length === 0">
                <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                  Belum ada anggota yang cocok dengan filter saat ini.
                </td>
              </tr>

              <tr v-for="(row, index) in anggota.data" :key="row.id" class="border-t border-slate-200 align-top dark:border-slate-800">
                <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ rowNumber(index) }}</td>
                <td class="px-4 py-4">
                  <div class="flex items-start gap-3">
                    <img v-if="row.foto_url" :src="row.foto_url" class="h-12 w-12 rounded-2xl object-cover" />
                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xs text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                      Foto
                    </div>

                    <div class="space-y-1">
                      <p class="font-semibold text-slate-900 dark:text-white">{{ row.nama_lengkap }}</p>
                      <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ row.jenis_identitas === 'ktp' ? 'KTP' : 'Kartu Pelajar' }}:
                        {{ row.nomor_identitas || '-' }}
                      </p>
                      <p class="text-xs text-slate-500 dark:text-slate-400">Lahir: {{ formatDisplayDate(row.tanggal_lahir) }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <p class="font-mono text-[13px] font-semibold text-[#0b6b31] dark:text-[#9fe7a8]">{{ row.nomor_anggota }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Urut DPD: {{ row.nomor_urut_dpd }}</p>
                </td>
                <td class="px-4 py-4">
                  <p>{{ row.kota_kab?.nama || '-' }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.kota_kab?.provinsi?.nama || '-' }}</p>
                </td>
                <td class="px-4 py-4">{{ row.kecamatan?.nama || '-' }}</td>
                <td class="px-4 py-4">{{ row.pagoruan?.nama || '-' }}</td>
                <td class="px-4 py-4">
                  <span :class="statusClass(row.status_pengajuan)" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                    {{ statusLabel(row.status_pengajuan) }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="space-y-1 text-xs">
                    <a v-if="row.kk_url" :href="row.kk_url" target="_blank" class="block text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat KK</a>
                    <a v-if="row.dokumen_identitas_url" :href="row.dokumen_identitas_url" target="_blank" class="block text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat Identitas</a>
                    <span v-if="!row.kk_url && !row.dokumen_identitas_url" class="text-slate-500 dark:text-slate-400">Tidak ada</span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex justify-end gap-2">
                    <Button size="sm" variant="outline" @click="openDetail(row)">Detail</Button>
                    <Button as-child size="sm" variant="outline">
                      <Link :href="`/anggota/${row.id}/edit`">Edit</Link>
                    </Button>
                    <Button size="sm" variant="destructive" @click="confirmDelete(row.id, row.nama_lengkap)">Hapus</Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <div v-if="anggota.links.length" class="flex flex-wrap justify-center gap-2">
        <Link
          v-for="link in anggota.links"
          :key="link.label"
          :href="link.url || ''"
          class="rounded-xl border px-3 py-2 text-sm"
          :class="{
            'border-[#0b6b31] bg-[#0b6b31] text-white dark:border-[#9fe7a8] dark:bg-[#123322]': link.active,
            'pointer-events-none border-slate-300 text-slate-400 dark:border-slate-700 dark:text-slate-600': !link.url,
            'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-200': link.url && !link.active,
          }"
          v-html="link.label"
        />
      </div>
    </div>

    <div v-if="selectedAnggota" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @click.self="closeDetail">
      <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-white/10 bg-white p-6 shadow-2xl dark:bg-[#0f1b16] dark:text-slate-100">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Detail Anggota</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ selectedAnggota.nama_lengkap }}</h2>
            <p class="mt-2 font-mono text-[#0b6b31] dark:text-[#9fe7a8]">{{ selectedAnggota.nomor_anggota }}</p>
          </div>
          <button class="rounded-full border border-slate-200 px-3 py-1 text-sm text-slate-500 hover:text-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white" @click="closeDetail">
            Tutup
          </button>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-[260px_1fr]">
          <div class="space-y-4">
            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/40">
              <img v-if="selectedAnggota.foto_url" :src="selectedAnggota.foto_url" class="h-80 w-full object-cover" />
              <div v-else class="flex h-80 items-center justify-center text-sm text-slate-400">
                Foto tidak tersedia
              </div>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 p-4 text-sm dark:border-slate-700">
              <p class="font-semibold text-slate-900 dark:text-white">Dokumen Pendukung</p>
              <div class="mt-3 space-y-2">
                <a v-if="selectedAnggota.kk_url" :href="selectedAnggota.kk_url" target="_blank" class="block text-[#0b6b31] underline dark:text-[#9fe7a8]">Buka KK</a>
                <a v-if="selectedAnggota.dokumen_identitas_url" :href="selectedAnggota.dokumen_identitas_url" target="_blank" class="block text-[#0b6b31] underline dark:text-[#9fe7a8]">Buka Dokumen Identitas</a>
                <p v-if="!selectedAnggota.kk_url && !selectedAnggota.dokumen_identitas_url" class="text-slate-500 dark:text-slate-400">Tidak ada dokumen terlampir.</p>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
              <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Data Pribadi</p>
                <div class="mt-4 space-y-2 text-sm leading-6">
                  <p><strong>Jenis Identitas:</strong> {{ selectedAnggota.jenis_identitas === 'ktp' ? 'KTP' : 'Kartu Pelajar' }}</p>
                  <p><strong>Nomor Identitas:</strong> {{ selectedAnggota.nomor_identitas || '-' }}</p>
                  <p><strong>Tanggal Lahir:</strong> {{ formatDisplayDate(selectedAnggota.tanggal_lahir) }}</p>
                  <p><strong>Telepon:</strong> {{ selectedAnggota.telepon || '-' }}</p>
                  <p><strong>Golongan Darah:</strong> {{ selectedAnggota.golongan_darah || '-' }}</p>
                </div>
              </div>

              <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Keanggotaan</p>
                <div class="mt-4 space-y-2 text-sm leading-6">
                  <p><strong>Status:</strong> {{ statusLabel(selectedAnggota.status_pengajuan) }}</p>
                  <p><strong>Tanggal Gabung:</strong> {{ formatDisplayDate(selectedAnggota.tanggal_gabung) }}</p>
                  <p><strong>Masa Berlaku:</strong> {{ formatDisplayDate(selectedAnggota.masa_berlaku_sampai) }}</p>
                  <p><strong>Catatan:</strong> {{ selectedAnggota.catatan_verifikasi || '-' }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Struktur Wilayah</p>
              <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                <p><strong>DPW:</strong> {{ selectedAnggota.kota_kab?.provinsi?.nama || '-' }}</p>
                <p><strong>DPD:</strong> {{ selectedAnggota.kota_kab?.nama || '-' }}</p>
                <p><strong>DPC:</strong> {{ selectedAnggota.kecamatan?.nama || '-' }}</p>
                <p><strong>Pagoruan:</strong> {{ selectedAnggota.pagoruan?.nama || '-' }}</p>
              </div>
            </div>

            <div class="rounded-[1.5rem] bg-[#0f172a] p-5 text-white dark:bg-[linear-gradient(135deg,#123322_0%,#0d2419_55%,#3a2b0e_100%)]">
              <p class="text-sm font-semibold text-white/80">Alamat</p>
              <p class="mt-2 text-sm leading-7 text-white/75">{{ selectedAnggota.alamat || '-' }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
              <Button as-child variant="outline">
                <Link :href="`/anggota/${selectedAnggota.id}/edit`">Edit Anggota</Link>
              </Button>
              <Button as-child>
                <Link :href="`/anggota/${selectedAnggota.id}/print`">Print Kartu</Link>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
