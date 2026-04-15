<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { ref, watch } from 'vue'
import axios from 'axios'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  provinsiList: { id: number; nama: string }[]
  filters: { provinsi_id?: number; kota_kab_id?: number }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPC Kecamatan', href: '/kecamatan' },
]

// state filter
const provinsiId = ref(props.filters.provinsi_id || null)
const kotaKabId = ref(props.filters.kota_kab_id || null)
const applied = ref(false)
const kecamatanData = ref<any[]>([])
const kotaKabList = ref<{id: number, nama: string}[]>([])
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
  kotaKabId.value = null
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

    <div class="p-6 space-y-6 max-w-5xl mx-auto">
      <!-- Filter -->
      <div class="flex gap-4 items-end">
        <div class="flex-1">
          <label class="block mb-1 font-medium">Provinsi</label>
          <select v-model="provinsiId" class="w-full border rounded px-3 py-2">
            <option disabled value="">-- Pilih Provinsi --</option>
            <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
              {{ prov.nama }}
            </option>
          </select>
        </div>

        <div class="flex-1">
          <label class="block mb-1 font-medium">Kota/Kab</label>
          <select v-model="kotaKabId" class="w-full border rounded px-3 py-2">
            <option disabled value="">-- Pilih Kota/Kab --</option>
            <option v-for="kota in kotaKabList" :key="kota.id" :value="kota.id">
              {{ kota.nama }}
            </option>
          </select>
        </div>

        <Button @click="applyFilters">Apply</Button>

        <Link href="/dpc-kecamatan/create">
          <Button>Tambah DPC Kecamatan</Button>
        </Link>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-4">Loading...</div>

      <!-- Error -->
      <div v-if="error" class="text-red-500 text-center py-4">{{ error }}</div>

      <!-- Table -->
      <div v-if="applied && !loading && kecamatanData.length" class="overflow-x-auto border rounded-lg">
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Nama Kecamatan</th>
              <th class="px-4 py-2">Kota/Kab</th>
              <th class="px-4 py-2">Provinsi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in kecamatanData" :key="item.id" class="border-t">
              <td class="px-4 py-2">{{ item.id }}</td>
              <td class="px-4 py-2">{{ item.nama }}</td>
              <td class="px-4 py-2">{{ item.kota_kab?.nama }}</td>
              <td class="px-4 py-2">{{ item.kota_kab?.provinsi?.nama }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="applied && (pagination.prev || pagination.next)" class="flex justify-center gap-2 mt-4">
        <Button :disabled="!pagination.prev" @click="goToPage(pagination.prev)">Prev</Button>
        <span class="px-3 py-1 border rounded">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <Button :disabled="!pagination.next" @click="goToPage(pagination.next)">Next</Button>
      </div>

      <div v-if="applied && !loading && kecamatanData.length === 0" class="text-center text-gray-500 py-4">
        Tidak ada data
      </div>
    </div>
  </AppLayout>
</template>
