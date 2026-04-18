<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Check, MapPinned, ShieldCheck, Sparkles, Users2 } from 'lucide-vue-next'

import PublicLayout from '@/components/public/PublicLayout.vue'
import type {
  LandingActivityEntry,
  LandingHeroItem,
  LandingHistoryEntry,
  LandingKtaEntry,
  LandingOrganizationEntry,
  LandingSupportEntry,
  PublicSite,
} from '@/pages/Public/types'
import { formatPublicDate } from '@/pages/Public/types'

const props = defineProps<{
  site: PublicSite
  hero: LandingHeroItem
  historyItems: LandingHistoryEntry[]
  organizationItems: LandingOrganizationEntry[]
  activities: LandingActivityEntry[]
  supportItems: LandingSupportEntry[]
  ktaBlock: LandingKtaEntry
  stats: {
    dpw: number
    dpd: number
    dpc: number
    pagoruan: number
    anggota: number
  }
}>()

type LandingSection = 'home' | 'sejarah' | 'struktur' | 'kegiatan' | 'support' | 'kta'

const navLinks = [
  { label: 'Home', href: '#home' },
  { label: 'Sejarah', href: '#sejarah' },
  { label: 'Struktur Organisasi', href: '#struktur' },
  { label: 'Kegiatan', href: '#kegiatan' },
  { label: 'Support', href: '#support' },
  { label: 'KTA', href: '#kta' },
]

const activeSection = ref<LandingSection>('home')
const activeNavHref = computed(() => `#${activeSection.value}`)

const historyItems = computed<LandingHistoryEntry[]>(() =>
  props.historyItems.length
    ? props.historyItems
    : [
        {
          id: null,
          title: 'Pelestarian Warisan',
          subtitle: 'PPSI hadir sebagai ruang tumbuh pencak silat',
          body: 'Organisasi dibangun untuk menjaga tradisi, memperkuat jejaring antarwilayah, dan menyiapkan kader yang tertib secara administrasi maupun budaya.',
          icon: 'Budaya',
        },
        {
          id: null,
          title: 'Persaudaraan dan Kaderisasi',
          subtitle: 'Silat bukan hanya teknik',
          body: 'Jejaring PPSI menghubungkan pengurus, pagoruan, dan anggota dalam ekosistem yang lebih rapi, terukur, dan terbuka bagi pendaftaran nasional.',
          icon: 'Persaudaraan',
        },
      ],
)

const organizationItems = computed<LandingOrganizationEntry[]>(() =>
  props.organizationItems.length
    ? props.organizationItems
    : [
        {
          id: null,
          name: 'Struktur Nasional',
          position: 'Sedang disiapkan',
          group_name: 'DPP PPSI',
          short_bio: 'Data struktur organisasi dapat dikelola langsung dari Landing CMS oleh superadmin.',
        },
      ],
)

const activities = computed<LandingActivityEntry[]>(() =>
  props.activities.length
    ? props.activities
    : [
        {
          id: null,
          title: 'Latihan dan Pembinaan',
          summary: 'Program pembinaan teknik, adab organisasi, dan kesinambungan kader.',
        },
        {
          id: null,
          title: 'Agenda Kegiatan',
          summary: 'Publikasi kegiatan, pelatihan, dan aktivitas wilayah dapat diatur dari CMS.',
        },
      ],
)

const supportItems = computed<LandingSupportEntry[]>(() =>
  props.supportItems.length
    ? props.supportItems
    : [
        { id: null, name: 'Mitra Organisasi' },
        { id: null, name: 'Pendukung Kegiatan' },
        { id: null, name: 'Kolaborator Wilayah' },
      ],
)

const heroBackgroundStyle = computed(() =>
  props.hero.background_image_url
    ? {
        backgroundImage: `linear-gradient(135deg, rgba(9, 35, 22, 0.86), rgba(11, 107, 49, 0.55)), url(${props.hero.background_image_url})`,
      }
    : undefined,
)

function pdfPreviewUrl(url?: string | null) {
  return url ? `${url}#toolbar=0&navpanes=0&scrollbar=0&view=FitH` : null
}

const statsCards = computed(() => [
  { title: 'DPW', value: props.stats.dpw, caption: 'Sebaran provinsi aktif', icon: ShieldCheck },
  { title: 'DPD', value: props.stats.dpd, caption: 'Kota dan kabupaten', icon: MapPinned },
  { title: 'Anggota', value: props.stats.anggota, caption: 'Data resmi tercatat', icon: Users2 },
])

function resolveSection(value?: string | null): LandingSection {
  const normalized = (value ?? '').replace(/^#/, '').trim()

  if (normalized === 'sejarah' || normalized === 'struktur' || normalized === 'kegiatan' || normalized === 'support' || normalized === 'kta') {
    return normalized
  }

  return 'home'
}

function updateBrowserHash(section: LandingSection) {
  if (typeof window === 'undefined') {
    return
  }

  const url = new URL(window.location.href)
  url.hash = section === 'home' ? '' : section

  window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`)
}

function setActiveSection(section: LandingSection, shouldSyncHash = true) {
  activeSection.value = section

  if (shouldSyncHash) {
    updateBrowserHash(section)
  }

  if (typeof window !== 'undefined') {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

function onNavSelect(href: string) {
  setActiveSection(resolveSection(href))
}

function handleHashChange() {
  activeSection.value = resolveSection(window.location.hash)
}

onMounted(() => {
  if (typeof window === 'undefined') {
    return
  }

  activeSection.value = resolveSection(window.location.hash)
  window.addEventListener('hashchange', handleHashChange)
})

onBeforeUnmount(() => {
  if (typeof window === 'undefined') {
    return
  }

  window.removeEventListener('hashchange', handleHashChange)
})
</script>

<template>
  <PublicLayout :site="site" title="Landing Page PPSI" :nav-links="navLinks" :active-nav-href="activeNavHref" @nav-select="onNavSelect">
    <section v-if="activeSection === 'home'" id="home" class="relative overflow-hidden" :style="heroBackgroundStyle">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(242,201,76,0.25),_transparent_22%),radial-gradient(circle_at_85%_15%,_rgba(159,231,168,0.18),_transparent_26%),linear-gradient(135deg,_#f7f4df_0%,_#fff7eb_34%,_#eef8ef_100%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(242,201,76,0.16),_transparent_24%),radial-gradient(circle_at_85%_15%,_rgba(159,231,168,0.1),_transparent_26%),linear-gradient(135deg,_rgba(9,35,22,0.88)_0%,_rgba(11,107,49,0.84)_52%,_rgba(15,23,42,0.92)_100%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.06)_1px,transparent_1px)] bg-[size:32px_32px] opacity-70 dark:opacity-20" />

      <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 md:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:py-24">
        <div class="space-y-7">
          <p class="inline-flex items-center gap-2 rounded-full border border-[#d7ebd9] bg-white/85 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-[#0b6b31] shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-[#d7f3db]">
            <Sparkles class="size-4" />
            {{ hero.badge || 'Portal Publik Nasional' }}
          </p>

          <div class="space-y-4">
            <h1 class="max-w-4xl font-['Bebas_Neue'] text-6xl uppercase leading-[0.9] tracking-[0.06em] text-slate-950 dark:text-[#fff7da] md:text-8xl">
              {{ hero.title }}
              <span v-if="hero.highlighted_text" class="block text-[#0b6b31] dark:text-[#f2c94c]">{{ hero.highlighted_text }}</span>
            </h1>
            <p class="max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-200 md:text-lg">{{ hero.description }}</p>
          </div>

          <div class="flex flex-col gap-4 sm:flex-row">
            <Link :href="hero.primary_cta_url || '/pendaftaran-anggota'" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-6 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]">
              {{ hero.primary_cta_label || 'Daftar Anggota' }}
              <ArrowRight class="size-4" />
            </Link>
            <Link :href="hero.secondary_cta_url || '/cek-pengajuan'" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-300 bg-white/90 px-6 py-3 text-sm font-semibold text-slate-800 transition hover:border-[#0b6b31] hover:text-[#0b6b31] dark:border-white/10 dark:bg-white/10 dark:text-white dark:hover:border-[#9fe7a8] dark:hover:text-[#9fe7a8]">
              {{ hero.secondary_cta_label || 'Cek Pengajuan' }}
            </Link>
          </div>

          <div class="grid gap-4 sm:grid-cols-3">
            <article v-for="card in statsCards" :key="card.title" class="rounded-[1.6rem] border border-white/70 bg-white/85 p-5 shadow-[0_24px_60px_-38px_rgba(15,23,42,0.28)] backdrop-blur dark:border-white/10 dark:bg-white/8 dark:shadow-none">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400 dark:text-slate-400">{{ card.title }}</p>
                  <p class="mt-3 text-4xl font-semibold text-slate-950 dark:text-white">{{ card.value }}</p>
                  <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ card.caption }}</p>
                </div>
                <div class="flex size-11 items-center justify-center rounded-2xl bg-[#eef8ef] text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]">
                  <component :is="card.icon" class="size-5" />
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="grid gap-5 self-end">
          <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 shadow-[0_28px_90px_-50px_rgba(15,23,42,0.48)] backdrop-blur dark:border-white/10 dark:bg-white/6 dark:shadow-none">
            <div v-if="hero.hero_image_url" class="h-[380px]">
              <img :src="hero.hero_image_url" alt="Hero PPSI" class="h-full w-full object-cover" />
            </div>
            <div v-else class="grid h-[380px] gap-4 bg-[linear-gradient(145deg,#0b6b31_0%,#123322_45%,#0f172a_100%)] p-6 text-white">
              <div class="flex items-center justify-between rounded-[1.5rem] border border-white/10 bg-white/8 px-5 py-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Akses Publik</p>
                  <p class="mt-2 text-2xl font-semibold">Pendaftaran Anggota Langsung</p>
                </div>
                <span class="rounded-full bg-[#f2c94c] px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-[#1f2937]">V1</span>
              </div>
              <div class="grid gap-3 rounded-[1.5rem] border border-white/10 bg-white/8 p-5">
                <div class="flex items-center gap-3">
                  <div class="flex size-10 items-center justify-center rounded-2xl bg-[#f2c94c] text-[#1f2937]"><Check class="size-5" /></div>
                  <div>
                    <p class="font-semibold">Landing dinamis</p>
                    <p class="text-sm text-white/70">Konten hero, sejarah, kegiatan, dan support dikelola dari CMS.</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="flex size-10 items-center justify-center rounded-2xl bg-[#f2c94c] text-[#1f2937]"><Check class="size-5" /></div>
                  <div>
                    <p class="font-semibold">Pengajuan tanpa akun publik</p>
                    <p class="text-sm text-white/70">Pelamar cukup mengisi form dan menyimpan kode pengajuan.</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="flex size-10 items-center justify-center rounded-2xl bg-[#f2c94c] text-[#1f2937]"><Check class="size-5" /></div>
                  <div>
                    <p class="font-semibold">Konversi ke anggota resmi</p>
                    <p class="text-sm text-white/70">Nomor anggota dibuat saat superadmin menyetujui pengajuan.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-[1.8rem] border border-[#d7ebd9] bg-[#0b6b31] p-6 text-white shadow-[0_28px_90px_-50px_rgba(11,107,49,0.84)] dark:border-white/10 dark:bg-[#123322] dark:shadow-none">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/65">Status Ekosistem</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <div>
                <p class="text-4xl font-semibold">{{ stats.pagoruan }}</p>
                <p class="mt-2 text-sm text-white/70">Pagoruan terhubung dalam struktur wilayah.</p>
              </div>
              <div>
                <p class="text-4xl font-semibold">{{ stats.dpc }}</p>
                <p class="mt-2 text-sm text-white/70">DPC kecamatan dalam data organisasi.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="activeSection === 'sejarah'" id="sejarah" class="mx-auto max-w-7xl px-4 py-16 md:px-6">
      <div class="max-w-3xl">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#0b6b31] dark:text-[#9fe7a8]">Sejarah</p>
        <h2 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">Akar Organisasi dan Nilai yang Dijaga</h2>
        <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">
         Empat elemen utama—sejarah, tujuan, legalitas, dan struktur organisasi—menjadi fondasi PPSI dalam menjaga identitas, arah, dan keberlanjutan di era modern.
        </p>
      </div>

      <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <article v-for="(item, index) in historyItems" :key="item.id ?? `${item.title}-${index}`" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_60px_-42px_rgba(15,23,42,0.24)] dark:border-slate-800 dark:bg-[#0f1b16] dark:shadow-none">
          <div class="grid h-full md:grid-cols-[0.9fr_1.1fr]">
            <div class="min-h-[240px] bg-[linear-gradient(145deg,#f7f3db_0%,#eef8ef_100%)] dark:bg-[linear-gradient(145deg,#123322_0%,#0f172a_100%)]">
              <img v-if="item.image_url" :src="item.image_url" class="h-full w-full object-cover" :alt="item.title" />
              <div v-else class="flex h-full items-center justify-center px-6 text-center font-['Bebas_Neue'] text-5xl uppercase tracking-[0.08em] text-[#0b6b31] dark:text-[#f2c94c]">
                {{ item.icon || 'PPSI' }}
              </div>
            </div>
            <div class="flex flex-col justify-between p-6">
              <div>
                <p v-if="item.subtitle" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">{{ item.subtitle }}</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-white">{{ item.title }}</h3>
                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ item.body }}</p>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section v-if="activeSection === 'struktur'" id="struktur" class="border-y border-slate-200 bg-white/70 py-16 dark:border-white/10 dark:bg-[#0b1713]/60">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div class="max-w-3xl">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#0b6b31] dark:text-[#9fe7a8]">Struktur Organisasi</p>
          <h2 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">Struktur Organisasi Tampil Langsung dalam PDF</h2>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-2">
          <article v-for="(item, index) in organizationItems" :key="item.id ?? `${item.name}-${index}`" class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#102019]">
            <div class="space-y-3 border-b border-slate-200 p-5 dark:border-slate-800">
              <div>
                <p v-if="item.group_name" class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ item.group_name }}</p>
                <h3 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ item.name }}</h3>
                <p class="mt-1 text-sm font-medium text-[#0b6b31] dark:text-[#9fe7a8]">{{ item.position }}</p>
              </div>
              <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">{{ item.short_bio || 'Dokumen struktur organisasi ditampilkan langsung dalam format PDF.' }}</p>
            </div>
            <div class="p-4">
              <iframe
                v-if="item.document_url && item.media_type === 'pdf'"
                :src="pdfPreviewUrl(item.document_url) || undefined"
                class="h-[560px] w-full rounded-[1.2rem] border border-slate-200 bg-white dark:border-slate-700"
                :title="item.name"
              />
              <img
                v-else-if="item.document_url"
                :src="item.document_url"
                class="h-[560px] w-full rounded-[1.2rem] border border-slate-200 object-contain bg-white dark:border-slate-700"
                :alt="item.name"
              />
              <div
                v-else
                class="flex h-[560px] items-center justify-center rounded-[1.2rem] border border-dashed border-slate-300 bg-[linear-gradient(145deg,#eef8ef_0%,#fff7db_100%)] px-6 text-center font-['Bebas_Neue'] text-4xl uppercase tracking-[0.08em] text-[#0b6b31] dark:border-slate-700 dark:bg-[linear-gradient(145deg,#123322_0%,#0f172a_100%)] dark:text-[#f2c94c]"
              >
                PDF Struktur Belum Diunggah
              </div>
            </div>
            <div v-if="item.document_url" class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
              <a
                :href="item.document_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#0b6b31] transition hover:text-[#084723] dark:text-[#9fe7a8]"
              >
                Buka PDF penuh
                <ArrowRight class="size-4" />
              </a>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section v-if="activeSection === 'kegiatan'" id="kegiatan" class="mx-auto max-w-7xl px-4 py-16 md:px-6">
      <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#0b6b31] dark:text-[#9fe7a8]">Kegiatan</p>
          <h2 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">Aktivitas Organisasi</h2>
        </div>
        <Link href="/pendaftaran-anggota" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0b6b31] transition hover:text-[#084723] dark:text-[#9fe7a8]">
          Daftar melalui jalur publik
          <ArrowRight class="size-4" />
        </Link>
      </div>

      <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="(item, index) in activities" :key="item.id ?? `${item.title}-${index}`" class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="h-56 bg-[linear-gradient(135deg,#f7f3db_0%,#eef8ef_100%)] dark:bg-[linear-gradient(135deg,#123322_0%,#0f172a_100%)]">
            <img v-if="item.image_url" :src="item.image_url" class="h-full w-full object-cover" :alt="item.title" />
            <div v-else class="flex h-full items-center justify-center font-['Bebas_Neue'] text-4xl uppercase tracking-[0.08em] text-[#0b6b31] dark:text-[#f2c94c]">Kegiatan</div>
          </div>
          <div class="space-y-3 p-5">
            <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
              <span v-if="item.event_date">{{ formatPublicDate(item.event_date) }}</span>
              <span v-if="item.location">{{ item.location }}</span>
            </div>
            <h3 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ item.title }}</h3>
            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">{{ item.summary }}</p>
            <a v-if="item.detail_url" :href="item.detail_url" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0b6b31] hover:text-[#084723] dark:text-[#9fe7a8]">
              Lihat Detail
              <ArrowRight class="size-4" />
            </a>
          </div>
        </article>
      </div>
    </section>

    <section v-if="activeSection === 'support'" id="support" class="border-y border-slate-200 bg-[#0f172a] py-16 text-white dark:border-white/10">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div class="max-w-3xl">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#f2c94c]">Support</p>
          <h2 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">Logo Pendukung dan Kolaborasi Organisasi</h2>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          <a v-for="(item, index) in supportItems" :key="item.id ?? `${item.name}-${index}`" :href="item.website_url || '#'" :target="item.website_url ? '_blank' : undefined" rel="noreferrer" class="group flex min-h-48 flex-col items-center justify-center rounded-[1.8rem] border border-white/10 bg-white/6 px-6 py-8 text-center transition hover:-translate-y-1 hover:border-[#f2c94c]/40 hover:bg-white/10">
            <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl bg-white text-[#0f172a]">
              <img v-if="item.logo_url" :src="item.logo_url" class="h-full w-full object-cover" :alt="item.name" />
              <span v-else class="font-['Bebas_Neue'] text-2xl tracking-[0.08em]">{{ item.name.slice(0, 4) }}</span>
            </div>
            <p class="mt-4 text-sm font-semibold uppercase tracking-[0.16em] text-white/75 group-hover:text-white">{{ item.name }}</p>
          </a>
        </div>
      </div>
    </section>

    <section v-if="activeSection === 'kta'" id="kta" class="mx-auto max-w-7xl px-4 py-16 md:px-6">
      <div class="grid gap-8 lg:grid-cols-[0.92fr_1.08fr]">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
          <div class="h-full min-h-[340px] bg-[linear-gradient(145deg,#f7f3db_0%,#eef8ef_100%)] dark:bg-[linear-gradient(145deg,#123322_0%,#0f172a_100%)]">
            <img v-if="ktaBlock.banner_image_url" :src="ktaBlock.banner_image_url" class="h-full w-full object-cover" alt="Banner pendaftaran anggota" />
            <div v-else class="flex h-full items-center justify-center px-8 text-center font-['Bebas_Neue'] text-6xl uppercase tracking-[0.08em] text-[#0b6b31] dark:text-[#f2c94c]">KTA</div>
          </div>
        </div>

        <div class="rounded-[2rem] border border-[#d7ebd9] bg-[linear-gradient(160deg,#ffffff_0%,#fff9ea_42%,#eef8ef_100%)] p-8 shadow-[0_28px_90px_-50px_rgba(11,107,49,0.36)] dark:border-white/10 dark:bg-[linear-gradient(160deg,#102019_0%,#0f172a_55%,#123322_100%)] dark:shadow-none">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[#0b6b31] dark:text-[#9fe7a8]">KTA</p>
          <h2 class="mt-3 font-['Bebas_Neue'] text-5xl uppercase leading-none tracking-[0.08em] md:text-6xl">{{ ktaBlock.title }}</h2>
          <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-200">{{ ktaBlock.description }}</p>

          <div class="mt-8 grid gap-4">
            <div v-for="(item, index) in ktaBlock.checklist_items || []" :key="`${item}-${index}`" class="flex items-start gap-4 rounded-[1.4rem] border border-white/70 bg-white/80 px-5 py-4 dark:border-white/10 dark:bg-white/6">
              <div class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-2xl bg-[#0b6b31] text-white dark:bg-[#9fe7a8] dark:text-[#102019]">
                <Check class="size-4" />
              </div>
              <p class="text-sm leading-7 text-slate-700 dark:text-slate-200">{{ item }}</p>
            </div>
          </div>

          <div class="mt-8 flex flex-col gap-4 sm:flex-row">
            <Link :href="ktaBlock.cta_url || '/pendaftaran-anggota'" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#0b6b31] px-6 py-3 text-sm font-semibold text-white shadow-[0_24px_60px_-32px_rgba(11,107,49,0.72)] transition hover:bg-[#095428]">
              {{ ktaBlock.cta_label || 'Mulai Pendaftaran' }}
              <ArrowRight class="size-4" />
            </Link>
            <Link href="/cek-pengajuan" class="inline-flex items-center justify-center gap-2 rounded-full border border-[#c8dec9] px-6 py-3 text-sm font-semibold text-[#0b6b31] transition hover:border-[#0b6b31] hover:bg-[#eef8ef] dark:border-white/10 dark:text-[#9fe7a8] dark:hover:bg-white/8">
              Lacak Pengajuan
            </Link>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
