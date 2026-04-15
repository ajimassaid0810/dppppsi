<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'

import { computed } from 'vue'

const previewUrl = computed(() =>
  form.ttd_ketua ? URL.createObjectURL(form.ttd_ketua) : null
)


const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPW Provinsi', href: '/dpw-provinsi' },
  { title: 'Tambah Provinsi', href: '/dpw-provinsi/create' },
]

const form = useForm({
  nama: '',
  nama_ketua_dpw: '',
  ttd_ketua: null as File | null,
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
  form.post('/dpw-provinsi', {
    forceFormData: true,
    onSuccess: () => {
      form.reset('nama', 'nama_ketua_dpw', 'ttd_ketua')
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Tambah Provinsi" />

    <div class="p-6 space-y-6 max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Tambah DPW Provinsi</h1>

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
          <label for="ttd_ketua" class="block font-medium mb-1">Tanda Tangan Ketua (opsional)</label>
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
