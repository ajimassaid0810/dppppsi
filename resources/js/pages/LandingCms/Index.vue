<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { FileStack, Globe2, LayoutPanelTop, Settings2 } from 'lucide-vue-next'

import InputError from '@/components/InputError.vue'
import AdminSectionEditor from '@/components/landing/AdminSectionEditor.vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AppPageProps, BreadcrumbItem } from '@/types'

const props = defineProps<{
  settings: Record<string, any>
  heroes: Array<Record<string, any>>
  historyItems: Array<Record<string, any>>
  organizationItems: Array<Record<string, any>>
  activities: Array<Record<string, any>>
  supportItems: Array<Record<string, any>>
  ktaBlocks: Array<Record<string, any>>
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Landing CMS', href: '/landing-cms' },
]

const page = usePage<AppPageProps>()
const flash = computed(() => page.props.flash ?? {})

const settingsForm = useForm<any>({
  site_name: props.settings.site_name ?? '',
  site_tagline: props.settings.site_tagline ?? '',
  email: props.settings.email ?? '',
  telepon: props.settings.telepon ?? '',
  alamat: props.settings.alamat ?? '',
  footer_text: props.settings.footer_text ?? '',
  seo_title: props.settings.seo_title ?? '',
  seo_description: props.settings.seo_description ?? '',
  seo_keywords: props.settings.seo_keywords ?? '',
  facebook_url: props.settings.facebook_url ?? '',
  instagram_url: props.settings.instagram_url ?? '',
  youtube_url: props.settings.youtube_url ?? '',
  linkedin_url: props.settings.linkedin_url ?? '',
  logo: null as File | null,
  favicon: null as File | null,
})

const logoPreview = ref<string | null>(props.settings.logo_url ?? null)
const faviconPreview = ref<string | null>(props.settings.favicon_url ?? null)

const heroFields = [
  { key: 'badge', label: 'Badge', type: 'text' },
  { key: 'title', label: 'Judul', type: 'text' },
  { key: 'highlighted_text', label: 'Teks Sorot', type: 'text' },
  { key: 'description', label: 'Deskripsi', type: 'textarea', rows: 4 },
  { key: 'primary_cta_label', label: 'Label CTA Utama', type: 'text' },
  { key: 'primary_cta_url', label: 'URL CTA Utama', type: 'text' },
  { key: 'secondary_cta_label', label: 'Label CTA Sekunder', type: 'text' },
  { key: 'secondary_cta_url', label: 'URL CTA Sekunder', type: 'text' },
  { key: 'video_url', label: 'URL Video', type: 'url' },
  { key: 'background_image', label: 'Background Image', type: 'file', accept: 'image/*', previewKey: 'background_image_url' },
  { key: 'hero_image', label: 'Hero Image', type: 'file', accept: 'image/*', previewKey: 'hero_image_url' },
]

const historyFields = [
  { key: 'title', label: 'Judul', type: 'text' },
  { key: 'subtitle', label: 'Subjudul', type: 'text' },
  { key: 'body', label: 'Isi Sejarah', type: 'textarea', rows: 5 },
  { key: 'icon', label: 'Label Ikon', type: 'text' },
  { key: 'image', label: 'Gambar', type: 'file', accept: 'image/*', previewKey: 'image_url' },
]

const organizationFields = [
  { key: 'name', label: 'Nama', type: 'text' },
  { key: 'position', label: 'Jabatan', type: 'text' },
  { key: 'group_name', label: 'Kelompok', type: 'text' },
  { key: 'short_bio', label: 'Bio Singkat', type: 'textarea', rows: 4 },
  { key: 'photo', label: 'Dokumen Struktur (PDF)', type: 'file', accept: '.pdf,application/pdf', previewKey: 'document_url', previewType: 'pdf', previewTypeKey: 'media_type' },
]

const activityFields = [
  { key: 'title', label: 'Judul', type: 'text' },
  { key: 'summary', label: 'Ringkasan', type: 'textarea', rows: 4 },
  { key: 'event_date', label: 'Tanggal', type: 'date' },
  { key: 'location', label: 'Lokasi', type: 'text' },
  { key: 'detail_url', label: 'URL Detail', type: 'url' },
  { key: 'image', label: 'Gambar', type: 'file', accept: 'image/*', previewKey: 'image_url' },
]

const supportFields = [
  { key: 'name', label: 'Nama Support', type: 'text' },
  { key: 'website_url', label: 'Website URL', type: 'url' },
  { key: 'logo', label: 'Logo', type: 'file', accept: 'image/*', previewKey: 'logo_url' },
]

const ktaFields = [
  { key: 'title', label: 'Judul', type: 'text' },
  { key: 'description', label: 'Deskripsi', type: 'textarea', rows: 4 },
  { key: 'checklist_text', label: 'Checklist per Baris', type: 'textarea', rows: 6 },
  { key: 'cta_label', label: 'Label CTA', type: 'text' },
  { key: 'cta_url', label: 'URL CTA', type: 'text' },
  { key: 'banner_image', label: 'Banner', type: 'file', accept: 'image/*', previewKey: 'banner_image_url' },
]

function onSettingsFileChange(event: Event, key: 'logo' | 'favicon') {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] ?? null

  settingsForm[key] = file

  if (!file) {
    if (key === 'logo') logoPreview.value = props.settings.logo_url ?? null
    if (key === 'favicon') faviconPreview.value = props.settings.favicon_url ?? null
    return
  }

  const preview = URL.createObjectURL(file)

  if (key === 'logo') logoPreview.value = preview
  if (key === 'favicon') faviconPreview.value = preview
}

function submitSettings() {
  settingsForm.transform((data) => ({ ...data, _method: 'put' }))

  settingsForm.post('/landing-cms/settings', {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => {
      settingsForm.transform((data) => data)
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Landing CMS" />

    <div class="space-y-6 p-6">
      <div v-if="flash.success" class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
        {{ flash.error }}
      </div>

      <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.22),_transparent_28%),radial-gradient(circle_at_88%_12%,_rgba(242,201,76,0.24),_transparent_24%),linear-gradient(135deg,_#f8fbf5_0%,_#fffef7_46%,_#eef8ef_100%)] p-6 shadow-[0_28px_80px_-44px_rgba(11,107,49,0.42)] dark:border-white/10 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.16),_transparent_28%),radial-gradient(circle_at_88%_12%,_rgba(242,201,76,0.12),_transparent_24%),linear-gradient(135deg,_#081711_0%,_#102019_46%,_#17261d_100%)] dark:shadow-none">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
          <div>
            <p class="inline-flex rounded-full border border-[#d7ebd9] bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#123322] dark:text-[#9fe7a8]">Superadmin Only</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">Landing CMS dan Pendaftaran Publik</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
              Kelola seluruh section landing page publik dan buka queue pengajuan anggota dari satu area kerja.
            </p>
          </div>
          <div class="grid gap-4 sm:grid-cols-3">
            <a href="#settings" class="rounded-[1.5rem] border border-white/70 bg-white/85 p-5 backdrop-blur dark:border-white/10 dark:bg-white/6">
              <Settings2 class="size-5 text-[#0b6b31] dark:text-[#9fe7a8]" />
              <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">Site Settings</p>
            </a>
            <a href="#heroes" class="rounded-[1.5rem] border border-white/70 bg-white/85 p-5 backdrop-blur dark:border-white/10 dark:bg-white/6">
              <LayoutPanelTop class="size-5 text-[#0b6b31] dark:text-[#9fe7a8]" />
              <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">Section CMS</p>
            </a>
            <Link href="/landing-cms/pengajuan-publik" class="rounded-[1.5rem] border border-[#d7ebd9] bg-[#0b6b31] p-5 text-white shadow-[0_24px_70px_-38px_rgba(11,107,49,0.9)] dark:border-[#1f4932] dark:bg-[#0d2419] dark:shadow-none">
              <FileStack class="size-5" />
              <p class="mt-4 text-sm font-semibold">Pengajuan Publik</p>
            </Link>
          </div>
        </div>
      </section>

      <section id="settings" class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Site Settings</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Branding dan SEO Dasar</h2>
          </div>
          <Link href="/landing-cms/pengajuan-publik" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0b6b31] dark:text-[#9fe7a8]">
            <Globe2 class="size-4" />
            Buka Queue Pengajuan
          </Link>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.95fr]">
          <form class="grid gap-5 md:grid-cols-2" @submit.prevent="submitSettings">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Situs</label>
              <input v-model="settingsForm.site_name" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="settingsForm.errors.site_name" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Tagline</label>
              <input v-model="settingsForm.site_tagline" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
              <InputError class="mt-2" :message="settingsForm.errors.site_tagline" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
              <input v-model="settingsForm.email" type="email" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Telepon</label>
              <input v-model="settingsForm.telepon" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Alamat</label>
              <textarea v-model="settingsForm.alamat" rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Footer Text</label>
              <textarea v-model="settingsForm.footer_text" rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">SEO Title</label>
              <input v-model="settingsForm.seo_title" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">SEO Keywords</label>
              <input v-model="settingsForm.seo_keywords" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">SEO Description</label>
              <textarea v-model="settingsForm.seo_description" rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" />
            </div>
            <div><label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Facebook URL</label><input v-model="settingsForm.facebook_url" type="url" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" /></div>
            <div><label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Instagram URL</label><input v-model="settingsForm.instagram_url" type="url" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" /></div>
            <div><label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">YouTube URL</label><input v-model="settingsForm.youtube_url" type="url" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" /></div>
            <div><label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">LinkedIn URL</label><input v-model="settingsForm.linkedin_url" type="url" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white" /></div>
            <div class="md:col-span-2">
              <Button type="submit" :disabled="settingsForm.processing">{{ settingsForm.processing ? 'Menyimpan...' : 'Simpan Site Settings' }}</Button>
            </div>
          </form>

          <div class="grid gap-5">
            <div class="rounded-[1.6rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Logo</label>
              <input type="file" accept="image/*" class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm dark:border-slate-700" @change="onSettingsFileChange($event, 'logo')" />
              <div v-if="logoPreview" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                <img :src="logoPreview" class="h-44 w-full object-cover" alt="Preview logo" />
              </div>
              <InputError class="mt-2" :message="settingsForm.errors.logo" />
            </div>
            <div class="rounded-[1.6rem] border border-slate-200 p-5 dark:border-slate-700">
              <label class="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Favicon</label>
              <input type="file" accept="image/*" class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm dark:border-slate-700" @change="onSettingsFileChange($event, 'favicon')" />
              <div v-if="faviconPreview" class="mt-4 flex h-32 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-[#102019]">
                <img :src="faviconPreview" class="h-24 w-24 object-cover" alt="Preview favicon" />
              </div>
              <InputError class="mt-2" :message="settingsForm.errors.favicon" />
            </div>
          </div>
        </div>
      </section>

      <AdminSectionEditor section="heroes" title="Hero" description="Section utama landing page. Gunakan satu item published dengan sort order terkecil untuk tampil di root." :items="heroes" :fields="heroFields" :summary-keys="['title', 'primary_cta_label']" />
      <AdminSectionEditor section="history" title="Sejarah" description="Kelola blok sejarah organisasi secara modular." :items="historyItems" :fields="historyFields" :summary-keys="['title', 'subtitle']" />
      <AdminSectionEditor section="organization" title="Struktur Organisasi" description="Khusus section ini gunakan dokumen PDF agar tampil sebagai preview struktur organisasi di landing page." :items="organizationItems" :fields="organizationFields" :summary-keys="['name', 'position']" />
      <AdminSectionEditor section="activities" title="Kegiatan" description="Kegiatan publik yang akan ditampilkan pada landing page." :items="activities" :fields="activityFields" :summary-keys="['title', 'location']" />
      <AdminSectionEditor section="support" title="Support" description="Logo dan tautan pendukung organisasi." :items="supportItems" :fields="supportFields" :summary-keys="['name', 'website_url']" />
      <AdminSectionEditor section="kta" title="Blok KTA" description="CTA khusus pendaftaran anggota publik." :items="ktaBlocks" :fields="ktaFields" :summary-keys="['title', 'cta_label']" />
    </div>
  </AppLayout>
</template>
