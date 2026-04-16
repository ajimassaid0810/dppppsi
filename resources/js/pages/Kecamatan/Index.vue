<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { ref, watch } from 'vue'
import axios from 'axios'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  provinsiList: { id: number; kode?: string | null; nama: string }[]
  filters: { provinsi_id?: number; kota_kab_id?: number }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPC Kecamatan', href: '/kecamatan' },
]

// state filter
const provinsiId = ref<number | ''>(props.filters.provinsi_id ?? '')
const kotaKabId = ref<number | ''>(props.filters.kota_kab_id ?? '')
const applied = ref(false)
const kecamatanData = ref<any[]>([])
const kotaKabList = ref<{id: number, kode?: string | null, nama: string}[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  next: null as string | null,
  prev: null as string | null,
})

// fetch kota/kab saat provinsi berubah
watch(provinsiId, async (newProvId) => {
  kotaKabId.value = ''
  if (!newProvId) {
    kotaKabList.value = []
    return
  }
  try {
    const res = await axios.get(`/api/kota-kab/${newProvId}`)
    kotaKabList.value = res.data
  } catch (err) {
    console.error(err)
    kotaKabList.value = []
  }
})

// fetch kecamatan dari API dengan pagination
async function fetchKecamatan(pageUrl?: string) {
  if (!kotaKabId.value) return

  loading.value = true
  error.value = null

  try {
    const url = pageUrl || `/api/kecamatan/${kotaKabId.value}`
    const res = await axios.get(url)
    kecamatanData.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
      total: res.data.total,
      next: res.data.links.next,
      prev: res.data.links.prev,
    }
  } catch (err) {
    console.error(err)
    error.value = 'Gagal mengambil data kecamatan'
    kecamatanData.value = []
  } finally {
    loading.value = false
    applied.value = true
  }
}

// klik Apply
function applyFilters() {
  fetchKecamatan()
}

// pagination
function goToPage(url: string | null) {
  if (url) fetchKecamatan(url)
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="DPC Kecamatan" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
      <!-- Filter -->
      <div class="flex gap-4 items-end">
        <div class="flex-1">
          <label class="mb-1 block font-medium text-slate-800 dark:text-slate-200">Provinsi</label>
          <select v-model="provinsiId" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
            <option value="">-- Pilih Provinsi --</option>
            <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
              {{ prov.kode ? `[${prov.kode}] ` : '' }}{{ prov.nama }}
            </option>
          </select>
        </div>

        <div class="flex-1">
          <label class="mb-1 block font-medium text-slate-800 dark:text-slate-200">Kota/Kab</label>
          <select v-model="kotaKabId" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
            <option value="">-- Pilih Kota/Kab --</option>
            <option v-for="kota in kotaKabList" :key="kota.id" :value="kota.id">
              {{ kota.kode ? `[${kota.kode}] ` : '' }}{{ kota.nama }}
            </option>
          </select>
        </div>

        <Button @click="applyFilters">Apply</Button>

        <Link href="/dpc-kecamatan/create">
          <Button>Tambah DPC Kecamatan</Button>
        </Link>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="py-4 text-center text-slate-600 dark:text-slate-300">Loading...</div>

      <!-- Error -->
      <div v-if="error" class="text-red-500 text-center py-4">{{ error }}</div>

      <!-- Table -->
      <div v-if="applied && !loading && kecamatanData.length" class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0f1b16]">
        <table class="w-full text-left">
          <thead class="bg-slate-100 text-slate-700 dark:bg-[#163425] dark:text-slate-100">
            <tr>
              <th class="px-4 py-2">No</th>
              <th class="px-4 py-2">Kode DPC</th>
              <th class="px-4 py-2">Nama Kecamatan</th>
              <th class="px-4 py-2">Kode DPD</th>
              <th class="px-4 py-2">Kota/Kab</th>
              <th class="px-4 py-2">Kode DPW</th>
              <th class="px-4 py-2">Provinsi</th>
            </tr>
          </thead>
          <tbody class="dark:text-slate-100">
            <tr v-for="item in kecamatanData" :key="item.id" class="border-t border-slate-200 dark:border-slate-800">
              <td class="px-4 py-2">{{ item.id }}</td>
              <td class="px-4 py-2 font-mono">{{ item.kode || '-' }}</td>
              <td class="px-4 py-2">{{ item.nama }}</td>
              <td class="px-4 py-2 font-mono">{{ item.kota_kab?.kode || '-' }}</td>
              <td class="px-4 py-2">{{ item.kota_kab?.nama }}</td>
              <td class="px-4 py-2 font-mono">{{ item.kota_kab?.provinsi?.kode || '-' }}</td>
              <td class="px-4 py-2">{{ item.kota_kab?.provinsi?.nama }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="applied && (pagination.prev || pagination.next)" class="mt-4 flex justify-center gap-2">
        <Button :disabled="!pagination.prev" @click="goToPage(pagination.prev)">Prev</Button>
        <span class="rounded border border-slate-300 px-3 py-1 text-slate-800 dark:border-slate-700 dark:text-slate-100">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <Button :disabled="!pagination.next" @click="goToPage(pagination.next)">Next</Button>
      </div>

      <div v-if="applied && !loading && kecamatanData.length === 0" class="py-4 text-center text-gray-500 dark:text-slate-400">
        Tidak ada data
      </div>
    </div>
  </AppLayout>
</template>
