<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'


defineProps<{
  anggota: {
    data: Array<{
      id: string
      nama_lengkap: string
      nik: string
      tanggal_lahir: string
      alamat: string | null
      telepon: string | null
      perguruan: string | null
      golongan_darah: string | null
      masa_berlaku: string | null
      tanggal_gabung: string
      cabang: { id: string; nama_cabang: string }
      kelurahan?: { id: string; nama: string } | null
      foto?: string | null
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
]

const page = usePage<{ flash?: { success?: string; error?: string } }>()
const flash = page.props.flash ?? {}

// Modal state
const showModal = ref(false)
const selectedAnggota = ref<typeof props.anggota.data[0] | null>(null)

function openModal(anggota: typeof props.anggota.data[0]) {
  selectedAnggota.value = anggota
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedAnggota.value = null
}

function goToPrintPage() {
  if (selectedAnggota.value) {
    window.location.href = `/anggota/${selectedAnggota.value.id}/print`
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Daftar Anggota" />

    <div class="p-6 space-y-6">
      <!-- Flash Messages -->
      <div v-if="flash.success" class="bg-green-100 text-green-700 p-2 rounded">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="bg-red-100 text-red-700 p-2 rounded">
        {{ flash.error }}
      </div>

      <!-- Header -->
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Daftar Anggota</h1>
        <Link href="/anggota/create">
          <Button>+ Tambah Anggota</Button>
        </Link>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2">Nama Lengkap</th>
              <th class="px-4 py-2">NIK</th>
              <th class="px-4 py-2">Tanggal Lahir</th>
              <th class="px-4 py-2">Cabang</th>
              <th class="px-4 py-2">Telepon</th>
              <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="anggota.data.length === 0">
              <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                Belum ada anggota terdaftar.
              </td>
            </tr>
            <tr v-for="row in anggota.data" :key="row.id" class="border-t">
              <td class="px-4 py-2">{{ row.nama_lengkap }}</td>
              <td class="px-4 py-2">{{ row.nik }}</td>
              <td class="px-4 py-2">{{ row.tanggal_lahir }}</td>
              <td class="px-4 py-2">{{ row.cabang?.nama_cabang }}</td>
              <td class="px-4 py-2">{{ row.telepon ?? '-' }}</td>
              <td class="px-4 py-2 text-right space-x-2">
                <Button variant="primary" size="sm" @click="openModal(row)">
                  Lihat Detail
                </Button>
                <Link :href="`/anggota/${row.id}/edit`">
                  <Button variant="secondary" size="sm">Edit</Button>
                </Link>
                <Link as="button" method="delete" :href="`/anggota/${row.id}`" class="inline-flex">
                  <Button variant="destructive" size="sm">Hapus</Button>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center mt-4 space-x-1">
        <Link
          v-for="link in anggota.links"
          :key="link.label"
          :href="link.url || ''"
          class="px-3 py-1 rounded border"
          :class="[link.active ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-900 hover:bg-gray-100', link.url ? '' : 'opacity-50 cursor-not-allowed']"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white p-6 rounded shadow-lg w-96 relative max-h-[80vh] overflow-y-auto">
        <button class="absolute top-2 right-2 text-gray-500" @click="closeModal">✕</button>
        <h2 class="text-xl font-bold mb-4">Detail Anggota</h2>
        <div v-if="selectedAnggota">
          <div v-if="selectedAnggota.foto" class="mb-2">
            <img :src="'/storage/' + selectedAnggota.foto" class="w-full h-40 object-cover rounded" />
          </div>
          <p><strong>Nama Lengkap:</strong> {{ selectedAnggota.nama_lengkap }}</p>
          <p><strong>NIK:</strong> {{ selectedAnggota.nik }}</p>
          <p><strong>Tanggal Lahir:</strong> {{ selectedAnggota.tanggal_lahir }}</p>
          <p><strong>Alamat:</strong> {{ selectedAnggota.alamat ?? '-' }}</p>
          <p><strong>Telepon:</strong> {{ selectedAnggota.telepon ?? '-' }}</p>
          <p><strong>Perguruan:</strong> {{ selectedAnggota.perguruan ?? '-' }}</p>
          <p><strong>Golongan Darah:</strong> {{ selectedAnggota.golongan_darah ?? '-' }}</p>
          <p><strong>Masa Berlaku:</strong> {{ selectedAnggota.masa_berlaku ?? '-' }}</p>
          <p><strong>Tanggal Gabung:</strong> {{ selectedAnggota.tanggal_gabung }}</p>
          <p><strong>Cabang:</strong> {{ selectedAnggota.cabang?.nama_cabang }}</p>
          <p><strong>Kelurahan:</strong> {{ selectedAnggota.kelurahan?.nama ?? '-' }}</p>

          <div class="mt-4">
            <Button variant="outline" size="sm" @click="goToPrintPage">Print Kartu</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
