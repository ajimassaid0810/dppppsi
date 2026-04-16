<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type AppPageProps, type BreadcrumbItem } from '@/types'

const props = defineProps<{
  provinsi: {
    id: number
    kode: string | null
    nama: string
    nama_ketua_dpw: string | null
    url_ttd_ketua: string | null
  }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPW Provinsi', href: '/dpw-provinsi' },
  { title: 'Edit Provinsi', href: `/dpw-provinsi/${props.provinsi.id}/edit` },
]

const page = usePage<AppPageProps>()
const flash = computed(() => page.props.flash ?? {})
const submitMessage = ref<string | null>(null)
const submitState = ref<'error' | 'success' | null>(null)

const form = useForm({
  _method: 'put' as const,
  nama: props.provinsi.nama ?? '',
  nama_ketua_dpw: props.provinsi.nama_ketua_dpw ?? '',
  ttd_ketua: null as File | null,
})

const previewUrl = computed(() => {
  if (form.ttd_ketua) {
    return URL.createObjectURL(form.ttd_ketua)
  }

  return props.provinsi.url_ttd_ketua
})

const validationMessages = computed(() => Object.values(form.errors))

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  form.ttd_ketua = target.files && target.files.length > 0 ? target.files[0] : null
}

function submit() {
  submitMessage.value = null
  submitState.value = null

  form.post(`/dpw-provinsi/${props.provinsi.id}`, {
    forceFormData: true,
    preserveScroll: true,
    onError: () => {
      submitState.value = 'error'
      submitMessage.value = 'Update gagal. Periksa kembali field yang ditandai merah.'
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Edit Provinsi" />

    <div class="mx-auto max-w-2xl space-y-6 p-6">
      <h1 class="mb-4 text-2xl font-bold">Edit DPW Provinsi</h1>

      <div
        v-if="flash.error || submitState === 'error'"
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
      >
        {{ flash.error || submitMessage }}
      </div>

      <div
        v-if="flash.info"
        class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300"
      >
        {{ flash.info }}
      </div>

      <div
        v-if="validationMessages.length"
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
      >
        <p class="font-semibold">Periksa kembali data berikut:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
          <li v-for="message in validationMessages" :key="message">{{ message }}</li>
        </ul>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="kode" class="mb-1 block font-medium">Kode DPW</label>
          <input
            id="kode"
            :value="props.provinsi.kode ?? '-'"
            type="text"
            readonly
            class="w-full cursor-not-allowed rounded border bg-muted/60 px-3 py-2 text-muted-foreground"
          />
          <p class="mt-1 text-sm text-muted-foreground">
            Kode DPW dikunci setelah dibuat dan tidak dapat diubah saat edit.
          </p>
        </div>

        <div>
          <label for="nama" class="mb-1 block font-medium">Nama Provinsi</label>
          <input
            id="nama"
            v-model="form.nama"
            type="text"
            class="w-full rounded border px-3 py-2"
            :class="{ 'border-red-500': form.errors.nama }"
            @input="form.clearErrors('nama')"
          />
          <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
            {{ form.errors.nama }}
          </div>
        </div>

        <div>
          <label for="nama_ketua_dpw" class="mb-1 block font-medium">Nama Ketua DPW</label>
          <input
            id="nama_ketua_dpw"
            v-model="form.nama_ketua_dpw"
            type="text"
            class="w-full rounded border px-3 py-2"
            :class="{ 'border-red-500': form.errors.nama_ketua_dpw }"
            @input="form.clearErrors('nama_ketua_dpw')"
          />
          <div v-if="form.errors.nama_ketua_dpw" class="mt-1 text-sm text-red-600">
            {{ form.errors.nama_ketua_dpw }}
          </div>
        </div>

        <div>
          <label for="ttd_ketua" class="mb-1 block font-medium">Tanda Tangan Ketua</label>
          <input
            id="ttd_ketua"
            type="file"
            accept="image/*"
            @change="onFileChange"
            class="w-full rounded border px-3 py-2"
            :class="{ 'border-red-500': form.errors.ttd_ketua }"
          />
          <div v-if="form.errors.ttd_ketua" class="mt-1 text-sm text-red-600">
            {{ form.errors.ttd_ketua }}
          </div>

          <div v-if="previewUrl" class="mt-2">
            <img :src="previewUrl" alt="Preview tanda tangan" class="h-24 rounded border" />
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <Link href="/dpw-provinsi">
            <Button variant="outline">Batal</Button>
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
