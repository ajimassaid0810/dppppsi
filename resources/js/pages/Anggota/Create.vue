<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AnggotaForm from '@/components/anggota/AnggotaForm.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import type { AnggotaFormOptions } from '@/pages/Anggota/types'

defineProps<{
  options: AnggotaFormOptions
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
  { title: 'Tambah Anggota', href: '/anggota/create' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Tambah Anggota" />

    <div class="mx-auto max-w-7xl p-6">
      <AnggotaForm mode="create" :options="options" />
    </div>
  </AppLayout>
</template>
