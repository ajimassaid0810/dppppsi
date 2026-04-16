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
  <div class="mt-4 p-4">
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-white">Daftar DPW Provinsi</h1>

    <!-- Search & Add -->
    <div class="mb-4 flex items-center gap-2">
      <input
        v-model="search"
        @keyup.enter="submitSearch"
        type="text"
        placeholder="Cari provinsi..."
        class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-[#10261c] dark:text-white dark:placeholder:text-slate-400"
      />
      <button @click="submitSearch" class="bg-blue-600 text-white px-4 py-2 rounded">Cari</button>
      <a href="/dpw-provinsi/create" class="bg-primary text-white border-primary text-white px-4 py-2 rounded">Tambah</a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0f1b16]">
   <table class="w-full overflow-hidden rounded-lg border text-left text-sm dark:border-slate-800">
     <thead class="bg-slate-100 text-xs uppercase tracking-wider text-slate-700 dark:bg-[#163425] dark:text-slate-100">

        <tr class="text-left">
          <th class="px-4 py-2">No</th>
          <th class="px-4 py-2">Kode</th>
          <th class="px-4 py-2">Nama</th>
          <th class="px-4 py-2">Ketua DPW</th>
          <th class="px-4 py-2">Tanda Tangan</th>
          <th class="px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="dark:text-slate-100">
        <tr v-for="p,index in provinsi.data" :key="p.id" class="border-t border-slate-200 transition hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-[#16261e]">
           <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ index + 1 }}</td>
           <td class="px-4 py-2 font-mono text-gray-800 dark:text-gray-100">{{ p.kode || '-' }}</td>
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
    <div class="mt-4 flex justify-center space-x-1">
      <template v-for="link in provinsi.links" :key="link.label">
        <button
          v-if="link.url"
          @click="router.get(link.url)"
          v-html="link.label"
          :class="[
            'px-3 py-1 rounded border',
            link.active ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-100 dark:border-slate-700 dark:bg-[#10261c] dark:text-slate-100 dark:hover:bg-[#163425]'
          ]"
        />
      </template>
    </div>
  </div>
  </AppLayout>
</template>
