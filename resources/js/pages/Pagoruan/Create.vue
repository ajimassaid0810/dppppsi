<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
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

const props = defineProps<{
  provinsiList: ProvinsiItem[]
  kotaKabList: KotaKabItem[]
  kecamatanList: KecamatanItem[]
  fixedKecamatan?: FixedKecamatan | null
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Pagoruan', href: '/pagoruan' },
  { title: 'Tambah', href: '/pagoruan/create' },
]

const isScopedToKecamatan = computed(() => !!props.fixedKecamatan)
const kotaKabList = ref<KotaKabItem[]>(props.kotaKabList)
const kecamatanList = ref<KecamatanItem[]>(props.kecamatanList)
const loadingKotaKab = ref(false)
const loadingKecamatan = ref(false)

const form = useForm({
  kode: '',
  nama: '',
  provinsi_id: props.fixedKecamatan?.kota_kab?.provinsi?.id ?? null as number | null,
  kota_kab_id: props.fixedKecamatan?.kota_kab?.id ?? null as number | null,
  kecamatan_id: props.fixedKecamatan?.id ?? null as number | null,
  alamat: '',
  telepon: '',
  nama_pimpinan: '',
  nama_pelatih: '',
  is_active: true,
})

watch(
  () => form.provinsi_id,
  async (value, oldValue) => {
    if (isScopedToKecamatan.value || value === oldValue) {
      return
    }

    form.kota_kab_id = null
    form.kecamatan_id = null
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
  },
)

watch(
  () => form.kota_kab_id,
  async (value, oldValue) => {
    if (isScopedToKecamatan.value || value === oldValue) {
      return
    }

    form.kecamatan_id = null

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
  },
)

function submit() {
  form.post('/pagoruan')
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Tambah Pagoruan" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
      <div>
        <h1 class="text-3xl font-semibold text-slate-950 dark:text-white">Tambah Pagoruan</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">
          Tambahkan pagoruan baru dan kaitkan ke kecamatan yang sesuai.
        </p>
      </div>

      <div
        v-if="fixedKecamatan"
        class="rounded-3xl border border-[#d7ebd9] bg-[#eef8ef] p-5 text-sm text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]"
      >
        Pagoruan yang Anda tambah akan otomatis masuk ke kecamatan
        <span class="font-semibold">{{ fixedKecamatan.nama }}</span>
        <span v-if="fixedKecamatan.kota_kab">, {{ fixedKecamatan.kota_kab.nama }}</span>.
      </div>

      <form @submit.prevent="submit" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kode</label>
            <input
              v-model="form.kode"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
            <p v-if="form.errors.kode" class="mt-1 text-sm text-red-600">{{ form.errors.kode }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Pagoruan</label>
            <input
              v-model="form.nama"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
            <p v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</p>
          </div>
        </div>

        <div v-if="!isScopedToKecamatan" class="grid gap-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Provinsi</label>
            <select
              v-model="form.provinsi_id"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option :value="null">Pilih Provinsi</option>
              <option v-for="item in provinsiList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kota/Kab</label>
            <select
              v-model="form.kota_kab_id"
              :disabled="loadingKotaKab || !form.provinsi_id"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option :value="null">Pilih Kota/Kab</option>
              <option v-for="item in kotaKabList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kecamatan</label>
            <select
              v-model="form.kecamatan_id"
              :disabled="loadingKecamatan || !form.kota_kab_id"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            >
              <option :value="null">Pilih Kecamatan</option>
              <option v-for="item in kecamatanList" :key="item.id" :value="item.id">
                {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
              </option>
            </select>
            <p v-if="form.errors.kecamatan_id" class="mt-1 text-sm text-red-600">{{ form.errors.kecamatan_id }}</p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Pimpinan</label>
            <input
              v-model="form.nama_pimpinan"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
            <p v-if="form.errors.nama_pimpinan" class="mt-1 text-sm text-red-600">{{ form.errors.nama_pimpinan }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Pelatih</label>
            <input
              v-model="form.nama_pelatih"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
            <p v-if="form.errors.nama_pelatih" class="mt-1 text-sm text-red-600">{{ form.errors.nama_pelatih }}</p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Telepon</label>
            <input
              v-model="form.telepon"
              type="text"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
            />
            <p v-if="form.errors.telepon" class="mt-1 text-sm text-red-600">{{ form.errors.telepon }}</p>
          </div>

          <div class="flex items-end">
            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">
              <input v-model="form.is_active" type="checkbox" class="size-4 rounded border-slate-300" />
              Status aktif
            </label>
          </div>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Alamat</label>
          <textarea
            v-model="form.alamat"
            rows="4"
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
          />
          <p v-if="form.errors.alamat" class="mt-1 text-sm text-red-600">{{ form.errors.alamat }}</p>
        </div>

        <div class="flex justify-end gap-2">
          <Link href="/pagoruan">
            <Button type="button" variant="outline">Batal</Button>
          </Link>
          <Button type="submit" :disabled="form.processing">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan</span>
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
