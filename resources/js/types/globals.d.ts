import type { Page, PageProps as InertiaPageProps } from '@inertiajs/core'
import type { Router } from '@inertiajs/vue3'
import { createHeadManager } from '@inertiajs/core'
import type { AppPageProps } from '@/types/index'

// Extend ImportMeta interface for Vite
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string
    [key: string]: string | boolean | undefined
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>
  }
}

// Extend Inertia PageProps
declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps, AppPageProps {
    flash: {
      success?: string
      error?: string
      info?: string
    }
    errors: Record<string, string>
  }
}

// Extend Vue Component instance
declare module 'vue' {
  interface ComponentCustomProperties {
    $inertia: typeof Router
    $page: Page<PageProps>
    $headManager: ReturnType<typeof createHeadManager>
  }
}
