<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { ref, watch } from 'vue'
import axios from 'axios'
import { computed } from 'vue'

const nikValid = computed(() => /^[0-9]{16}$/.test(form.nik))


defineProps<{
  provinsi: Array<{ id: string; nama: string }>
  cabang: Array<{ id: string; nama_cabang: string }>
}>()

// Form Inertia
const form = useForm({
  nama_lengkap: '',
  nik: '',
  tanggal_lahir: '',
  alamat: '',
  provinsi_id: '',
  kota_kab_id: '',
  kecamatan_id: '',
  kelurahan_id: '',
  telepon: '',
  foto: null as File | null,
  tanggal_gabung: '',
  masa_berlaku: '',
  perguruan: '',
  golongan_darah: '',
  cabang_id: '',
})

// Data select dependent
const kotaKabupaten = ref<Array<{ id: string; nama: string }>>([])
const kecamatan = ref<Array<{ id: string; nama: string }>>([])
const kelurahan = ref<Array<{ id: string; nama: string }>>([])

// Watch provinsi -> kota/kabupaten
watch(() => form.provinsi_id, async (val) => {
  if (!val) return
  try {
    const res = await axios.get(`/api/kota-kab/${val}`)
    kotaKabupaten.value = res.data
    kecamatan.value = []
    kelurahan.value = []
    form.kota_kab_id = ''
    form.kecamatan_id = ''
    form.kelurahan_id = ''
  } catch (e) {
    console.error('Error fetching kota/kab:', e)
  }
})

// Watch kota/kabupaten -> kecamatan
watch(() => form.kota_kab_id, async (val) => {
  if (!val) return
  try {
    const res = await axios.get(`/api/kecamatan/${val}`)
    kecamatan.value = res.data
    kelurahan.value = []
    form.kecamatan_id = ''
    form.kelurahan_id = ''
  } catch (e) {
    console.error('Error fetching kecamatan:', e)
  }
})

// Watch kecamatan -> kelurahan
watch(() => form.kecamatan_id, async (val) => {
  if (!val) return
  try {
    const res = await axios.get(`/api/kelurahan/${val}`)
    kelurahan.value = res.data
    form.kelurahan_id = ''
  } catch (e) {
    console.error('Error fetching kelurahan:', e)
  }
})

// Handle foto upload
function handleFotoUpload(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.foto = target.files[0]
  }
}

// Submit form
function submit() {
  form.post('/anggota', {
    onSuccess: () => {
      console.log('Data berhasil disimpan')
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    },
  })
}

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
  { title: 'Tambah Anggota', href: '/anggota/create' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Tambah Anggota" />

    <!-- Flash messages -->
    <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-700 p-2 rounded mb-4">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="bg-red-100 text-red-700 p-2 rounded mb-4">
      {{ $page.props.flash.error }}
    </div>

    <form @submit.prevent="submit" enctype="multipart/form-data" class="grid grid-cols-2 gap-6 p-6">
      <!-- Column 1 -->
      <div class="space-y-4">
        <div>
          <label>Nama Lengkap</label>
          <input v-model="form.nama_lengkap" type="text" class="input w-full" />
        </div>
        <div>
          <label>NIK</label>
          <input v-model="form.nik" type="text" class="input w-full" />
        </div>
        <div>
          <label>Tanggal Lahir</label>
          <input v-model="form.tanggal_lahir" type="date" class="input w-full" />
        </div>
        <div>
          <label>Alamat</label>
          <textarea v-model="form.alamat" class="input w-full"></textarea>
        </div>
        <div>
          <label>DPW Provinsi</label>
          <select v-model="form.provinsi_id" class="input w-full">
            <option value="">-- Pilih DPW Provinsi --</option>
            <option v-for="p in provinsi" :key="p.id" :value="p.id">{{ p.nama }}</option>
          </select>
        </div>
        <div>
          <label>DPD Kota / Kabupaten</label>
          <select v-model="form.kota_kab_id" class="input w-full">
            <option value="">-- Pilih DPD Kota/Kab --</option>
            <option v-for="k in kotaKabupaten" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
        </div>
        <div>
          <label>DPC Kecamatan</label>
          <select v-model="form.kecamatan_id" class="input w-full">
            <option value="">-- Pilih DPC Kecamatan --</option>
            <option v-for="k in kecamatan" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
        </div>
        
      </div>

      <!-- Column 2 -->
      <div class="space-y-4">
        <div>
          <label>Paguroan</label>
          <select v-model="form.kelurahan_id" class="input w-full">
            <option value="">-- Pilih Paguroan --</option>
            <option v-for="k in kelurahan" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
        </div>
        <div>
          <label>Golongan Darah</label>
          <select v-model="form.golongan_darah" class="input w-full">
            <option value="">-- Pilih Golongan Darah --</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="AB">AB</option>
            <option value="O">O</option>
          </select>
        </div>
        <div>
          <label>Tanggal Gabung</label>
          <input v-model="form.tanggal_gabung" type="date" class="input w-full" />
        </div>
        <div>
          <label>Masa Berlaku</label>
          <input v-model="form.masa_berlaku" type="date" class="input w-full" />
        </div>
        <div>
          <label>Perguruan</label>
          <input v-model="form.perguruan" type="text" class="input w-full" />
        </div>
        <div>
          <label>Telepon</label>
          <input v-model="form.telepon" type="text" class="input w-full" />
        </div>
        <div>
          <label>Foto</label>
          <input type="file" @change="handleFotoUpload" class="input w-full" />
        </div>
       
      </div>
       <Button type="submit" class="w-full" :disabled="form.processing || !nikValid">
  Simpan
</Button>
    </form>
  </AppLayout>
</template>
