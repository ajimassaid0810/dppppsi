<script setup lang="ts">
import { computed, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Pencil, Plus, Trash2, X } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import InputError from '@/components/InputError.vue'

interface FieldDefinition {
  key: string
  label: string
  type: 'text' | 'textarea' | 'url' | 'date' | 'number' | 'file'
  placeholder?: string
  previewKey?: string
  previewType?: 'image' | 'pdf'
  previewTypeKey?: string
  accept?: string
  rows?: number
}

const props = defineProps<{
  section: string
  title: string
  description: string
  items: Array<Record<string, any>>
  fields: FieldDefinition[]
  summaryKeys: string[]
}>()

const editingId = computed<number | null>(() => (typeof form.id === 'number' ? form.id : null))
const filePreviews = reactive<Record<string, string | null>>({})
const filePreviewTypes = reactive<Record<string, 'image' | 'pdf' | null>>({})
const editingItem = reactive<Record<string, any>>({})

function buildInitialState() {
  const state: Record<string, any> = {
    id: null,
    sort_order: 0,
    is_published: true,
  }

  for (const field of props.fields) {
    state[field.key] = field.type === 'number' ? 0 : field.type === 'file' ? null : ''
  }

  return state
}

const form = useForm<any>(buildInitialState())

function resetForm() {
  form.defaults(buildInitialState())
  form.reset()
  form.clearErrors()
  Object.keys(editingItem).forEach((key) => {
    delete editingItem[key]
  })

  Object.keys(filePreviews).forEach((key) => {
    clearFilePreview(key)
  })
}

function startEdit(item: Record<string, any>) {
  const nextState = buildInitialState()
  nextState.id = item.id
  nextState.sort_order = item.sort_order ?? 0
  nextState.is_published = item.is_published ?? true
  Object.keys(editingItem).forEach((key) => {
    delete editingItem[key]
  })
  Object.assign(editingItem, item)

  for (const field of props.fields) {
    nextState[field.key] = field.type === 'file' ? null : (item[field.key] ?? '')
    clearFilePreview(field.key)
  }

  form.defaults(nextState)
  form.reset()
  form.clearErrors()
}

function submit() {
  if (editingId.value) {
    form.transform((data) => ({ ...data, _method: 'put' }))

    form.post(`/landing-cms/sections/${props.section}/${editingId.value}`, {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => {
        form.transform((data) => data)
      },
      onSuccess: () => {
        resetForm()
      },
    })

    return
  }

  form.post(`/landing-cms/sections/${props.section}`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
    },
  })
}

function removeItem(id: number) {
  if (!window.confirm(`Hapus ${props.title.toLowerCase()} ini?`)) {
    return
  }

  router.delete(`/landing-cms/sections/${props.section}/${id}`, {
    preserveScroll: true,
  })
}

function onFileChange(event: Event, key: string) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] ?? null

  form[key] = file
  clearFilePreview(key)

  if (!file) {
    return
  }

  filePreviewTypes[key] = detectPreviewType(file)
  filePreviews[key] = URL.createObjectURL(file)
}

function fieldError(key: string) {
  return form.errors[key]
}

function previewUrl(item: Record<string, any>, field: FieldDefinition) {
  return field.previewKey ? item[field.previewKey] : null
}

function clearFilePreview(key: string) {
  if (filePreviews[key]?.startsWith('blob:')) {
    URL.revokeObjectURL(filePreviews[key] as string)
  }

  filePreviews[key] = null
  filePreviewTypes[key] = null
}

function detectPreviewType(file: File): 'image' | 'pdf' | null {
  if (file.type === 'application/pdf') {
    return 'pdf'
  }

  if (file.type.startsWith('image/')) {
    return 'image'
  }

  return null
}

function resolvedPreviewType(item: Record<string, any> | null, field: FieldDefinition) {
  if (filePreviewTypes[field.key]) {
    return filePreviewTypes[field.key]
  }

  if (item && field.previewTypeKey && item[field.previewTypeKey]) {
    return item[field.previewTypeKey]
  }

  return field.previewType ?? 'image'
}

function editorPreviewUrl(field: FieldDefinition) {
  if (filePreviews[field.key]) {
    return filePreviews[field.key]
  }

  if (!field.previewKey || !editingItem.id) {
    return null
  }

  return editingItem[field.previewKey] ?? null
}

function previewFrameUrl(url: string | null) {
  return url ? `${url}#toolbar=0&navpanes=0&scrollbar=0&view=FitH` : null
}
</script>

<template>
  <section :id="section" class="rounded-[1.8rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0f1b16]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">{{ title }}</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Kelola {{ title }}</h2>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-300">{{ description }}</p>
      </div>
      <Button type="button" variant="outline" @click="resetForm">
        <Plus class="mr-2 size-4" />
        Form Baru
      </Button>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
      <div class="space-y-4">
        <article
          v-for="item in items"
          :key="item.id"
          class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-[#102019]"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#eef8ef] px-3 py-1 text-xs font-semibold text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]">
                  Urutan {{ item.sort_order ?? 0 }}
                </span>
                <span
                  :class="item.is_published ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200'"
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                >
                  {{ item.is_published ? 'Published' : 'Draft' }}
                </span>
              </div>
              <div v-for="key in summaryKeys" :key="`${item.id}-${key}`">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ key.replaceAll('_', ' ') }}</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                  {{ item[key] || '-' }}
                </p>
              </div>
            </div>
            <div class="flex gap-2">
              <Button size="sm" variant="outline" type="button" @click="startEdit(item)">
                <Pencil class="mr-2 size-4" />
                Edit
              </Button>
              <Button size="sm" variant="destructive" type="button" @click="removeItem(item.id)">
                <Trash2 class="mr-2 size-4" />
                Hapus
              </Button>
            </div>
          </div>

          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <template v-for="field in fields.filter((entry) => entry.previewKey)" :key="`${item.id}-${field.key}-preview`">
              <div v-if="item[field.previewKey || '']" class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-[#0b1713]">
                <iframe
                  v-if="resolvedPreviewType(item, field) === 'pdf'"
                  :src="previewFrameUrl(previewUrl(item, field)) || undefined"
                  class="h-56 w-full bg-white"
                  :title="field.label"
                />
                <img v-else :src="previewUrl(item, field) || ''" class="h-32 w-full object-cover" :alt="field.label" />
              </div>
            </template>
          </div>
        </article>

        <div v-if="items.length === 0" class="rounded-[1.5rem] border border-dashed border-slate-300 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
          Belum ada data untuk section ini.
        </div>
      </div>

      <form class="space-y-5 rounded-[1.5rem] border border-slate-200 p-5 dark:border-slate-800" @submit.prevent="submit">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
              {{ editingId ? 'Edit Item' : 'Tambah Item' }}
            </p>
            <h3 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ title }}</h3>
          </div>
          <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">
            <X class="mr-2 size-4" />
            Batal Edit
          </Button>
        </div>

        <div class="grid gap-5">
          <div v-for="field in fields" :key="field.key">
            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">{{ field.label }}</label>

            <input
              v-if="field.type === 'text' || field.type === 'url' || field.type === 'date' || field.type === 'number'"
              :type="field.type"
              :value="form[field.key]"
              class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
              :placeholder="field.placeholder"
              @input="form[field.key] = ($event.target as HTMLInputElement).value"
            />

            <textarea
              v-else-if="field.type === 'textarea'"
              :value="form[field.key]"
              class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
              :placeholder="field.placeholder"
              :rows="field.rows || 4"
              @input="form[field.key] = ($event.target as HTMLTextAreaElement).value"
            />

            <div v-else class="space-y-3">
              <input
                :accept="field.accept"
                type="file"
                class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                @change="onFileChange($event, field.key)"
              />
              <div v-if="editorPreviewUrl(field)" class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                <iframe
                  v-if="resolvedPreviewType(editingItem, field) === 'pdf'"
                  :src="previewFrameUrl(editorPreviewUrl(field)) || undefined"
                  class="h-72 w-full bg-white"
                  :title="field.label"
                />
                <img v-else :src="editorPreviewUrl(field) || ''" class="h-40 w-full object-cover" :alt="field.label" />
              </div>
            </div>

            <InputError class="mt-2" :message="fieldError(field.key)" />
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Sort Order</label>
              <input
                v-model="form.sort_order"
                type="number"
                min="0"
                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#0b6b31] dark:border-slate-700 dark:bg-[#10261c] dark:text-white"
              />
              <InputError class="mt-2" :message="form.errors.sort_order" />
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">
              <input v-model="form.is_published" type="checkbox" class="size-4 rounded border-slate-300 text-[#0b6b31]" />
              Published
            </label>
          </div>

          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Menyimpan...' : editingId ? 'Perbarui Item' : 'Simpan Item' }}
          </Button>
        </div>
      </form>
    </div>
  </section>
</template>
