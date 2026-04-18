<script setup lang="ts">
import axios from 'axios'
import { computed, reactive, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRight, Check, Upload, X } from 'lucide-vue-next'

import InputError from '@/components/InputError.vue'
import PublicLayout from '@/components/public/PublicLayout.vue'
import type { KecamatanItem, KotaKabItem, PagoruanItem, ProvinsiItem, SelectOption } from '@/pages/Anggota/types'
import type { PublicSite } from '@/pages/Public/types'

const props = defineProps<{
  site: PublicSite
  options: {
    provinsi: ProvinsiItem[]
    jenis_identitas: SelectOption[]
    golongan_darah: SelectOption[]
  }
}>()

const navLinks = [
  { label: 'Home', href: '/' },
  { label: 'Daftar Anggota', href: '/pendaftaran-anggota' },
  { label: 'Cek Pengajuan', href: '/cek-pengajuan' },
]

const currentStep = ref(1)
const totalSteps = 4
const kotaKabList = ref<KotaKabItem[]>([])
const kecamatanList = ref<KecamatanItem[]>([])
const pagoruanList = ref<PagoruanItem[]>([])
const previews = reactive<Record<string, string | null>>({
  foto: null,
  kk: null,
  dokumen_identitas: null,
})
const clientErrors = reactive<Record<string, string>>({})

const form = useForm<any>({
  nama_lengkap: '',
  jenis_identitas: props.options.jenis_identitas[0]?.value ?? 'ktp',
  nomor_identitas: '',
  tanggal_lahir: '',
  alamat: '',
  telepon: '',
  email: '',
  golongan_darah: '',
  provinsi_id: null as number | null,
  kota_kab_id: null as number | null,
  kecamatan_id: null as number | null,
  pagoruan_id: null as number | null,
  foto: null as File | null,
  kk: null as File | null,
  dokumen_identitas: null as File | null,
})

const steps = [
  { number: 1, title: 'Identitas', caption: 'Biodata pelamar' },
  { number: 2, title: 'Wilayah', caption: 'DPW, DPD, DPC, pagoruan' },
  { number: 3, title: 'Dokumen', caption: 'Upload pendukung' },
  { number: 4, title: 'Review', caption: 'Periksa sebelum kirim' },
]

watch(
  () => form.provinsi_id,
  async (value) => {
    form.kota_kab_id = null
    form.kecamatan_id = null
    form.pagoruan_id = null
    kotaKabList.value = []
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) return

    const response = await axios.get(`/api/kota-kab/${value}`)
    kotaKabList.value = response.data
  },
)

watch(
  () => form.kota_kab_id,
  async (value) => {
    form.kecamatan_id = null
    form.pagoruan_id = null
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) return

    const response = await axios.get(`/api/kecamatan/${value}?paginate=0`)
    kecamatanList.value = response.data.data ?? []
  },
)

watch(
  () => form.kecamatan_id,
  async (value) => {
    form.pagoruan_id = null
    pagoruanList.value = []

    if (!value) return

    const response = await axios.get(`/api/pagoruan/${value}`)
    pagoruanList.value = response.data
  },
)

const selectedProvinsi = computed(() => props.options.provinsi.find((item) => item.id === form.provinsi_id) ?? null)
const selectedKotaKab = computed(() => kotaKabList.value.find((item) => item.id === form.kota_kab_id) ?? null)
const selectedKecamatan = computed(() => kecamatanList.value.find((item) => item.id === form.kecamatan_id) ?? null)
const selectedPagoruan = computed(() => pagoruanList.value.find((item) => item.id === form.pagoruan_id) ?? null)

function resetClientErrors() {
  Object.keys(clientErrors).forEach((key) => delete clientErrors[key])
}

function fieldError(key: string) {
  return clientErrors[key] || form.errors[key]
}

function validateStep(step: number) {
  resetClientErrors()

  if (step === 1) {
    if (!form.nama_lengkap) clientErrors.nama_lengkap = 'Nama lengkap wajib diisi.'
    if (!form.jenis_identitas) clientErrors.jenis_identitas = 'Jenis identitas wajib dipilih.'
    if (!form.nomor_identitas) clientErrors.nomor_identitas = 'Nomor identitas wajib diisi.'
    if (!form.tanggal_lahir) clientErrors.tanggal_lahir = 'Tanggal lahir wajib diisi.'
    if (!form.telepon) clientErrors.telepon = 'Telepon wajib diisi.'
    if (!form.alamat) clientErrors.alamat = 'Alamat wajib diisi.'
  }

  if (step === 2) {
    if (!form.provinsi_id) clientErrors.provinsi_id = 'DPW provinsi wajib dipilih.'
    if (!form.kota_kab_id) clientErrors.kota_kab_id = 'DPD kota/kab wajib dipilih.'
  }

  if (step === 3) {
    if (!form.foto) clientErrors.foto = 'Foto wajib diunggah.'
    if (!form.kk) clientErrors.kk = 'KK wajib diunggah.'
    if (!form.dokumen_identitas) clientErrors.dokumen_identitas = 'Dokumen identitas wajib diunggah.'
  }

  return Object.keys(clientErrors).length === 0
}

function firstErrorStep() {
  if (['nama_lengkap', 'jenis_identitas', 'nomor_identitas', 'tanggal_lahir', 'telepon', 'alamat'].some((key) => clientErrors[key])) return 1
  if (['provinsi_id', 'kota_kab_id'].some((key) => clientErrors[key])) return 2
  if (['foto', 'kk', 'dokumen_identitas'].some((key) => clientErrors[key])) return 3

  return 1
}

function goNext() {
  if (!validateStep(currentStep.value)) return
  currentStep.value = Math.min(totalSteps, currentStep.value + 1)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goPrev() {
  currentStep.value = Math.max(1, currentStep.value - 1)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function onFileChange(event: Event, key: 'foto' | 'kk' | 'dokumen_identitas') {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] ?? null

  form[key] = file

  if (!file) {
    previews[key] = null
    return
  }

  previews[key] = file.type.startsWith('image/') ? URL.createObjectURL(file) : null
}

function submit() {
  const valid = [1, 2, 3].every((step) => validateStep(step))

  if (!valid) {
    currentStep.value = firstErrorStep()
    return
  }

  form.post('/pendaftaran-anggota', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <PublicLayout :site="site" title="Pendaftaran Anggota" :nav-links="navLinks">
    <section class="mx-auto max-w-7xl px-4 py-10 md:px-6">
      <div class="grid gap-8 xl:grid-cols-[0.88fr_1.12fr]">
        <aside class="space-y-6">
          <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(160deg,#0f172a_0%,#0b6b31_55%,#123322_100%)] p-6 text-white shadow-[0_28px_90px_-50px_rgba(11,107,49,0.85)] dark:border-white/10 dark:shadow-none">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Pendaftaran Publik</p>
            <h1 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em]">Daftar Menjadi Anggota PPSI</h1>
            <p class="mt-4 text-sm leading-7 text-white/75">
              Isi form ini langkah demi langkah. Setelah terkirim, Anda akan menerima kode pengajuan untuk melacak proses verifikasi.
            </p>
            <div class="mt-6 grid gap-3">
              <div v-for="step in steps" :key="step.number" class="flex items-center gap-4 rounded-[1.4rem] border px-4 py-3" :class="step.number === currentStep ? 'border-white/30 bg-white/12' : 'border-white/10 bg-white/5'">
                <div class="flex size-10 items-center justify-center rounded-2xl text-sm font-bold" :class="step.number <= currentStep ? 'bg-[#f2c94c] text-[#0f172a]' : 'bg-white/10 text-white/75'">
                  {{ step.number }}
                </div>
                <div>
                  <p class="font-semibold">{{ step.title }}</p>
                  <p class="text-xs uppercase tracking-[0.16em] text-white/60">{{ step.caption }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Catatan</p>
            <ul class="mt-4 space-y-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
              <li class="flex gap-3"><Check class="mt-1 size-4 shrink-0 text-[#0b6b31] dark:text-[#9fe7a8]" />Pastikan DPD kota/kab dipilih sesuai wilayah pendaftaran Anda.</li>
              <li class="flex gap-3"><Check class="mt-1 size-4 shrink-0 text-[#0b6b31] dark:text-[#9fe7a8]" />Semua dokumen wajib diunggah sebelum pengajuan dapat dikirim.</li>
              <li class="flex gap-3"><Check class="mt-1 size-4 shrink-0 text-[#0b6b31] dark:text-[#9fe7a8]" />Pengajuan publik tidak dapat diedit lagi setelah submit.</li>
            </ul>
          </div>
        </aside>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16] md:p-8">
          <div class="mb-8 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Step {{ currentStep }} / {{ totalSteps }}</p>
              <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ steps[currentStep - 1].title }}</h2>
            </div>
            <span class="rounded-full bg-[#eef8ef] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]">Form Multi-step</span>
          </div>

          <div v-if="currentStep === 1" class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Lengkap <span class="text-red-600">*</span></label>
              <input v-model="form.nama_lengkap" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('nama_lengkap')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Jenis Identitas <span class="text-red-600">*</span></label>
              <select v-model="form.jenis_identitas" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option v-for="item in options.jenis_identitas" :key="item.value" :value="item.value">{{ item.label }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('jenis_identitas')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nomor Identitas <span class="text-red-600">*</span></label>
              <input v-model="form.nomor_identitas" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('nomor_identitas')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal Lahir <span class="text-red-600">*</span></label>
              <input v-model="form.tanggal_lahir" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('tanggal_lahir')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Telepon <span class="text-red-600">*</span></label>
              <input v-model="form.telepon" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('telepon')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
              <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('email')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Golongan Darah</label>
              <select v-model="form.golongan_darah" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option value="">Pilih golongan darah</option>
                <option v-for="item in options.golongan_darah" :key="item.value" :value="item.value">{{ item.label }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('golongan_darah')" />
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Alamat <span class="text-red-600">*</span></label>
              <textarea v-model="form.alamat" rows="5" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="fieldError('alamat')" />
            </div>
          </div>
          <div v-else-if="currentStep === 2" class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">DPW Provinsi <span class="text-red-600">*</span></label>
              <select v-model="form.provinsi_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">Pilih provinsi</option>
                <option v-for="item in options.provinsi" :key="item.id" :value="item.id">{{ item.nama }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('provinsi_id')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">DPD Kota/Kab <span class="text-red-600">*</span></label>
              <select v-model="form.kota_kab_id" :disabled="!form.provinsi_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">Pilih DPD kota/kab</option>
                <option v-for="item in kotaKabList" :key="item.id" :value="item.id">{{ item.nama }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('kota_kab_id')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">DPC Kecamatan</label>
              <select v-model="form.kecamatan_id" :disabled="!form.kota_kab_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">Pilih DPC kecamatan</option>
                <option v-for="item in kecamatanList" :key="item.id" :value="item.id">{{ item.nama }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('kecamatan_id')" />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Pagoruan</label>
              <select v-model="form.pagoruan_id" :disabled="!form.kecamatan_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">Pilih pagoruan</option>
                <option v-for="item in pagoruanList" :key="item.id" :value="item.id">{{ item.nama }}</option>
              </select>
              <InputError class="mt-2" :message="fieldError('pagoruan_id')" />
            </div>

            <div class="md:col-span-2 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-[#102019]">
              <p class="text-sm font-semibold text-slate-900 dark:text-white">Ringkasan Wilayah</p>
              <div class="mt-4 grid gap-3 text-sm leading-7 text-slate-600 md:grid-cols-2 dark:text-slate-300">
                <p><strong>DPW:</strong> {{ selectedProvinsi?.nama || '-' }}</p>
                <p><strong>DPD:</strong> {{ selectedKotaKab?.nama || '-' }}</p>
                <p><strong>DPC:</strong> {{ selectedKecamatan?.nama || '-' }}</p>
                <p><strong>Pagoruan:</strong> {{ selectedPagoruan?.nama || '-' }}</p>
              </div>
            </div>
          </div>
          <div v-else-if="currentStep === 3" class="grid gap-5 md:grid-cols-3">
            <div class="rounded-[1.6rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Foto <span class="text-red-600">*</span></label>
              <label class="flex min-h-48 cursor-pointer flex-col items-center justify-center gap-3 rounded-[1.5rem] border border-dashed border-slate-300 px-4 py-6 text-center dark:border-slate-700">
                <Upload class="size-6 text-[#0b6b31] dark:text-[#9fe7a8]" />
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Unggah foto</span>
                <input type="file" accept="image/*" class="hidden" @change="onFileChange($event, 'foto')" />
              </label>
              <div v-if="previews.foto" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                <img :src="previews.foto" class="h-52 w-full object-cover" alt="Preview foto" />
              </div>
              <InputError class="mt-2" :message="fieldError('foto')" />
            </div>

            <div class="rounded-[1.6rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-200">KK <span class="text-red-600">*</span></label>
              <label class="flex min-h-48 cursor-pointer flex-col items-center justify-center gap-3 rounded-[1.5rem] border border-dashed border-slate-300 px-4 py-6 text-center dark:border-slate-700">
                <Upload class="size-6 text-[#0b6b31] dark:text-[#9fe7a8]" />
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Unggah KK</span>
                <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="onFileChange($event, 'kk')" />
              </label>
              <div v-if="form.kk" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-[#102019] dark:text-slate-300">{{ form.kk.name }}</div>
              <InputError class="mt-2" :message="fieldError('kk')" />
            </div>

            <div class="rounded-[1.6rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Dokumen Identitas <span class="text-red-600">*</span></label>
              <label class="flex min-h-48 cursor-pointer flex-col items-center justify-center gap-3 rounded-[1.5rem] border border-dashed border-slate-300 px-4 py-6 text-center dark:border-slate-700">
                <Upload class="size-6 text-[#0b6b31] dark:text-[#9fe7a8]" />
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Unggah identitas</span>
                <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="onFileChange($event, 'dokumen_identitas')" />
              </label>
              <div v-if="form.dokumen_identitas" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-[#102019] dark:text-slate-300">{{ form.dokumen_identitas.name }}</div>
              <InputError class="mt-2" :message="fieldError('dokumen_identitas')" />
            </div>
          </div>
          <div v-else class="space-y-6">
            <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-[#102019]">
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Review Data</p>
              <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="space-y-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                  <p><strong>Nama:</strong> {{ form.nama_lengkap }}</p>
                  <p><strong>Identitas:</strong> {{ form.nomor_identitas }}</p>
                  <p><strong>Tanggal Lahir:</strong> {{ form.tanggal_lahir || '-' }}</p>
                  <p><strong>Telepon:</strong> {{ form.telepon }}</p>
                  <p><strong>Email:</strong> {{ form.email || '-' }}</p>
                  <p><strong>Golongan Darah:</strong> {{ form.golongan_darah || '-' }}</p>
                </div>
                <div class="space-y-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                  <p><strong>DPW:</strong> {{ selectedProvinsi?.nama || '-' }}</p>
                  <p><strong>DPD:</strong> {{ selectedKotaKab?.nama || '-' }}</p>
                  <p><strong>DPC:</strong> {{ selectedKecamatan?.nama || '-' }}</p>
                  <p><strong>Pagoruan:</strong> {{ selectedPagoruan?.nama || '-' }}</p>
                  <p><strong>Foto:</strong> {{ form.foto?.name || '-' }}</p>
                  <p><strong>KK:</strong> {{ form.kk?.name || '-' }}</p>
                  <p><strong>Dokumen Identitas:</strong> {{ form.dokumen_identitas?.name || '-' }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-[1.8rem] border border-[#d7ebd9] bg-[#eef8ef] p-6 text-sm leading-7 text-[#0b6b31] dark:border-[#234a33] dark:bg-[#123322] dark:text-[#d7f3db]">
              Setelah Anda menekan kirim, sistem akan membuat kode pengajuan unik. Simpan kode itu untuk melacak status verifikasi pengajuan Anda.
            </div>
          </div>

          <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
            <Link href="/" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">
              <X class="size-4" />
              Kembali ke landing
            </Link>

            <div class="flex flex-col gap-3 sm:flex-row">
              <button v-if="currentStep > 1" type="button" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#0b6b31] hover:text-[#0b6b31] dark:border-slate-700 dark:text-slate-200 dark:hover:border-[#9fe7a8] dark:hover:text-[#9fe7a8]" @click="goPrev">
                <ArrowLeft class="size-4" />
                Sebelumnya
              </button>

              <button v-if="currentStep < totalSteps" type="button" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-5 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]" @click="goNext">
                Berikutnya
                <ArrowRight class="size-4" />
              </button>

              <button v-else type="button" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-5 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" @click="submit">
                {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                <ArrowRight class="size-4" />
              </button>
            </div>
          </div>
        </section>
      </div>
    </section>
  </PublicLayout>
</template>
