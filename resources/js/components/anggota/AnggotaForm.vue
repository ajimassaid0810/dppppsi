<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'

import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import type { AnggotaFormOptions, AnggotaRecord, KecamatanItem, KotaKabItem, PagoruanItem, ProvinsiItem } from '@/pages/Anggota/types'
import { formatFileSize } from '@/pages/Anggota/types'

type UploadField = 'foto' | 'kk' | 'dokumen_identitas'

interface UploadPreviewState {
  objectUrl: string | null
  name: string | null
  sizeLabel: string | null
  isImage: boolean
}

interface ExistingFileState {
  url: string | null
  name: string | null
  isImage: boolean
}

const props = withDefaults(defineProps<{
  mode: 'create' | 'edit'
  options: AnggotaFormOptions
  anggota?: AnggotaRecord | null
}>(), {
  anggota: null,
})

const isEdit = computed(() => props.mode === 'edit')
const syncingFormFromProps = ref(false)

function buildFormState(anggota?: AnggotaRecord | null) {
  return {
    nama_lengkap: anggota?.nama_lengkap ?? '',
    jenis_identitas: anggota?.jenis_identitas ?? props.options.jenis_identitas[0]?.value ?? 'ktp',
    nomor_identitas: anggota?.nomor_identitas ?? '',
    tanggal_lahir: anggota?.tanggal_lahir ?? '',
    alamat: anggota?.alamat ?? '',
    provinsi_id: anggota?.kota_kab?.provinsi?.id ?? null as number | null,
    kota_kab_id: anggota?.kota_kab_id ?? null as number | null,
    kecamatan_id: anggota?.kecamatan_id ?? null as number | null,
    pagoruan_id: anggota?.pagoruan_id ?? null as number | null,
    telepon: anggota?.telepon ?? '',
    golongan_darah: anggota?.golongan_darah ?? '',
    tanggal_gabung: anggota?.tanggal_gabung ?? '',
    masa_berlaku_sampai: anggota?.masa_berlaku_sampai ?? '',
    status_pengajuan: anggota?.status_pengajuan ?? props.options.status_pengajuan.find((item) => item.value === 'draft_dpd')?.value ?? props.options.status_pengajuan[0]?.value ?? 'draft_dpd',
    catatan_verifikasi: anggota?.catatan_verifikasi ?? '',
    foto: null as File | null,
    kk: null as File | null,
    dokumen_identitas: null as File | null,
  }
}

const form = useForm(buildFormState(props.anggota))

const kotaKabList = ref<KotaKabItem[]>([])
const kecamatanList = ref<KecamatanItem[]>([])
const pagoruanList = ref<PagoruanItem[]>([])
const loadingKotaKab = ref(false)
const loadingKecamatan = ref(false)
const loadingPagoruan = ref(false)

const fotoInput = ref<HTMLInputElement | null>(null)
const kkInput = ref<HTMLInputElement | null>(null)
const dokumenIdentitasInput = ref<HTMLInputElement | null>(null)

const uploadPreview = reactive<Record<UploadField, UploadPreviewState>>({
  foto: { objectUrl: null, name: null, sizeLabel: null, isImage: false },
  kk: { objectUrl: null, name: null, sizeLabel: null, isImage: false },
  dokumen_identitas: { objectUrl: null, name: null, sizeLabel: null, isImage: false },
})

let kotaKabRequestToken = 0
let kecamatanRequestToken = 0
let pagoruanRequestToken = 0

const selectedProvinsi = computed<ProvinsiItem | null>(() => {
  return props.options.provinsi.find((item) => item.id === form.provinsi_id) ?? null
})

const selectedKotaKab = computed<KotaKabItem | null>(() => {
  return kotaKabList.value.find((item) => item.id === form.kota_kab_id) ?? null
})

const nomorPreview = computed(() => {
  if (!selectedProvinsi.value?.kode || !selectedKotaKab.value?.kode) {
    return props.anggota?.nomor_anggota ?? 'Otomatis setelah data disimpan'
  }

  const nomorUrut = isEdit.value && props.anggota
    ? String(props.anggota.nomor_urut_dpd).padStart(6, '0')
    : '000001'

  return `${selectedProvinsi.value.kode}.${selectedKotaKab.value.kode}.${nomorUrut}`
})

const errorSummary = computed(() => Object.values(form.errors).filter(Boolean))
const photoDisplayUrl = computed(() => uploadPreview.foto.objectUrl ?? props.anggota?.foto_url ?? null)
const kkExistingFile = computed(() => buildExistingFileState(props.anggota?.kk_url ?? null, props.anggota?.kk_path ?? null))
const dokumenIdentitasExistingFile = computed(() => buildExistingFileState(props.anggota?.dokumen_identitas_url ?? null, props.anggota?.dokumen_identitas_path ?? null))

onMounted(async () => {
  if (isEdit.value) {
    return
  }

  if (form.provinsi_id) {
    await loadKotaKab(form.provinsi_id)
  }

  if (form.kota_kab_id) {
    await loadKecamatan(form.kota_kab_id)
  }

  if (form.kecamatan_id) {
    await loadPagoruan(form.kecamatan_id)
  }
})

onBeforeUnmount(() => {
  revokePreview('foto')
  revokePreview('kk')
  revokePreview('dokumen_identitas')
})

watch(
  () => form.provinsi_id,
  async (value, oldValue) => {
    if (syncingFormFromProps.value || value === oldValue) {
      return
    }

    form.kota_kab_id = null
    form.kecamatan_id = null
    form.pagoruan_id = null
    kotaKabList.value = []
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) {
      return
    }

    await loadKotaKab(value)
  },
)

watch(
  () => form.kota_kab_id,
  async (value, oldValue) => {
    if (syncingFormFromProps.value || value === oldValue) {
      return
    }

    form.kecamatan_id = null
    form.pagoruan_id = null
    kecamatanList.value = []
    pagoruanList.value = []

    if (!value) {
      return
    }

    await loadKecamatan(value)
  },
)

watch(
  () => form.kecamatan_id,
  async (value, oldValue) => {
    if (syncingFormFromProps.value || value === oldValue) {
      return
    }

    form.pagoruan_id = null
    pagoruanList.value = []

    if (!value) {
      return
    }

    await loadPagoruan(value)
  },
)

watch(
  () => props.anggota,
  async (anggota) => {
    if (!isEdit.value || !anggota) {
      return
    }

    syncingFormFromProps.value = true

    const nextState = buildFormState(anggota)

    form.defaults(nextState)
    form.reset()
    form.clearErrors()
    revokePreview('foto')
    revokePreview('kk')
    revokePreview('dokumen_identitas')

    if (fotoInput.value) {
      fotoInput.value.value = ''
    }

    if (kkInput.value) {
      kkInput.value.value = ''
    }

    if (dokumenIdentitasInput.value) {
      dokumenIdentitasInput.value.value = ''
    }

    kotaKabList.value = []
    kecamatanList.value = []
    pagoruanList.value = []

    if (nextState.provinsi_id) {
      await loadKotaKab(nextState.provinsi_id)
    }

    if (nextState.kota_kab_id) {
      await loadKecamatan(nextState.kota_kab_id)
    }

    if (nextState.kecamatan_id) {
      await loadPagoruan(nextState.kecamatan_id)
    }

    syncingFormFromProps.value = false
  },
  { immediate: true },
)

async function loadKotaKab(provinsiId: number) {
  const requestToken = ++kotaKabRequestToken
  loadingKotaKab.value = true

  try {
    const response = await axios.get(`/api/kota-kab/${provinsiId}`)

    if (requestToken !== kotaKabRequestToken) {
      return
    }

    kotaKabList.value = response.data
  } catch (error) {
    if (requestToken !== kotaKabRequestToken) {
      return
    }

    console.error(error)
    kotaKabList.value = []
  } finally {
    if (requestToken === kotaKabRequestToken) {
      loadingKotaKab.value = false
    }
  }
}

async function loadKecamatan(kotaKabId: number) {
  const requestToken = ++kecamatanRequestToken
  loadingKecamatan.value = true

  try {
    const response = await axios.get(`/api/kecamatan/${kotaKabId}?paginate=0`)

    if (requestToken !== kecamatanRequestToken) {
      return
    }

    kecamatanList.value = response.data.data ?? []
  } catch (error) {
    if (requestToken !== kecamatanRequestToken) {
      return
    }

    console.error(error)
    kecamatanList.value = []
  } finally {
    if (requestToken === kecamatanRequestToken) {
      loadingKecamatan.value = false
    }
  }
}

async function loadPagoruan(kecamatanId: number) {
  const requestToken = ++pagoruanRequestToken
  loadingPagoruan.value = true

  try {
    const response = await axios.get(`/api/pagoruan/${kecamatanId}`)

    if (requestToken !== pagoruanRequestToken) {
      return
    }

    pagoruanList.value = response.data
  } catch (error) {
    if (requestToken !== pagoruanRequestToken) {
      return
    }

    console.error(error)
    pagoruanList.value = []
  } finally {
    if (requestToken === pagoruanRequestToken) {
      loadingPagoruan.value = false
    }
  }
}

function revokePreview(field: UploadField) {
  if (uploadPreview[field].objectUrl?.startsWith('blob:')) {
    URL.revokeObjectURL(uploadPreview[field].objectUrl)
  }

  uploadPreview[field].objectUrl = null
  uploadPreview[field].name = null
  uploadPreview[field].sizeLabel = null
  uploadPreview[field].isImage = false
}

function handleFileChange(field: UploadField, event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] ?? null

  form[field] = file
  revokePreview(field)

  if (!file) {
    return
  }

  uploadPreview[field].name = file.name
  uploadPreview[field].sizeLabel = formatFileSize(file.size)
  uploadPreview[field].isImage = file.type.startsWith('image/')
  uploadPreview[field].objectUrl = uploadPreview[field].isImage ? URL.createObjectURL(file) : null
}

function clearSelectedFile(field: UploadField) {
  form[field] = null
  revokePreview(field)

  const input = {
    foto: fotoInput.value,
    kk: kkInput.value,
    dokumen_identitas: dokumenIdentitasInput.value,
  }[field]

  if (input) {
    input.value = ''
  }
}

function buildExistingFileState(url: string | null, path: string | null): ExistingFileState {
  const candidate = path ?? url ?? ''

  return {
    url,
    name: extractFileName(candidate),
    isImage: isImageFile(candidate),
  }
}

function extractFileName(value: string | null) {
  if (!value) {
    return null
  }

  const normalized = value.split('?')[0] ?? value
  const segments = normalized.split('/')

  return segments[segments.length - 1] ?? normalized
}

function isImageFile(value: string | null) {
  if (!value) {
    return false
  }

  return /\.(jpg|jpeg|png|webp|gif)$/i.test(value)
}

function submit() {
  if (isEdit.value && props.anggota) {
    form
      .transform((data) => ({
        ...data,
        _method: 'put',
      }))
      .post(`/anggota/${props.anggota.id}`, {
        forceFormData: true,
        preserveScroll: true,
      })

    return
  }

  form.post('/anggota', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.22),_transparent_32%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.22),_transparent_24%),linear-gradient(135deg,_#f8fbf5_0%,_#ffffff_48%,_#eef8ef_100%)] p-6 shadow-[0_28px_80px_-44px_rgba(11,107,49,0.48)] dark:border-white/10 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_28%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.12),_transparent_18%),linear-gradient(135deg,_#0b1713_0%,_#102019_48%,_#16251d_100%)] dark:shadow-none">
      <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-4">
          <div class="inline-flex rounded-full border border-[#d7ebd9] bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]">
            Form Anggota
          </div>
          <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white md:text-4xl">
              {{ isEdit ? 'Perbarui Data Anggota' : 'Tambah Anggota Baru' }}
            </h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
              Form ini menyesuaikan struktur tabel `anggota`, termasuk wilayah, dokumen pendukung, dan status pengajuan. Kolom bertanda
              <span class="font-semibold text-red-600">*</span> wajib diisi.
            </p>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <div class="rounded-3xl border border-white/80 bg-white/85 p-5 backdrop-blur dark:border-white/10 dark:bg-white/5">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Nomor Anggota</p>
            <p class="mt-3 font-mono text-xl font-semibold text-[#0b6b31] dark:text-[#9fe7a8]">{{ nomorPreview }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">
              Nomor anggota dibentuk otomatis dari kode provinsi, kode DPD, dan nomor urut DPD.
            </p>
          </div>

          <div class="rounded-3xl border border-[#d7ebd9] bg-[#0b6b31] p-5 text-white dark:border-[#1f4932] dark:bg-[#0d2419]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Kondisi Form</p>
            <p class="mt-3 text-lg font-semibold">{{ form.processing ? 'Sedang menyimpan...' : 'Siap disimpan' }}</p>
            <p class="mt-2 text-sm leading-6 text-white/75">
              Pastikan wilayah berjenjang dipilih berurutan agar DPC dan pagoruan selalu sinkron.
            </p>
          </div>
        </div>
      </div>
    </section>

    <div v-if="errorSummary.length" class="rounded-3xl border border-red-200 bg-red-50 p-5 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">
      <p class="font-semibold">Form belum bisa disimpan.</p>
      <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
        <li v-for="message in errorSummary" :key="message">{{ message }}</li>
      </ul>
    </div>

    <form @submit.prevent="submit" class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
      <section class="space-y-6">
        <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Bagian 1</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Data Pribadi</h2>
          </div>

          <div class="grid gap-5">
            <div>
              <Label for="nama_lengkap">Nama Lengkap <span class="text-red-600">*</span></Label>
              <input id="nama_lengkap" v-model="form.nama_lengkap" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="form.errors.nama_lengkap" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <Label for="jenis_identitas">Jenis Identitas <span class="text-red-600">*</span></Label>
                <select id="jenis_identitas" v-model="form.jenis_identitas" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                  <option v-for="item in options.jenis_identitas" :key="item.value" :value="item.value">
                    {{ item.label }}
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.jenis_identitas" />
              </div>

              <div>
                <Label for="nomor_identitas">Nomor Identitas</Label>
                <input id="nomor_identitas" v-model="form.nomor_identitas" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
                <InputError class="mt-2" :message="form.errors.nomor_identitas" />
              </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <Label for="tanggal_lahir">Tanggal Lahir <span class="text-red-600">*</span></Label>
                <input id="tanggal_lahir" v-model="form.tanggal_lahir" type="date" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
                <InputError class="mt-2" :message="form.errors.tanggal_lahir" />
              </div>

              <div>
                <Label for="telepon">Telepon</Label>
                <input id="telepon" v-model="form.telepon" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
                <InputError class="mt-2" :message="form.errors.telepon" />
              </div>
            </div>

            <div>
              <Label for="alamat">Alamat</Label>
              <textarea id="alamat" v-model="form.alamat" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="form.errors.alamat" />
            </div>

            <div class="grid gap-5 md:grid-cols-3">
              <div>
                <Label for="golongan_darah">Golongan Darah</Label>
                <select id="golongan_darah" v-model="form.golongan_darah" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                  <option value="">Pilih golongan darah</option>
                  <option v-for="item in options.golongan_darah" :key="item.value" :value="item.value">
                    {{ item.label }}
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.golongan_darah" />
              </div>

              <div>
                <Label for="tanggal_gabung">Tanggal Gabung</Label>
                <input id="tanggal_gabung" v-model="form.tanggal_gabung" type="date" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
                <InputError class="mt-2" :message="form.errors.tanggal_gabung" />
              </div>

              <div>
                <Label for="masa_berlaku_sampai">Masa Berlaku Sampai</Label>
                <input id="masa_berlaku_sampai" v-model="form.masa_berlaku_sampai" type="date" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
                <InputError class="mt-2" :message="form.errors.masa_berlaku_sampai" />
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Bagian 2</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Struktur Organisasi</h2>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <Label for="provinsi_id">DPW Provinsi <span class="text-red-600">*</span></Label>
              <select id="provinsi_id" v-model="form.provinsi_id" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">Pilih DPW provinsi</option>
                <option v-for="item in options.provinsi" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.provinsi_id" />
            </div>

            <div>
              <Label for="kota_kab_id">DPD Kota/Kab <span class="text-red-600">*</span></Label>
              <select id="kota_kab_id" v-model="form.kota_kab_id" :disabled="loadingKotaKab || !form.provinsi_id" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">{{ loadingKotaKab ? 'Memuat DPD...' : 'Pilih DPD kota/kab' }}</option>
                <option v-for="item in kotaKabList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.kota_kab_id" />
            </div>

            <div>
              <Label for="kecamatan_id">DPC Kecamatan</Label>
              <select id="kecamatan_id" v-model="form.kecamatan_id" :disabled="loadingKecamatan || !form.kota_kab_id" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">{{ loadingKecamatan ? 'Memuat DPC...' : 'Pilih DPC kecamatan' }}</option>
                <option v-for="item in kecamatanList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.kecamatan_id" />
            </div>

            <div>
              <Label for="pagoruan_id">Pagoruan / Perguruan</Label>
              <select id="pagoruan_id" v-model="form.pagoruan_id" :disabled="loadingPagoruan || !form.kecamatan_id" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option :value="null">{{ loadingPagoruan ? 'Memuat pagoruan...' : 'Pilih pagoruan' }}</option>
                <option v-for="item in pagoruanList" :key="item.id" :value="item.id">
                  {{ item.kode ? `[${item.kode}] ` : '' }}{{ item.nama }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.pagoruan_id" />
            </div>
          </div>
        </div>
      </section>

      <section class="space-y-6">
        <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Bagian 3</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Foto & Dokumen</h2>
          </div>

          <div class="space-y-5">
            <div class="rounded-3xl border border-slate-200 p-4 dark:border-slate-700">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">Foto Anggota</p>
                  <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Format gambar, maksimal 2 MB. Jika diganti, preview baru muncul di sini.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Preview</span>
              </div>

              <div class="mt-4 space-y-4">
                <div class="overflow-hidden rounded-3xl border border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/30">
                  <img v-if="photoDisplayUrl" :src="photoDisplayUrl" class="h-72 w-full object-cover" />
                  <div v-else class="flex h-72 items-center justify-center text-sm text-slate-400">
                    Belum ada foto yang dipilih
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                  <input ref="fotoInput" type="file" accept="image/*" class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 file:mr-4 file:rounded-full file:border-0 file:bg-[#0b6b31] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0a5d2b] dark:border-slate-700 dark:bg-[#10261c] dark:text-white md:w-auto" @change="handleFileChange('foto', $event)" />
                  <Button v-if="uploadPreview.foto.name" type="button" variant="outline" @click="clearSelectedFile('foto')">
                    Batal Ganti Foto
                  </Button>
                </div>

                <p v-if="uploadPreview.foto.name" class="text-sm text-slate-500 dark:text-slate-400">
                  File baru: <span class="font-medium text-slate-900 dark:text-white">{{ uploadPreview.foto.name }}</span>
                  <span v-if="uploadPreview.foto.sizeLabel">({{ uploadPreview.foto.sizeLabel }})</span>
                </p>
                <InputError :message="form.errors.foto" />
              </div>
            </div>

            <div class="rounded-3xl border border-slate-200 p-4 dark:border-slate-700">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">Kartu Keluarga</p>
                  <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">JPG, PNG, atau PDF. Maksimal 4 MB.</p>
                </div>
                <a v-if="anggota?.kk_url" :href="anggota.kk_url" target="_blank" class="text-sm font-medium text-[#0b6b31] underline dark:text-[#9fe7a8]">
                  Lihat File Saat Ini
                </a>
              </div>

              <div class="mt-4 space-y-3">
                <input ref="kkInput" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 file:mr-4 file:rounded-full file:border-0 file:bg-[#0b6b31] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0a5d2b] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @change="handleFileChange('kk', $event)" />
                <div v-if="!uploadPreview.kk.name && kkExistingFile.url" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900/30">
                  <img v-if="kkExistingFile.isImage" :src="kkExistingFile.url" class="mb-3 h-44 w-full rounded-2xl object-contain bg-white p-2 dark:bg-slate-950" />
                  <p>File saat ini: <span class="font-medium text-slate-900 dark:text-white">{{ kkExistingFile.name || 'Dokumen KK' }}</span></p>
                </div>
                <div v-if="uploadPreview.kk.name" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900/30">
                  <img v-if="uploadPreview.kk.isImage && uploadPreview.kk.objectUrl" :src="uploadPreview.kk.objectUrl" class="mb-3 h-44 w-full rounded-2xl object-contain bg-white p-2 dark:bg-slate-950" />
                  <p>File pengganti: <span class="font-medium text-slate-900 dark:text-white">{{ uploadPreview.kk.name }}</span></p>
                  <p v-if="uploadPreview.kk.sizeLabel" class="mt-1 text-slate-500 dark:text-slate-400">{{ uploadPreview.kk.sizeLabel }}</p>
                  <Button class="mt-3" type="button" variant="outline" @click="clearSelectedFile('kk')">Batal Ganti KK</Button>
                </div>
                <InputError :message="form.errors.kk" />
              </div>
            </div>

            <div class="rounded-3xl border border-slate-200 p-4 dark:border-slate-700">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">Dokumen Identitas</p>
                  <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Unggah KTP atau Kartu Pelajar dalam format JPG, PNG, atau PDF.</p>
                </div>
                <a v-if="anggota?.dokumen_identitas_url" :href="anggota.dokumen_identitas_url" target="_blank" class="text-sm font-medium text-[#0b6b31] underline dark:text-[#9fe7a8]">
                  Lihat File Saat Ini
                </a>
              </div>

              <div class="mt-4 space-y-3">
                <input ref="dokumenIdentitasInput" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 file:mr-4 file:rounded-full file:border-0 file:bg-[#0b6b31] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0a5d2b] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @change="handleFileChange('dokumen_identitas', $event)" />
                <div v-if="!uploadPreview.dokumen_identitas.name && dokumenIdentitasExistingFile.url" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900/30">
                  <img v-if="dokumenIdentitasExistingFile.isImage" :src="dokumenIdentitasExistingFile.url" class="mb-3 h-44 w-full rounded-2xl object-contain bg-white p-2 dark:bg-slate-950" />
                  <p>File saat ini: <span class="font-medium text-slate-900 dark:text-white">{{ dokumenIdentitasExistingFile.name || 'Dokumen identitas' }}</span></p>
                </div>
                <div v-if="uploadPreview.dokumen_identitas.name" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-900/30">
                  <img v-if="uploadPreview.dokumen_identitas.isImage && uploadPreview.dokumen_identitas.objectUrl" :src="uploadPreview.dokumen_identitas.objectUrl" class="mb-3 h-44 w-full rounded-2xl object-contain bg-white p-2 dark:bg-slate-950" />
                  <p>File pengganti: <span class="font-medium text-slate-900 dark:text-white">{{ uploadPreview.dokumen_identitas.name }}</span></p>
                  <p v-if="uploadPreview.dokumen_identitas.sizeLabel" class="mt-1 text-slate-500 dark:text-slate-400">{{ uploadPreview.dokumen_identitas.sizeLabel }}</p>
                  <Button class="mt-3" type="button" variant="outline" @click="clearSelectedFile('dokumen_identitas')">Batal Ganti Dokumen</Button>
                </div>
                <InputError :message="form.errors.dokumen_identitas" />
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Bagian 4</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Status & Catatan</h2>
          </div>

          <div class="space-y-5">
            <div>
              <Label for="status_pengajuan">Status Pengajuan <span class="text-red-600">*</span></Label>
              <select id="status_pengajuan" v-model="form.status_pengajuan" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
                <option v-for="item in options.status_pengajuan" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.status_pengajuan" />
            </div>

            <div>
              <Label for="catatan_verifikasi">Catatan Verifikasi</Label>
              <textarea id="catatan_verifikasi" v-model="form.catatan_verifikasi" rows="5" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="form.errors.catatan_verifikasi" />
            </div>
          </div>

          <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <Button as-child variant="outline" class="w-full sm:order-1 sm:w-auto">
              <Link href="/anggota">Kembali</Link>
            </Button>
            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
              {{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Anggota' }}
            </Button>
          </div>
        </div>
      </section>
    </form>
  </div>
</template>
