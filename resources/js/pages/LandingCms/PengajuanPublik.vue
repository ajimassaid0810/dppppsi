<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AppPageProps, BreadcrumbItem } from '@/types'
import type { PublicPengajuanRecord } from '@/pages/Public/types'
import { formatPublicDate, pengajuanStatusClass, pengajuanStatusLabel } from '@/pages/Public/types'

const props = defineProps<{
  pengajuan: {
    data: PublicPengajuanRecord[]
    current_page: number
    per_page: number
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string | null
    status?: string | null
  }
  statusOptions: Array<{ value: string; label: string }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Landing CMS', href: '/landing-cms' },
  { title: 'Pengajuan Publik', href: '/landing-cms/pengajuan-publik' },
]

const page = usePage<AppPageProps>()
const flash = computed(() => page.props.flash ?? {})
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const selectedPengajuan = ref<PublicPengajuanRecord | null>(null)
const statusForm = useForm({
  status: 'ditinjau',
  catatan_admin: '',
})

function applyFilters() {
  router.get(
    '/landing-cms/pengajuan-publik',
    {
      search: search.value || undefined,
      status: status.value || undefined,
    },
    { preserveState: true, replace: true },
  )
}

function resetFilters() {
  search.value = ''
  status.value = ''
  router.get('/landing-cms/pengajuan-publik', {}, { preserveState: true, replace: true })
}

function openDetail(item: PublicPengajuanRecord) {
  selectedPengajuan.value = item
  statusForm.defaults({
    status: item.status === 'ditolak' ? 'ditolak' : 'ditinjau',
    catatan_admin: item.catatan_admin ?? '',
  })
  statusForm.reset()
  statusForm.clearErrors()
}

function closeDetail() {
  selectedPengajuan.value = null
}

function saveStatus(nextStatus: 'ditinjau' | 'ditolak') {
  if (!selectedPengajuan.value) return

  statusForm.status = nextStatus
  statusForm.patch(`/landing-cms/pengajuan-publik/${selectedPengajuan.value.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      closeDetail()
    },
  })
}

function convertToAnggota() {
  if (!selectedPengajuan.value) return

  statusForm.post(`/landing-cms/pengajuan-publik/${selectedPengajuan.value.id}/convert`, {
    preserveScroll: true,
    onSuccess: () => {
      closeDetail()
    },
  })
}

function rowNumber(index: number) {
  return (props.pengajuan.current_page - 1) * props.pengajuan.per_page + index + 1
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Pengajuan Publik" />

    <div class="space-y-6 p-6">
      <div v-if="flash.success" class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
        {{ flash.error }}
      </div>

      <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.22),_transparent_30%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.18),_transparent_22%),linear-gradient(135deg,_#f8fbf5_0%,_#ffffff_48%,_#eef8ef_100%)] p-6 shadow-[0_28px_80px_-44px_rgba(11,107,49,0.48)] dark:border-white/10 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_28%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.1),_transparent_18%),linear-gradient(135deg,_#0b1713_0%,_#102019_48%,_#16251d_100%)] dark:shadow-none">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Queue Verifikasi</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">Pengajuan Anggota Publik</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
              Telaah dokumen, berikan catatan, ubah status, atau konversi pengajuan menjadi anggota resmi.
            </p>
          </div>
          <Button as-child>
            <Link href="/landing-cms">Kembali ke Landing CMS</Link>
          </Button>
        </div>
      </section>

      <section class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="grid gap-4 md:grid-cols-[1.1fr_0.9fr_auto_auto]">
          <input v-model="search" type="text" placeholder="Cari kode, nama, identitas, atau telepon" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
          <select v-model="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
            <option value="">Semua status</option>
            <option v-for="item in statusOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
          </select>
          <Button variant="outline" @click="resetFilters">Reset</Button>
          <Button @click="applyFilters">Terapkan</Button>
        </div>
      </section>

      <section class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[980px] text-left">
            <thead class="bg-slate-100 text-sm text-slate-700 dark:bg-[#163425] dark:text-slate-100">
              <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Pengajuan</th>
                <th class="px-4 py-3">Wilayah</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Dikirim</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-sm dark:text-slate-100">
              <tr v-if="pengajuan.data.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada pengajuan yang cocok dengan filter saat ini.</td>
              </tr>

              <tr v-for="(row, index) in pengajuan.data" :key="row.id" class="border-t border-slate-200 align-top dark:border-slate-800">
                <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ rowNumber(index) }}</td>
                <td class="px-4 py-4">
                  <p class="font-semibold text-slate-900 dark:text-white">{{ row.nama_lengkap }}</p>
                  <p class="mt-1 font-mono text-xs text-[#0b6b31] dark:text-[#9fe7a8]">{{ row.kode_pengajuan }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.nomor_identitas }}</p>
                </td>
                <td class="px-4 py-4">
                  <p>{{ row.kota_kab?.nama || '-' }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.provinsi?.nama || '-' }}</p>
                </td>
                <td class="px-4 py-4">
                  <span :class="pengajuanStatusClass(row.status)" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                    {{ pengajuanStatusLabel(row.status) }}
                  </span>
                </td>
                <td class="px-4 py-4">{{ formatPublicDate(row.submitted_at, true) }}</td>
                <td class="px-4 py-4 text-right">
                  <Button size="sm" variant="outline" @click="openDetail(row)">Detail</Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <div v-if="pengajuan.links.length" class="flex flex-wrap justify-center gap-2">
        <Link v-for="link in pengajuan.links" :key="link.label" :href="link.url || ''" class="rounded-xl border px-3 py-2 text-sm" :class="{
          'border-[#0b6b31] bg-[#0b6b31] text-white dark:border-[#9fe7a8] dark:bg-[#123322]': link.active,
          'pointer-events-none border-slate-300 text-slate-400 dark:border-slate-700 dark:text-slate-600': !link.url,
          'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-200': link.url && !link.active,
        }" v-html="link.label" />
      </div>
    </div>

    <div v-if="selectedPengajuan" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @click.self="closeDetail">
      <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/10 bg-white p-6 shadow-2xl dark:bg-[#0f1b16] dark:text-slate-100">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Detail Pengajuan</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ selectedPengajuan.nama_lengkap }}</h2>
            <p class="mt-2 font-mono text-[#0b6b31] dark:text-[#9fe7a8]">{{ selectedPengajuan.kode_pengajuan }}</p>
          </div>
          <button class="rounded-full border border-slate-200 px-3 py-1 text-sm text-slate-500 hover:text-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white" @click="closeDetail">Tutup</button>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
          <div class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-3">
              <a :href="selectedPengajuan.foto_url" target="_blank" class="rounded-[1.5rem] border border-slate-200 p-4 text-center text-sm font-semibold text-[#0b6b31] dark:border-slate-700 dark:text-[#9fe7a8]">Foto</a>
              <a :href="selectedPengajuan.kk_url" target="_blank" class="rounded-[1.5rem] border border-slate-200 p-4 text-center text-sm font-semibold text-[#0b6b31] dark:border-slate-700 dark:text-[#9fe7a8]">KK</a>
              <a :href="selectedPengajuan.dokumen_identitas_url" target="_blank" class="rounded-[1.5rem] border border-slate-200 p-4 text-center text-sm font-semibold text-[#0b6b31] dark:border-slate-700 dark:text-[#9fe7a8]">Identitas</a>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Data Pengajuan</p>
              <div class="mt-4 space-y-2 text-sm leading-6">
                <p><strong>Status:</strong> {{ pengajuanStatusLabel(selectedPengajuan.status) }}</p>
                <p><strong>Dikirim:</strong> {{ formatPublicDate(selectedPengajuan.submitted_at, true) }}</p>
                <p><strong>Telepon:</strong> {{ selectedPengajuan.telepon }}</p>
                <p><strong>Email:</strong> {{ selectedPengajuan.email || '-' }}</p>
                <p><strong>Alamat:</strong> {{ selectedPengajuan.alamat }}</p>
              </div>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Wilayah</p>
              <div class="mt-4 space-y-2 text-sm leading-6">
                <p><strong>DPW:</strong> {{ selectedPengajuan.provinsi?.nama || '-' }}</p>
                <p><strong>DPD:</strong> {{ selectedPengajuan.kota_kab?.nama || '-' }}</p>
                <p><strong>DPC:</strong> {{ selectedPengajuan.kecamatan?.nama || '-' }}</p>
                <p><strong>Pagoruan:</strong> {{ selectedPengajuan.pagoruan?.nama || '-' }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-5">
            <div class="rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan Admin</label>
              <textarea v-model="statusForm.catatan_admin" rows="6" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <p v-if="statusForm.errors.catatan_admin" class="mt-2 text-sm text-red-600">{{ statusForm.errors.catatan_admin }}</p>
            </div>

            <div class="rounded-[1.5rem] bg-[#0f172a] p-5 text-white dark:bg-[linear-gradient(135deg,#123322_0%,#0d2419_55%,#3a2b0e_100%)]">
              <p class="text-sm font-semibold text-white/80">Aksi Review</p>
              <div class="mt-4 flex flex-col gap-3">
                <Button v-if="selectedPengajuan.status !== 'dikonversi'" variant="outline" class="justify-center text-slate-900 dark:text-white" @click="saveStatus('ditinjau')">
                  Tandai Ditinjau
                </Button>
                <Button v-if="selectedPengajuan.status !== 'dikonversi'" variant="destructive" class="justify-center" @click="saveStatus('ditolak')">
                  Tolak Pengajuan
                </Button>
                <Button v-if="selectedPengajuan.status !== 'dikonversi'" class="justify-center" @click="convertToAnggota">
                  Konversi Menjadi Anggota
                </Button>
                <Link v-if="selectedPengajuan.anggota_public_url" :href="selectedPengajuan.anggota_public_url" class="inline-flex items-center justify-center rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white/85 transition hover:text-white">
                  Buka Halaman Publik Anggota
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
