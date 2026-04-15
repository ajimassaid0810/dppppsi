<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'
import { computed, ref } from 'vue'

const props = defineProps<{
  provinsi: {
    id: number
    nama: string
    nama_ketua_dpw: string | null
    url_ttd_ketua: string | null
  }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPW Provinsi', href: '/dpw-provinsi' },
  { title: 'Edit Provinsi', href: `/dpw-provinsi/${props.provinsi.id}/edit` },
]

const form = useForm({
  id: props.provinsi.id,
  nama: props.provinsi.nama ?? '',            // ← isi dari backend
  nama_ketua_dpw: props.provinsi.nama_ketua_dpw ?? '',
  ttd_ketua: null as File | null,             // file baru (kalau upload)
})


const previewUrl = computed(() => {
  if (form.ttd_ketua) {
    return URL.createObjectURL(form.ttd_ketua) // preview file baru
  }
  return props.provinsi.url_ttd_ketua // tampilkan path lama kalau tidak ada file baru
})

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    form.ttd_ketua = target.files[0]
  } else {
    form.ttd_ketua = null
  }
}

function submit() {
  form.put(`/dpw-provinsi/${props.provinsi.id}`, {
    forceFormData: !!form.ttd_ketua, // hanya kalau ada file baru
    preserveScroll: true,
  })
}

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Edit Provinsi" />

    <div class="p-6 space-y-6 max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Edit DPW Provinsi</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Nama Provinsi -->
        <div>
          <label for="nama" class="block font-medium mb-1">Nama Provinsi</label>
          <input
            id="nama"
            v-model="form.nama"
            type="text"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.nama }"
          />
          <div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">
            {{ form.errors.nama }}
          </div>
        </div>

        <!-- Nama Ketua DPW -->
        <div>
          <label for="nama_ketua_dpw" class="block font-medium mb-1">Nama Ketua DPW</label>
          <input
            id="nama_ketua_dpw"
            v-model="form.nama_ketua_dpw"
            type="text"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.nama_ketua_dpw }"
          />
          <div v-if="form.errors.nama_ketua_dpw" class="text-red-600 text-sm mt-1">
            {{ form.errors.nama_ketua_dpw }}
          </div>
        </div>

        <!-- Upload Tanda Tangan -->
        <div>
          <label for="ttd_ketua" class="block font-medium mb-1">Tanda Tangan Ketua</label>
          <input
            id="ttd_ketua"
            type="file"
            @change="onFileChange"
            class="w-full border px-3 py-2 rounded"
            accept="image/*"
          />
          <div v-if="form.errors.ttd_ketua" class="text-red-600 text-sm mt-1">
            {{ form.errors.ttd_ketua }}
          </div>

          <!-- Preview tanda tangan -->
          <div v-if="previewUrl" class="mt-2">
            <img :src="previewUrl" alt="Preview tanda tangan" class="h-24 border rounded" />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 mt-6">
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
