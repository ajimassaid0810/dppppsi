<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, BookMarked, Building2, MapPinned, ShieldCheck, Sparkles, Users2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        dpw: number;
        dpd: number;
        dpc: number;
        anggota: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const roleName = computed(() => user.value?.role?.name ?? '');
const roleLabel = computed(() => user.value?.role?.display_name ?? 'Pengguna');

const quickLinks = computed(() => {
    const items = [
        { title: 'Data Anggota', href: '/anggota', icon: Users2, roles: ['superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd'] },
        { title: 'DPW Provinsi', href: '/dpw-provinsi', icon: ShieldCheck, roles: ['superadmin', 'admin_dpp', 'admin_dpw'] },
        { title: 'DPD Kota/Kab', href: '/dpd-kota-kab', icon: Building2, roles: ['superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd'] },
        { title: 'DPC Kecamatan', href: '/dpc-kecamatan', icon: MapPinned, roles: ['superadmin', 'admin_dpp', 'admin_dpw', 'admin_dpd'] },
    ];

    return items.filter((item) => item.roles.includes(roleName.value));
});

const statCards = computed(() => [
    { title: 'DPW Aktif', value: props.stats.dpw, caption: 'Struktur provinsi yang terdaftar', icon: ShieldCheck, accent: 'from-[#0b6b31] to-[#1b8e3f]' },
    { title: 'DPD Terdaftar', value: props.stats.dpd, caption: 'Kota/Kabupaten yang sudah masuk sistem', icon: Building2, accent: 'from-[#d4a514] to-[#f2c94c]' },
    { title: 'DPC Terdata', value: props.stats.dpc, caption: 'Kecamatan aktif dalam jaringan organisasi', icon: MapPinned, accent: 'from-[#0f766e] to-[#22c55e]' },
    { title: 'Anggota Sistem', value: props.stats.anggota, caption: 'Seluruh data anggota yang sudah tercatat', icon: BookMarked, accent: 'from-[#1f2937] to-[#334155]' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <section class="relative overflow-hidden rounded-[2rem] border border-white/60 bg-[radial-gradient(circle_at_top_left,_rgba(11,107,49,0.22),_transparent_28%),radial-gradient(circle_at_85%_20%,_rgba(242,201,76,0.32),_transparent_24%),linear-gradient(135deg,_#f7f8ef_0%,_#fffdf5_45%,_#edf8ef_100%)] p-6 shadow-[0_30px_80px_-40px_rgba(11,107,49,0.45)] dark:border-white/10 dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.16),_transparent_26%),radial-gradient(circle_at_85%_20%,_rgba(242,201,76,0.13),_transparent_22%),linear-gradient(135deg,_#081711_0%,_#0d2017_45%,_#13271d_100%)] dark:shadow-none md:p-8">
                <div class="absolute inset-0 bg-[linear-gradient(rgba(11,107,49,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(11,107,49,0.04)_1px,transparent_1px)] bg-[size:28px_28px] dark:bg-[linear-gradient(rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.04)_1px,transparent_1px)]" />
                <div class="relative grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-5">
                        <p class="inline-flex items-center gap-2 rounded-full border border-[#d7ebd9] bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#0f2d1f] dark:text-[#9fe7a8]">
                            <Sparkles class="size-3.5" />
                            Pusat Kendali Organisasi
                        </p>
                        <div class="space-y-3">
                            <h1 class="font-['League_Gothic'] text-5xl uppercase leading-none tracking-[0.04em] text-slate-950 dark:text-[#fff8df] md:text-7xl">
                                Dashboard PPSI yang Lebih Hidup
                            </h1>
                            <p class="max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-300 md:text-lg">
                                Halo {{ user?.display_name }}. Anda masuk sebagai {{ roleLabel.toLowerCase() }}. Pantau struktur wilayah, data anggota, dan alur administrasi dari satu tempat yang terasa modern namun tetap resmi.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-[0_20px_60px_-35px_rgba(15,23,42,0.4)] backdrop-blur dark:border-white/10 dark:bg-white/6 dark:shadow-none">
                            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-400 dark:text-slate-500">Login Aktif</p>
                            <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">{{ roleLabel }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">Akses menu dan data di sidebar sudah disesuaikan otomatis dengan role Anda.</p>
                        </div>
                        <div class="rounded-3xl border border-[#d7ebd9] bg-[#0b6b31] p-5 text-white shadow-[0_24px_70px_-38px_rgba(11,107,49,0.9)] dark:border-[#1f4932] dark:bg-[#0d2419] dark:shadow-none">
                            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-white/65">Arah Hari Ini</p>
                            <p class="mt-3 text-2xl font-semibold">Fokus ke Data Inti</p>
                            <p class="mt-2 text-sm leading-6 text-white/75">Mulai dari menu yang paling penting untuk role Anda, lalu lanjutkan ke validasi dan pengelolaan anggota.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="card in statCards"
                    :key="card.title"
                    class="rounded-[1.6rem] border border-white/70 bg-white p-5 shadow-[0_24px_60px_-38px_rgba(15,23,42,0.35)] dark:border-white/10 dark:bg-[#0f1b16] dark:shadow-none"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ card.title }}</p>
                            <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ card.value }}</p>
                            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-300">{{ card.caption }}</p>
                        </div>
                        <div :class="`flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br ${card.accent} text-white shadow-lg`">
                            <component :is="card.icon" class="size-5" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <div class="rounded-[1.8rem] border border-white/70 bg-white p-6 shadow-[0_24px_60px_-38px_rgba(15,23,42,0.35)] dark:border-white/10 dark:bg-[#0f1b16] dark:shadow-none">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Quick Access</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Menu Utama Anda</h2>
                        </div>
                        <span class="rounded-full bg-[#eef8ef] px-3 py-1 text-xs font-semibold text-[#0b6b31] dark:bg-[#123322] dark:text-[#9fe7a8]">{{ quickLinks.length }} menu</span>
                    </div>

                    <div class="mt-6 grid gap-4">
                        <Link
                            v-for="item in quickLinks"
                            :key="item.title"
                            :href="item.href"
                            class="group flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50/70 px-5 py-4 transition hover:border-[#b8d9b9] hover:bg-[#f5fbf5] dark:border-white/10 dark:bg-[#122019] dark:hover:border-[#2f6b47] dark:hover:bg-[#16261e]"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex size-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0b6b31_0%,#f2c94c_100%)] text-white shadow-[0_16px_32px_-18px_rgba(11,107,49,0.75)]">
                                    <component :is="item.icon" class="size-5" />
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-300">Buka halaman dan lanjutkan pengelolaan data</p>
                                </div>
                            </div>
                            <ArrowRight class="size-5 text-slate-400 transition group-hover:translate-x-1 group-hover:text-[#0b6b31] dark:text-slate-500 dark:group-hover:text-[#9fe7a8]" />
                        </Link>
                    </div>
                </div>

                <div class="rounded-[1.8rem] border border-[#d7ebd9] bg-[linear-gradient(180deg,#ffffff_0%,#fbfdf8_100%)] p-6 shadow-[0_24px_60px_-38px_rgba(15,23,42,0.35)] dark:border-[#1f4932] dark:bg-[linear-gradient(180deg,#0b1713_0%,#101d17_100%)] dark:shadow-none">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#0b6b31] dark:text-[#9fe7a8]">Insight</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Gambaran Operasional</h2>

                    <div class="mt-6 space-y-5">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f1b16]">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-slate-900 dark:text-white">Sebaran Wilayah</p>
                                <span class="rounded-full bg-[#fff7d6] px-3 py-1 text-xs font-semibold text-[#9a6b00] dark:bg-[#3a2b0e] dark:text-[#f4e7b1]">Nasional</span>
                            </div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">DPW</p>
                                    <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-white">{{ stats.dpw }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">DPD</p>
                                    <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-white">{{ stats.dpd }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">DPC</p>
                                    <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-white">{{ stats.dpc }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-[#0f172a] p-5 text-white dark:bg-[linear-gradient(135deg,#123322_0%,#0d2419_55%,#3a2b0e_100%)]">
                            <p class="text-sm font-semibold text-white/80 dark:text-[#d7f3db]">Anggota Terdata</p>
                            <p class="mt-3 text-5xl font-semibold tracking-tight">{{ stats.anggota }}</p>
                            <p class="mt-3 max-w-lg text-sm leading-6 text-white/65 dark:text-[#f4e7b1]">
                                Angka ini memberi gambaran kapasitas data yang sudah masuk ke sistem dan bisa dijadikan dasar untuk validasi bertahap di level organisasi.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
