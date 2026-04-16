<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative min-h-dvh overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.24),_transparent_30%),radial-gradient(circle_at_85%_15%,_rgba(242,201,76,0.35),_transparent_25%),linear-gradient(135deg,_#f6f8ee_0%,_#fffef7_48%,_#edf7ef_100%)] text-slate-900 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_28%),radial-gradient(circle_at_82%_16%,_rgba(242,201,76,0.15),_transparent_20%),linear-gradient(135deg,_#071711_0%,_#0b2017_48%,_#10261e_100%)] dark:text-slate-100"
    >
        <div class="absolute inset-0 opacity-40">
            <div class="absolute left-[8%] top-[12%] h-32 w-32 rounded-full bg-[#73c57d]/50 blur-3xl dark:bg-[#1b8e3f]/35" />
            <div class="absolute right-[10%] top-[22%] h-40 w-40 rounded-full bg-[#f2c94c]/70 blur-3xl dark:bg-[#f2c94c]/25" />
            <div class="absolute bottom-[8%] left-[22%] h-48 w-48 rounded-full bg-white/80 blur-3xl dark:bg-[#d9f99d]/8" />
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.03)_1px,transparent_1px)] bg-[size:34px_34px] dark:bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)]" />

        <div class="relative mx-auto grid min-h-dvh w-full max-w-7xl items-center gap-10 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-10">
            <section class="hidden lg:flex lg:flex-col lg:justify-between">
                <div class="max-w-xl space-y-8">
                    <Link :href="home()" class="inline-flex items-center gap-3 rounded-full border border-white/70 bg-white/70 px-4 py-2 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 dark:shadow-none">
                        <span class="flex size-10 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg dark:bg-[#f2c94c] dark:text-[#10261e]">
                            <AppLogoIcon class="size-5 fill-current" />
                        </span>
                        <span>
                            <span class="block text-xs font-medium uppercase tracking-[0.32em] text-slate-500 dark:text-slate-400">Portal Nasional</span>
                            <span class="block text-base font-semibold text-slate-900 dark:text-white">PPSI</span>
                        </span>
                    </Link>

                    <div class="space-y-5">
                        <p class="inline-flex rounded-full border border-[#d7ebd9] bg-[#eef8ef] px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#0f2d1f] dark:text-[#9fe7a8]">
                            Dashboard Verifikasi Organisasi
                        </p>
                        <h1 class="max-w-2xl font-['League_Gothic'] text-6xl uppercase leading-none tracking-[0.04em] text-slate-950 dark:text-[#fff8df] xl:text-8xl">
                            Sistem Anggota yang Ringkas, Modern, dan Siap Pakai
                        </h1>
                        <p class="max-w-lg text-lg leading-8 text-slate-600 dark:text-slate-300">
                            Kelola alur DPW, DPD, dan DPC dalam satu portal yang terasa profesional, cepat dipahami, dan nyaman dipakai setiap hari.
                        </p>
                    </div>
                </div>

                <div class="grid max-w-2xl gap-4 xl:grid-cols-3">
                    <div class="rounded-3xl border border-white/80 bg-white/75 p-5 shadow-[0_20px_60px_-30px_rgba(15,23,42,0.35)] backdrop-blur dark:border-white/10 dark:bg-white/6 dark:shadow-none">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400 dark:text-slate-500">Alur Kerja</p>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">DPD ke DPW</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Pengajuan anggota lebih terstruktur dengan proses verifikasi yang jelas.</p>
                    </div>
                    <div class="rounded-3xl border border-white/80 bg-slate-900 p-5 text-white shadow-[0_20px_60px_-30px_rgba(15,23,42,0.65)] dark:border-[#1f4932] dark:bg-[#0d2419] dark:shadow-none">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/60 dark:text-[#b8d9b9]">Data Inti</p>
                        <p class="mt-3 text-2xl font-semibold">Wilayah Nasional</p>
                        <p class="mt-2 text-sm leading-6 text-white/70">Struktur provinsi, kota/kabupaten, dan kecamatan siap dipakai untuk administrasi.</p>
                    </div>
                    <div class="rounded-3xl border border-white/80 bg-amber-50/90 p-5 shadow-[0_20px_60px_-30px_rgba(180,83,9,0.35)] backdrop-blur dark:border-[#5b4a16] dark:bg-[#2a220d] dark:shadow-none">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700/70 dark:text-[#f2c94c]">Pengalaman</p>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-[#fff8df]">Cepat & Fokus</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-[#f4e7b1]">Tampilan dibuat lebih segar tanpa meninggalkan kesan resmi organisasi.</p>
                    </div>
                </div>
            </section>

            <section class="w-full">
                <div class="mx-auto w-full max-w-[480px] rounded-[2rem] border border-white/80 bg-white/88 p-6 shadow-[0_30px_80px_-32px_rgba(15,23,42,0.42)] backdrop-blur-xl dark:border-white/10 dark:bg-[#0f1f18]/80 dark:shadow-none sm:p-8">
                    <div class="mb-8 flex items-center gap-4 lg:hidden">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg dark:bg-[#f2c94c] dark:text-[#10261e]">
                            <AppLogoIcon class="size-6 fill-current" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400 dark:text-slate-500">Portal Nasional</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">PPSI</p>
                        </div>
                    </div>

                    <div class="mb-8 space-y-2 text-left">
                        <h1 v-if="title" class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ title }}</h1>
                        <p v-if="description" class="text-sm leading-6 text-slate-500 dark:text-slate-300">{{ description }}</p>
                    </div>

                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>
