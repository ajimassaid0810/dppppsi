<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'
import { ref, watch } from 'vue'

// Props dari Laravel
const props = defineProps<{
  kotaKab?: {
    data: {
      id: number
      nama: string
      provinsi?: { id: number; nama: string }
    }[]
    links: { url: string | null; label: string; active: boolean }[]
  }
  provinsiList: { id: number; nama: string }[]
  filters: { search?: string; provinsi_id?: number }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'DPD Kota/Kab', href: '/dpd-kota-kab' },
]

const search = ref(props.filters.search || '')
const provinsiId = ref(props.filters.provinsi_id || (props.provinsiList[0]?.id ?? null))

watch([search, provinsiId], () => {
  router.get(
    '/dpd-kota-kab',
    { search: search.value, provinsi_id: provinsiId.value },
    { preserveState: true, replace: true }
  )
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="DPD Kota/Kab" />

    <div class="p-6 space-y-6 max-w-5xl ">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">DPD Kota/Kab</h1>
        <Link href="/dpd-kota-kab/create">
          <Button>Tambah DPD Kota/Kab</Button>
        </Link>
      </div>

      <!-- Filter & Search -->
      <div class="flex gap-4">
        <select v-model="provinsiId" class="border rounded px-3 py-2">
          <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
            {{ prov.nama }}
          </option>
        </select>

        <input
          v-model="search"
          type="text"
          placeholder="Cari kota/kabupaten..."
          class="border rounded px-3 py-2 flex-1"
        />
      </div>

      <!-- Table -->
      <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Nama Kota/Kab</th>
              <th class="px-4 py-2">Provinsi</th>
              <th class="px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item, index in props.kotaKab?.data || []"
              :key="item.id"
              class="border-t"
            >
              <td class="px-4 py-2">{{ index+1 }}</td>
              <td class="px-4 py-2">{{ item.nama }}</td>
              <td class="px-4 py-2">{{ item.provinsi?.nama }}</td>
              <td class="px-4 py-2 flex gap-2">
                <Link :href="`/dpd-kota-kab/${item.id}/edit`">
                  <Button variant="outline" size="sm">Edit</Button>
                </Link>
                <Button
                  variant="destructive"
                  size="sm"
                  @click="router.delete(`/dpd-kota-kab/${item.id}`)"
                >
                  Hapus
                </Button>
              </td>
            </tr>
            <tr v-if="!props.kotaKab || props.kotaKab.data.length === 0">
              <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                Tidak ada data
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="props.kotaKab?.links?.length"
        class="flex gap-2 justify-center"
      >
        <Link
          v-for="link in props.kotaKab.links"
          :key="link.label"
          :href="link.url || ''"
          class="px-3 py-1 border rounded"
          :class="{
            'bg-gray-200': link.active,
            'text-gray-400 pointer-events-none': !link.url,
          }"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>
