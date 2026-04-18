<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search } from 'lucide-vue-next'

import PublicLayout from '@/components/public/PublicLayout.vue'
import type { PublicSite } from '@/pages/Public/types'

const props = defineProps<{ site: PublicSite }>()

const navLinks = [
  { label: 'Home', href: '/' },
  { label: 'Daftar Anggota', href: '/pendaftaran-anggota' },
  { label: 'Cek Pengajuan', href: '/cek-pengajuan' },
]

const kode = ref('')

function submit() {
  if (!kode.value.trim()) return
  router.get(`/cek-pengajuan/${kode.value.trim().toUpperCase()}`)
}
</script>

<template>
  <PublicLayout :site="site" title="Cek Pengajuan" :nav-links="navLinks">
    <section class="mx-auto max-w-4xl px-4 py-16 md:px-6">
      <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-[0_28px_80px_-50px_rgba(15,23,42,0.28)] dark:border-slate-800 dark:bg-[#0f1b16] dark:shadow-none">
        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-[#0b6b31] dark:text-[#9fe7a8]">Pelacakan Pengajuan</p>
        <h1 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">Masukkan Kode Pengajuan Anda</h1>
        <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-300">
          Kode pengajuan diberikan saat form pendaftaran anggota publik berhasil dikirim. Gunakan kode itu untuk melihat status verifikasi terbaru.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-[1fr_auto]">
          <input v-model="kode" type="text" placeholder="Contoh: PPSI-260418-AB12CD" class="w-full rounded-full border border-slate-300 bg-white px-6 py-4 text-sm font-semibold uppercase tracking-[0.16em] text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" @keyup.enter="submit" />
          <button type="button" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-6 py-4 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]" @click="submit">
            <Search class="size-4" />
            Lacak
          </button>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
