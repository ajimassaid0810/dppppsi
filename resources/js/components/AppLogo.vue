<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#0b6b31_0%,#1b8e3f_58%,#f2c94c_100%)] text-white shadow-[0_10px_25px_-12px_rgba(11,107,49,0.85)]">
        <AppLogoIcon class="size-5 fill-current text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold text-sidebar-foreground">Portal PPSI</span>
        <span class="truncate text-xs text-sidebar-foreground/60">Administrasi Organisasi</span>
    </div>
</template>
