<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, ref, watch } from 'vue'

import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

interface ProvinsiItem {
  id: number
  kode?: string | null
  nama: string
}

interface KotaKabItem {
  id: number
  kode?: string | null
  nama: string
}

interface KecamatanItem {
  id: number
  kode?: string | null
  nama: string
}

interface FixedKecamatan {
  id: number
  kode?: string | null
  nama: string
  kota_kab?: {
    id: number
    kode?: string | null
    nama: string
    provinsi?: {
      id: number
      kode?: string | null
      nama: string
    } | null
  } | null
}

interface PagoruanRow {
  id: number
  kode?: string | null
  nama: string
  telepon?: string | null
  nama_pimpinan?: string | null
  is_active: boolean
  kecamatan?: {
    id: number
    kode?: string | null
    nama: string
    kota_kab?: {
      id: number
      kode?: string | null
      nama: string
      provinsi?: {
        id: number
        kode?: string | null
        nama: string
      } | null
    } | null
  } | null
}

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

const props = defineProps<{
  pagoruan: {
    data: PagoruanRow[]
    current_page: number
    per_page: number
    links: PaginationLink[]
  }
  provinsiList: ProvinsiItem[]
  kotaKabList: KotaKabItem[]
  kecamatanList: KecamatanItem[]
  filters: {
    search?: string
    provinsi_id?: number | null
    kota_kab_id?: number | null
    kecamatan_id?: number | null
  }
  fixedKecamatan?: FixedKecamatan | null
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Pagoruan', href: '/pagoruan' },
]

const search = ref(props.filters.search ?? '')
const provinsiId = ref<number | ''>(props.filters.provinsi_id ?? '')
const kotaKabId = ref<number | ''>(props.filters.kota_kab_id ?? '')
const kecamatanId = ref<number | ''>(props.filters.kecamatan_id ?? '')
const kotaKabList = ref<KotaKabItem[]>(props.kotaKabList)
const kecamatanList = ref<KecamatanItem[]>(props.kecamatanList)
const loadingKotaKab = ref(false)
const loadingKecamatan = ref(false)
const isScopedToKecamatan = computed(() => !!props.fixedKecamatan)

watch(
  () => props.kotaKabList,
  (value) => {
    kotaKabList.value = value
  },
)

watch(
  () => props.kecamatanList,
  (value) => {
    kecamatanList.value = value
  },
)

watch(provinsiId, async (value, oldValue) => {
  if (isScopedToKecamatan.value || value === oldValue) {
    return
  }

  kotaKabId.value = ''
  kecamatanId.value = ''
  kecamatanList.value = []

  if (!value) {
    kotaKabList.value = []
    return
  }

  loadingKotaKab.value = true

  try {
    const response = await axios.get(`/api/kota-kab/${value}`)
    kotaKabList.value = response.data
  } finally {
    loadingKotaKab.value = false
  }
})

watch(kotaKabId, async (value, oldValue) => {
  if (isScopedToKecamatan.value || value === oldValue) {
    return
  }

  kecamatanId.value = ''

  if (!value) {
    kecamatanList.value = []
    return
  }

  loadingKecamatan.value = true

  try {
    const response = await axios.get(`/api/kecamatan/${value}?paginate=0`)
    kecamatanList.value = response.data.data ?? []
  } finally {
    loadingKecamatan.value = false
  }
})

function applyFilters() {
  router.get(
    '/pagoruan',
    {
      search: search.value || undefined,
      provinsi_id: isScopedToKecamatan.value ? undefined : provinsiId.value || undefined,
      kota_kab_id: isScopedToKecamatan.value ? undefined : kotaKabId.value || undefined,
      kecamatan_id: isScopedToKecamatan.value ? undefined : kecamatanId.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
    },
  )
}

function resetFilters() {
  search.value = ''
  provinsiId.value = isScopedToKecamatan.value ? (props.filters.provinsi_id ?? '') : ''
  kotaKabId.value = isScopedToKecamatan.value ? (props.filters.kota_kab_id ?? '') : ''
  kecamatanId.value = isScopedToKecamatan.value ? (props.filters.kecamatan_id ?? '') : ''
  kotaKabList.value = isScopedToKecamatan.value ? props.kotaKabList : []
  kecamatanList.value = isScopedToKecamatan.value ? props.kecamatanList : []

  router.get('/pagoruan', {}, { preserveState: true, replace: true })
}

function rowNumber(index: number) {
  return (props.pagoruan.current_page - 1) * props.pagoruan.per_page + index + 1
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Pagoruan" />

    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-slate-950 dark:text-white">Pagoruan</h1>
          <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">
            Kelola data pagoruan berdasarkan kecamatan agar pilihan di form anggota selalu sinkron.
          </p>
        </div>

        <Link href="/pagoruan/create">
          <Button>Tambah Pagoruan</Button>
        </Link>
      </div>

      <div
        v-if="fixedKecamatan"
        class="rounded-3xl border border-[#d7ebd9] bg-[#eef8ef] p-5 text-sm text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]"
      >
        Data dibatasi untuk kecamatan
        <span class="font-semibold">{{ fixedKecamatan.nama }}</span>
        <span v-if="fixedKecamatan.kota_kab">, {{ fixedKecamatan.kota_kab.nama }}</span>
        <span v-if="fixedKecamatan.kota_kab?.provinsi">, {{ fixedKecamatan.kota_kab.provinsi.nama }}</span>.
      </div>

      <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="grid gap-4 lg:grid-cols-[1.2fr_repeat(3,1fr)_auto]">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Cari Pagoruan</label>
            <input
              v-model="search"
              type="text"
              placeholder="Nama, kode, pimpinan, atau pelatih"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
          </div>

          <div v-if="!isScopedToKecamatan">
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Provinsi</label>
            <select
              v-model="provinsiId"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option value="">Semua Provinsi</option>
              <option v-for="item in provinsiList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
          </div>

          <div v-if="!isScopedToKecamatan">
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kota/Kab</label>
            <select
              v-model="kotaKabId"
              :disabled="loadingKotaKab || !provinsiId"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option value="">Semua Kota/Kab</option>
              <option v-for="item in kotaKabList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
          </div>

          <div v-if="!isScopedToKecamatan">
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kecamatan</label>
            <select
              v-model="kecamatanId"
              :disabled="loadingKecamatan || !kotaKabId"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option value="">Semua Kecamatan</option>
              <option v-for="item in kecamatanList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
          </div>

          <div class="flex items-end gap-2">
            <Button @click="applyFilters">Terapkan</Button>
            <Button variant="outline" @click="resetFilters">Reset</Button>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <table class="w-full text-left">
          <thead class="bg-slate-100 text-slate-700 dark:bg-[#163425] dark:text-slate-100">
            <tr>
              <th class="px-4 py-3">No</th>
              <th class="px-4 py-3">Kode</th>
              <th class="px-4 py-3">Nama Pagoruan</th>
              <th class="px-4 py-3">Kecamatan</th>
              <th class="px-4 py-3">Kota/Kab</th>
              <th class="px-4 py-3">Provinsi</th>
              <th class="px-4 py-3">Pimpinan</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="dark:text-slate-100">
            <tr
              v-for="(item, index) in pagoruan.data"
              :key="item.id"
              class="border-t border-slate-200 dark:border-slate-800"
            >
              <td class="px-4 py-3">{{ rowNumber(index) }}</td>
              <td class="px-4 py-3 font-mono">{{ item.kode || '-' }}</td>
              <td class="px-4 py-3">{{ item.nama }}</td>
              <td class="px-4 py-3">{{ item.kecamatan?.nama || '-' }}</td>
              <td class="px-4 py-3">{{ item.kecamatan?.kota_kab?.nama || '-' }}</td>
              <td class="px-4 py-3">{{ item.kecamatan?.kota_kab?.provinsi?.nama || '-' }}</td>
              <td class="px-4 py-3">{{ item.nama_pimpinan || '-' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="item.is_active ? 'bg-[#eef8ef] text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'"
                >
                  {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <Link :href="`/pagoruan/${item.id}/edit`">
                    <Button size="sm" variant="outline">Edit</Button>
                  </Link>
                  <Button size="sm" variant="destructive" @click="router.delete(`/pagoruan/${item.id}`)">
                    Hapus
                  </Button>
                </div>
              </td>
            </tr>
            <tr v-if="pagoruan.data.length === 0">
              <td colspan="9" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                Tidak ada data pagoruan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagoruan.links.length" class="flex flex-wrap justify-center gap-2">
        <Link
          v-for="link in pagoruan.links"
          :key="link.label"
          :href="link.url || ''"
          class="rounded-xl border px-3 py-2 text-sm"
          :class="{
            'border-[#0b6b31] bg-[#0b6b31] text-white dark:border-[#9fe7a8] dark:bg-[#123322]': link.active,
            'border-slate-300 text-slate-400 pointer-events-none dark:border-slate-700 dark:text-slate-600': !link.url,
            'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-200': link.url && !link.active,
          }"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>
