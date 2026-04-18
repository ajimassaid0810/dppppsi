<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AnggotaForm from '@/components/anggota/AnggotaForm.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import type { AnggotaFormOptions, AnggotaRecord } from '@/pages/Anggota/types'

const props = defineProps<{
  anggota: AnggotaRecord
  options: AnggotaFormOptions
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daftar Anggota', href: '/anggota' },
  { title: 'Edit Anggota', href: `/anggota/${props.anggota.id}/edit` },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit Anggota" />

    <div class="mx-auto max-w-7xl p-6">
      <AnggotaForm mode="edit" :options="props.options" :anggota="props.anggota" />
    </div>
  </AppLayout>
</template>
