<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref, watch } from 'vue'
import axios from 'axios'
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
  provinsi?: ProvinsiItem | null
}

interface KecamatanItem {
  id: number
  kode?: string | null
  nama: string
}

interface PagoruanItem {
  id: number
  kode?: string | null
  nama: string
}

const props = defineProps<{
  anggota: {
    id: string
    nomor_anggota: string
    nama_lengkap: string
    jenis_identitas: 'ktp' | 'kartu_pelajar'
    nomor_identitas: string | null
    tanggal_lahir: string
    alamat: string | null
    telepon: string | null
    golongan_darah: string | null
    tanggal_gabung: string | null
    masa_berlaku_sampai: string | null
    status_pengajuan: string
    catatan_verifikasi: string | null
    foto_path: string | null
    foto_url?: string | null
    kk_path: string | null
    dokumen_identitas_path: string | null
    kota_kab_id: number
    kecamatan_id: number | null
    pagoruan_id: number | null
    kota_kab?: { id: number; kode?: string | null; nama: string; provinsi?: ProvinsiItem | null } | null
  }
  provinsi: ProvinsiItem[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
  { title: 'Edit Anggota', href: `/anggota/${props.anggota.id}/edit` },
]

const form = useForm({
  nama_lengkap: props.anggota.nama_lengkap ?? '',
  jenis_identitas: props.anggota.jenis_identitas ?? 'ktp',
  nomor_identitas: props.anggota.nomor_identitas ?? '',
  tanggal_lahir: props.anggota.tanggal_lahir ?? '',
  alamat: props.anggota.alamat ?? '',
  provinsi_id: props.anggota.kota_kab?.provinsi?.id ?? '' as number | '',
  kota_kab_id: props.anggota.kota_kab_id ?? '' as number | '',
  kecamatan_id: props.anggota.kecamatan_id ?? '' as number | '',
  pagoruan_id: props.anggota.pagoruan_id ?? '' as number | '',
  telepon: props.anggota.telepon ?? '',
  golongan_darah: props.anggota.golongan_darah ?? '',
  tanggal_gabung: props.anggota.tanggal_gabung ?? '',
  masa_berlaku_sampai: props.anggota.masa_berlaku_sampai ?? '',
  status_pengajuan: props.anggota.status_pengajuan ?? 'draft_dpd',
  catatan_verifikasi: props.anggota.catatan_verifikasi ?? '',
  foto: null as File | null,
  kk: null as File | null,
  dokumen_identitas: null as File | null,
})

const kotaKabList = ref<KotaKabItem[]>([])
const kecamatanList = ref<KecamatanItem[]>([])
const pagoruanList = ref<PagoruanItem[]>([])
const initializing = ref(true)

const selectedProvinsi = computed(() => props.provinsi.find((item) => item.id === form.provinsi_id))
const selectedKotaKab = computed(() => kotaKabList.value.find((item) => item.id === form.kota_kab_id))
const nomorPreview = computed(() => {
  if (!selectedProvinsi.value?.kode || !selectedKotaKab.value?.kode) {
    return props.anggota.nomor_anggota
  }

  return `${selectedProvinsi.value.kode}.${selectedKotaKab.value.kode}.xxxxxx`
})

watch(
  () => form.provinsi_id,
  async (value) => {
    const previousKota = form.kota_kab_id
    form.kota_kab_id = ''
    form.kecamatan_id = ''
    form.pagoruan_id = ''
    kotaKabList.value = []
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) {
      return
    }

    const response = await axios.get(`/api/kota-kab/${value}`)
    kotaKabList.value = response.data

    if (initializing.value && previousKota) {
      form.kota_kab_id = previousKota
    }
  },
  { immediate: true },
)

watch(
  () => form.kota_kab_id,
  async (value) => {
    const previousKecamatan = form.kecamatan_id
    form.kecamatan_id = ''
    form.pagoruan_id = ''
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) {
      return
    }

    const response = await axios.get(`/api/kecamatan/${value}?paginate=0`)
    kecamatanList.value = response.data.data ?? []

    if (initializing.value && previousKecamatan) {
      form.kecamatan_id = previousKecamatan
    }
  },
  { immediate: true },
)

watch(
  () => form.kecamatan_id,
  async (value) => {
    const previousPagoruan = form.pagoruan_id
    form.pagoruan_id = ''
    pagoruanList.value = []

    if (!value) {
      initializing.value = false
      return
    }

    const response = await axios.get(`/api/pagoruan/${value}`)
    pagoruanList.value = response.data

    if (initializing.value && previousPagoruan) {
      form.pagoruan_id = previousPagoruan
    }

    initializing.value = false
  },
  { immediate: true },
)

function handleFile(field: 'foto' | 'kk' | 'dokumen_identitas', event: Event) {
  const target = event.target as HTMLInputElement
  form[field] = target.files?.[0] ?? null
}

function submit() {
  form.transform((data) => ({
    ...data,
    _method: 'put',
  })).post(`/anggota/${props.anggota.id}`, {
    forceFormData: true,
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit Anggota" />

    <div class="mx-auto max-w-6xl space-y-6 p-6">
      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h1 class="text-3xl font-semibold text-slate-950 dark:text-white">Edit Anggota</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">
              Nomor anggota saat ini: <span class="font-semibold text-slate-900 dark:text-white">{{ anggota.nomor_anggota }}</span>
            </p>
          </div>
          <div class="rounded-2xl border border-[#d7ebd9] bg-[#eef8ef] px-4 py-3 text-sm font-medium text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]">
            Struktur nomor: {{ nomorPreview }}
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Data Pribadi</h2>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Lengkap</label>
            <input v-model="form.nama_lengkap" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            <p v-if="form.errors.nama_lengkap" class="mt-1 text-sm text-red-600">{{ form.errors.nama_lengkap }}</p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Jenis Identitas</label>
              <select v-model="form.jenis_identitas" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="ktp">KTP</option>
                <option value="kartu_pelajar">Kartu Pelajar</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nomor Identitas</label>
              <input v-model="form.nomor_identitas" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal Lahir</label>
              <input v-model="form.tanggal_lahir" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Telepon</label>
              <input v-model="form.telepon" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Alamat</label>
            <textarea v-model="form.alamat" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
          </div>

          <div class="grid gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Golongan Darah</label>
              <select v-model="form.golongan_darah" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal Gabung</label>
              <input v-model="form.tanggal_gabung" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Masa Berlaku Sampai</label>
              <input v-model="form.masa_berlaku_sampai" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
          </div>
        </section>

        <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Struktur Organisasi & Dokumen</h2>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">DPW Provinsi</label>
              <select v-model="form.provinsi_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih DPW</option>
                <option v-for="item in provinsi" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">DPD Kota/Kab</label>
              <select v-model="form.kota_kab_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih DPD</option>
                <option v-for="item in kotaKabList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">DPC Kecamatan</label>
              <select v-model="form.kecamatan_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih DPC</option>
                <option v-for="item in kecamatanList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Pagoruan / Perguruan</label>
              <select v-model="form.pagoruan_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih Pagoruan</option>
                <option v-for="item in pagoruanList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Foto Baru</label>
              <input type="file" accept="image/*" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @change="handleFile('foto', $event)" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">KK Baru</label>
              <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @change="handleFile('kk', $event)" />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Opsional. Boleh upload KK saja atau dikosongkan.</p>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Dokumen Identitas Baru</label>
              <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @change="handleFile('dokumen_identitas', $event)" />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Opsional. Boleh upload KTP atau Kartu Pelajar saja tanpa KK.</p>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div v-if="anggota.foto_url" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
              <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Foto saat ini</p>
              <img :src="anggota.foto_url" class="mt-3 h-40 w-full rounded-xl object-cover" />
            </div>
            <div class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
              <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Dokumen saat ini</p>
              <a v-if="anggota.kk_path" :href="`/storage/${anggota.kk_path}`" target="_blank" class="block text-sm text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat KK</a>
              <a v-if="anggota.dokumen_identitas_path" :href="`/storage/${anggota.dokumen_identitas_path}`" target="_blank" class="block text-sm text-[#0b6b31] underline dark:text-[#9fe7a8]">Lihat Dokumen Identitas</a>
              <p v-if="!anggota.kk_path && !anggota.dokumen_identitas_path" class="text-sm text-slate-500 dark:text-slate-400">Belum ada dokumen tersimpan.</p>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Status Pengajuan</label>
            <select v-model="form.status_pengajuan" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
              <option value="draft_dpd">Draft DPD</option>
              <option value="diajukan_ke_dpw">Diajukan ke DPW</option>
              <option value="diverifikasi_dpw">Diverifikasi DPW</option>
              <option value="diajukan_ke_dpp">Diajukan ke DPP</option>
              <option value="disetujui_dpp">Disetujui DPP</option>
              <option value="ditolak">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan Verifikasi</label>
            <textarea v-model="form.catatan_verifikasi" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
          </div>
        </section>

        <div class="xl:col-span-2 flex justify-end">
          <Button type="submit" :disabled="form.processing">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Update Anggota</span>
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
