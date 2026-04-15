<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  provinsiList: { id: number; nama: string }[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPD Kota/Kab', href: '/dpd-kota-kab' },
  { title: 'Tambah', href: '/dpd-kota-kab/create' },
]

const form = useForm({
  nama: '',
  provinsi_id: props.provinsiList[0]?.id || null,
})

function submit() {
  form.post('/dpd-kota-kab', {
    onSuccess: () => {
      form.reset('nama', 'provinsi_id')
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Tambah DPD Kota/Kab" />

    <div class="p-6 space-y-6 max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Tambah DPD Kota/Kab</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Nama Kota/Kab -->
        <div>
          <label for="nama" class="block font-medium mb-1">Nama Kota/Kab</label>
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

        <!-- Pilih Provinsi -->
        <div>
          <label for="provinsi_id" class="block font-medium mb-1">Provinsi</label>
          <select
            id="provinsi_id"
            v-model="form.provinsi_id"
            class="w-full border px-3 py-2 rounded"
            :class="{ 'border-red-500': form.errors.provinsi_id }"
          >
            <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
              {{ prov.nama }}
            </option>
          </select>
          <div v-if="form.errors.provinsi_id" class="text-red-600 text-sm mt-1">
            {{ form.errors.provinsi_id }}
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 mt-6">
          <Link href="/dpd-kota-kab">
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
