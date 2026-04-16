<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { ref, watch, reactive } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  provinsiList: { id: number; kode?: string | null; nama: string }[]
}>()

// Form data
const form = useForm({
  id: null as number | null,
  kode: '',
  nama: '',
  provinsi_id: null as number | null,
  kota_kab_id: null as number | null,
})

// Daftar kota/kab untuk provinsi terpilih
const kotaKabList = ref<{ id: number; kode?: string | null; nama: string }[]>([])
const loadingKota = ref(false)
const errorKota = ref<string | null>(null)

// Watch Provinsi → fetch kota/kab
watch(
  () => form.provinsi_id,
  async (provId) => {
    if (!provId) {
      kotaKabList.value = []
      form.kota_kab_id = null
      return
    }
    loadingKota.value = true
    errorKota.value = null
    try {
      const res = await axios.get(`/api/kota-kab/${provId}`)
      kotaKabList.value = res.data
      form.kota_kab_id = null
    } catch (err) {
      console.error(err)
      kotaKabList.value = []
      form.kota_kab_id = null
      errorKota.value = 'Gagal mengambil data Kota/Kab'
    } finally {
      loadingKota.value = false
    }
  },
  { immediate: true }
)

// Submit form
function submit() {
  form.post('/dpc-kecamatan', {
    onSuccess: () => {
      form.reset('kode', 'nama', 'provinsi_id', 'kota_kab_id')
    },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Tambah Kecamatan" />

    <div class="p-6 max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold">Tambah Kecamatan (DPC)</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="kode" class="block mb-1 font-medium">Kode DPC</label>
          <input
            v-model="form.kode"
            id="kode"
            type="text"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.kode }"
          />
          <div v-if="form.errors.kode" class="text-red-600 text-sm mt-1">{{ form.errors.kode }}</div>
        </div>

        <!-- Nama -->
        <div>
          <label for="nama" class="block mb-1 font-medium">Nama DPC Kecamatan</label>
          <input
            v-model="form.nama"
            id="nama"
            type="text"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.nama }"
          />
          <div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">{{ form.errors.nama }}</div>
        </div>

        <!-- Provinsi -->
        <div>
          <label for="provinsi_id" class="block mb-1 font-medium">DPW Provinsi</label>
          <select
            v-model="form.provinsi_id"
            id="provinsi_id"
            class="w-full border px-3 py-2 rounded"
          >
            <option :value="null">-- Pilih Provinsi --</option>
            <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
              {{ prov.kode ? `[${prov.kode}] ` : '' }}{{ prov.nama }}
            </option>
          </select>
        </div>

        <!-- Kota/Kab -->
        <div>
          <label for="kota_kab_id" class="block mb-1 font-medium">DPD Kota/Kab</label>
          <select
            v-model="form.kota_kab_id"
            id="kota_kab_id"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.kota_kab_id }"
            :disabled="loadingKota || kotaKabList.length === 0"
          >
            <option v-if="loadingKota" disabled>Loading...</option>
            <option v-else :value="null">-- Pilih Kota/Kab --</option>
            <option v-for="kota in kotaKabList" :key="kota.id" :value="kota.id">
              {{ kota.kode ? `[${kota.kode}] ` : '' }}{{ kota.nama }}
            </option>
          </select>
          <div v-if="errorKota" class="text-red-600 text-sm mt-1">{{ errorKota }}</div>
          <div v-if="form.errors.kota_kab_id" class="text-red-600 text-sm mt-1">{{ form.errors.kota_kab_id }}</div>
        </div>

        <div class="flex gap-2">
          <Button type="submit">Simpan</Button>
          <Link href="/kecamatan"><Button variant="outline">Batal</Button></Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
