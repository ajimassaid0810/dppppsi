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
      kode?: string | null
      nama: string
      provinsi?: { id: number; kode?: string | null; nama: string }
    }[]
    links: { url: string | null; label: string; active: boolean }[]
  }
  provinsiList: { id: number; kode?: string | null; nama: string }[]
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

    <div class="max-w-5xl space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">DPD Kota/Kab</h1>
        <Link href="/dpd-kota-kab/create">
          <Button>Tambah DPD Kota/Kab</Button>
        </Link>
      </div>

      <!-- Filter & Search -->
      <div class="flex gap-4">
        <select v-model="provinsiId" class="rounded border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white">
          <option v-for="prov in props.provinsiList" :key="prov.id" :value="prov.id">
            {{ prov.kode ? `[${prov.kode}] ` : '' }}{{ prov.nama }}
          </option>
        </select>

        <input
          v-model="search"
          type="text"
          placeholder="Cari kota/kabupaten..."
          class="flex-1 rounded border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white dark:placeholder:text-slate-400"
        />
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0f1b16]">
        <table class="w-full text-left">
          <thead class="bg-slate-100 text-slate-700 dark:bg-[#163425] dark:text-slate-100">
            <tr>
              <th class="px-4 py-2">No</th>
              <th class="px-4 py-2">Kode DPD</th>
              <th class="px-4 py-2">Nama Kota/Kab</th>
              <th class="px-4 py-2">Kode DPW</th>
              <th class="px-4 py-2">Provinsi</th>
              <th class="px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody class="dark:text-slate-100">
            <tr
              v-for="item, index in props.kotaKab?.data || []"
              :key="item.id"
              class="border-t border-slate-200 dark:border-slate-800"
            >
              <td class="px-4 py-2">{{ index+1 }}</td>
              <td class="px-4 py-2 font-mono">{{ item.kode || '-' }}</td>
              <td class="px-4 py-2">{{ item.nama }}</td>
              <td class="px-4 py-2 font-mono">{{ item.provinsi?.kode || '-' }}</td>
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
              <td colspan="6" class="px-4 py-2 text-center text-gray-500 dark:text-slate-400">
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
          class="rounded border px-3 py-1"
          :class="{
            'bg-gray-200 text-slate-900 dark:bg-[#163425] dark:text-white': link.active,
            'text-gray-400 pointer-events-none dark:text-slate-600': !link.url,
          }"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>
