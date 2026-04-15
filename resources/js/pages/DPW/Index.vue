<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  provinsi: Object, // hasil dari paginate()
  filters: Object,
})

const search = ref(props.filters.search || '')

function submitSearch() {
  router.get(`/dpw-provinsi`, {
    search: search.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function confirmDelete(id) {
  if (confirm('Yakin ingin menghapus provinsi ini?')) {
    router.delete(`/dpw-provinsi/${id}`)
  }
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbItems">
  <div class="p-4 mt-4">
    <h1 class="text-2xl font-bold mb-6">Daftar DPW Provinsi</h1>

    <!-- Search & Add -->
    <div class="flex items-center gap-2 mb-4">
      <input
        v-model="search"
        @keyup.enter="submitSearch"
        type="text"
        placeholder="Cari provinsi..."
        class="border px-3 py-2 rounded w-full"
      />
      <button @click="submitSearch" class="bg-blue-600 text-white px-4 py-2 rounded">Cari</button>
      <a href="/dpw-provinsi/create" class="bg-primary text-white border-primary text-white px-4 py-2 rounded">Tambah</a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border rounded-lg">
   <table class=" w-full text-left text-sm border rounded-lg overflow-hidden">
     <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">

        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Nama</th>
          <th class="px-4 py-2">Ketua DPW</th>
          <th class="px-4 py-2">Tanda Tangan</th>
          <th class="px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p,index in provinsi.data" :key="p.id" class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">
           <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ index + 1 }}</td>
           <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ p.nama }}</td>
           <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ p.nama_ketua_dpw || '-' }}</td>
           <td class="px-4 py-2 text-gray-800 dark:text-gray-100">
            <a
              v-if="p.url_ttd_ketua"
              :href="p.url_ttd_ketua"
              target="_blank"
              class="text-blue-500 underline"
            >Lihat</a>
            <span v-else>-</span>
          </td>
          <td class="p-2 flex gap-2">
            <a :href="`/dpw-provinsi/${p.id}/edit`" class="text-yellow-600"><Button variant="outline">Edit</Button></a>
            <Button variant="outline"  @click="confirmDelete(p.id)" class="text-red-600">Hapus</Button>
          </td>
        </tr>
      </tbody>
    </table>
</div>
    <!-- Pagination -->
    <div class="flex justify-center mt-4 space-x-1">
      <template v-for="link in provinsi.links" :key="link.label">
        <button
          v-if="link.url"
          @click="router.get(link.url)"
          v-html="link.label"
          :class="[
            'px-3 py-1 rounded border',
            link.active ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-900 hover:bg-gray-100'
          ]"
        />
      </template>
    </div>
  </div>
  </AppLayout>
</template>