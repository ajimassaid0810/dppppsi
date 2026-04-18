<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ArrowUpRight, LayoutDashboard, LogIn, Menu, Search, X } from 'lucide-vue-next'

import type { AppPageProps } from '@/types'
import type { PublicSite } from '@/pages/Public/types'

interface NavLink {
  label: string
  href: string
}

const emit = defineEmits<{
  navSelect: [href: string]
}>()

const props = withDefaults(
  defineProps<{
    site: PublicSite
    title?: string
    navLinks?: NavLink[]
    activeNavHref?: string | null
  }>(),
  {
    title: null,
    navLinks: () => [],
    activeNavHref: null,
  },
)

const page = usePage<AppPageProps>()
const mobileOpen = ref(false)
const flash = computed(() => page.props.flash ?? {})
const authUser = computed(() => page.props.auth.user)
const currentPath = computed(() => page.url.split('?')[0]?.split('#')[0] || '/')
const authAction = computed(() =>
  authUser.value
    ? { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard }
    : { label: 'Login', href: '/login', icon: LogIn },
)
const socialEntries = computed(() =>
  Object.entries(props.site.social_links ?? {}).filter(([, url]) => !!url) as Array<[string, string]>,
)

function closeMenu() {
  mobileOpen.value = false
}

function isActiveNav(item: NavLink) {
  if (item.href.startsWith('#')) {
    return item.href === props.activeNavHref
  }

  return item.href === currentPath.value
}

function desktopNavClass(item: NavLink) {
  return isActiveNav(item)
    ? 'inline-flex items-center rounded-full bg-[#0b6b31] px-4 py-2 text-sm font-semibold text-white shadow-[0_16px_40px_-24px_rgba(11,107,49,0.76)] dark:bg-[#9fe7a8] dark:text-[#102019]'
    : 'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-[#eef8ef] hover:text-[#0b6b31] dark:text-slate-200 dark:hover:bg-[#123322] dark:hover:text-[#9fe7a8]'
}

function mobileNavClass(item: NavLink) {
  return isActiveNav(item)
    ? 'rounded-2xl bg-[#0b6b31] px-4 py-3 text-sm font-semibold text-white dark:bg-[#9fe7a8] dark:text-[#102019]'
    : 'rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-[#eef8ef] hover:text-[#0b6b31] dark:text-slate-100 dark:hover:bg-[#123322] dark:hover:text-[#9fe7a8]'
}

function footerNavClass(item: NavLink) {
  return isActiveNav(item)
    ? 'font-semibold text-white'
    : 'transition hover:text-white'
}

function selectNav(item: NavLink) {
  if (!item.href.startsWith('#')) {
    return
  }

  emit('nav-select', item.href)
  closeMenu()
}
</script>

<template>
  <Head :title="title || site.seo_title || site.site_name">
    <meta name="description" :content="site.seo_description || site.site_tagline || site.site_name" />
    <meta v-if="site.seo_keywords" name="keywords" :content="site.seo_keywords" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link v-if="site.favicon_url" rel="icon" :href="site.favicon_url" />
  </Head>

  <div class="min-h-screen bg-[linear-gradient(180deg,#fffef6_0%,#fff8ec_26%,#fffdf7_58%,#eef8ef_100%)] text-slate-900 dark:bg-[#08110c] dark:text-slate-100">
    <header class="sticky top-0 z-50 border-b border-white/60 bg-white/85 backdrop-blur-xl dark:border-white/10 dark:bg-[#0b1713]/90">
      <div class="border-b border-slate-200/70 bg-[#0b6b31] text-[#f7f3db] dark:border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-2 text-xs sm:flex-row sm:items-center sm:justify-between md:px-6">
          <div class="flex flex-wrap items-center gap-4">
            <span v-if="site.email">{{ site.email }}</span>
            <span v-if="site.telepon">{{ site.telepon }}</span>
          </div>
          <div class="flex items-center gap-3">
            <a href="/cek-pengajuan" class="inline-flex items-center gap-1 font-semibold text-[#fff4bf] transition hover:text-white">
              <Search class="size-3.5" />
              Cek Pengajuan
            </a>
            <a
              v-for="[name, url] in socialEntries"
              :key="name"
              :href="url"
              target="_blank"
              rel="noreferrer"
              class="capitalize text-[#d8f4db] transition hover:text-white"
            >
              {{ name }}
            </a>
          </div>
        </div>
      </div>

      <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 md:px-6">
        <a href="/" class="flex min-w-0 items-center gap-4">
          <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-[#d7ebd9] bg-white shadow-sm dark:border-white/10 dark:bg-[#0f2018]">
            <img v-if="site.logo_url" :src="site.logo_url" class="h-full w-full object-cover" alt="Logo PPSI" />
            <span v-else class="font-['Bebas_Neue'] text-2xl tracking-[0.12em] text-[#0b6b31]">PPSI</span>
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold uppercase tracking-[0.28em] text-[#0b6b31] dark:text-[#9fe7a8]">Portal Publik</p>
            <h1 class="truncate font-['Bebas_Neue'] text-3xl leading-none tracking-[0.08em]">{{ site.site_name }}</h1>
          </div>
        </a>

        <nav class="hidden items-center gap-6 lg:flex">
          <template v-for="item in navLinks" :key="item.href">
            <button
              v-if="item.href.startsWith('#')"
              type="button"
              :class="desktopNavClass(item)"
              @click="selectNav(item)"
            >
              {{ item.label }}
            </button>
            <Link
              v-else
              :href="item.href"
              :class="desktopNavClass(item)"
            >
              {{ item.label }}
            </Link>
          </template>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
          <Link
            href="/cek-pengajuan"
            class="inline-flex items-center justify-center rounded-full border border-[#c8dec9] px-4 py-2 text-sm font-semibold text-[#0b6b31] transition hover:border-[#0b6b31] hover:bg-[#eef8ef] dark:border-[#234a33] dark:text-[#9fe7a8] dark:hover:bg-[#123322]"
          >
            Cek Pengajuan
          </Link>
          <Link
            :href="authAction.href"
            class="inline-flex items-center gap-2 rounded-full bg-[#0f172a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0b6b31] dark:bg-[#123322] dark:hover:bg-[#0b6b31]"
          >
            <component :is="authAction.icon" class="size-4" />
            {{ authAction.label }}
          </Link>
        </div>

        <button
          type="button"
          class="inline-flex size-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-700 lg:hidden dark:border-white/10 dark:text-slate-100"
          @click="mobileOpen = !mobileOpen"
        >
          <Menu v-if="!mobileOpen" class="size-5" />
          <X v-else class="size-5" />
        </button>
      </div>

      <div v-if="mobileOpen" class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden dark:border-white/10 dark:bg-[#0b1713]">
        <div class="flex flex-col gap-3">
          <template v-for="item in navLinks" :key="`mobile-${item.href}`">
            <button
              v-if="item.href.startsWith('#')"
              type="button"
              :class="mobileNavClass(item)"
              @click="selectNav(item)"
            >
              {{ item.label }}
            </button>
            <Link
              v-else
              :href="item.href"
              :class="mobileNavClass(item)"
              @click="closeMenu"
            >
              {{ item.label }}
            </Link>
          </template>
          <Link
            href="/cek-pengajuan"
            class="rounded-2xl border border-[#c8dec9] px-4 py-3 text-sm font-semibold text-[#0b6b31] dark:border-[#234a33] dark:text-[#9fe7a8]"
            @click="closeMenu"
          >
            Cek Pengajuan
          </Link>
          <Link
            :href="authAction.href"
            class="rounded-2xl bg-[#0f172a] px-4 py-3 text-sm font-semibold text-white dark:bg-[#123322]"
            @click="closeMenu"
          >
            {{ authAction.label }}
          </Link>
        </div>
      </div>
    </header>

    <div v-if="flash.success" class="mx-auto mt-4 max-w-7xl px-4 md:px-6">
      <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ flash.success }}
      </div>
    </div>
    <div v-if="flash.error" class="mx-auto mt-4 max-w-7xl px-4 md:px-6">
      <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
        {{ flash.error }}
      </div>
    </div>

    <main>
      <slot />
    </main>

    <footer class="mt-16 border-t border-slate-200 bg-[#0f172a] text-slate-100 dark:border-white/10">
      <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-[1.2fr_0.8fr_0.8fr] md:px-6">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.28em] text-[#9fe7a8]">PPSI</p>
          <h2 class="mt-3 font-['Bebas_Neue'] text-4xl leading-none tracking-[0.08em]">{{ site.site_name }}</h2>
          <p class="mt-4 max-w-xl text-sm leading-7 text-slate-300">
            {{ site.footer_text || site.site_tagline || 'Portal publik untuk informasi organisasi, pendaftaran anggota, dan pelacakan pengajuan.' }}
          </p>
        </div>

        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#f2c94c]">Navigasi</p>
          <div class="mt-4 flex flex-col gap-3 text-sm text-slate-300">
            <template v-for="item in navLinks" :key="`footer-${item.href}`">
              <button v-if="item.href.startsWith('#')" type="button" :class="footerNavClass(item)" @click="selectNav(item)">{{ item.label }}</button>
              <Link v-else :href="item.href" :class="footerNavClass(item)">{{ item.label }}</Link>
            </template>
          </div>
        </div>

        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.24em] text-[#f2c94c]">Kontak</p>
          <div class="mt-4 space-y-3 text-sm leading-7 text-slate-300">
            <p v-if="site.alamat">{{ site.alamat }}</p>
            <p v-if="site.telepon">{{ site.telepon }}</p>
            <p v-if="site.email">{{ site.email }}</p>
          </div>
        </div>
      </div>

      <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-xs text-slate-400 md:flex-row md:items-center md:justify-between md:px-6">
          <p>&copy; {{ new Date().getFullYear() }} {{ site.site_name }}. All rights reserved.</p>
          <Link
            :href="authAction.href"
            class="inline-flex items-center gap-2 font-semibold text-[#d7f3db] transition hover:text-white"
          >
            <component :is="authAction.icon" class="size-4" />
            {{ authAction.label }}
            <ArrowUpRight class="size-4" />
          </Link>
        </div>
      </div>
    </footer>
  </div>
</template>
