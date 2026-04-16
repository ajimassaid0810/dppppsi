<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowRight, LoaderCircle, ShieldCheck, Sparkles } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Masuk ke portal PPSI"
        description="Akses dashboard organisasi untuk mengelola anggota, wilayah, dan proses verifikasi secara lebih rapi."
    >
        <Head title="Login" />

        <div v-if="status" class="mb-6 rounded-2xl border border-[#d7ebd9] bg-[#eef8ef] px-4 py-3 text-sm font-medium text-[#0b6b31] dark:border-[#1f4932] dark:bg-[#0f2d1f] dark:text-[#9fe7a8]">
            {{ status }}
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-[#d7ebd9] bg-[#f5fbf5] p-4 dark:border-[#1f4932] dark:bg-[#10261c]">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-[#0b6b31] text-white">
                    <ShieldCheck class="size-5" />
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Akses Terkontrol</p>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-300">Login dipusatkan untuk admin DPP, DPW, DPD, dan DPC.</p>
            </div>
            <div class="rounded-2xl border border-[#f4df95] bg-[#fff8df] p-4 dark:border-[#5b4a16] dark:bg-[#2a220d]">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-[#d4a514] text-white">
                    <Sparkles class="size-5" />
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-[#fff8df]">Portal Modern</p>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-[#f4e7b1]">Tampilan baru dibuat lebih segar, tetap formal, dan fokus ke pekerjaan.</p>
            </div>
        </div>

        <Form
            v-bind="AuthenticatedSessionController.store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="admin@ppsi.local"
                        class="h-12 rounded-2xl border-slate-200 bg-white px-4 shadow-none transition focus:border-[#0b6b31] focus:ring-[#0b6b31]/20 dark:border-slate-700 dark:bg-[#10261c] dark:text-white dark:placeholder:text-slate-400"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Password</Label>
                        <TextLink v-if="canResetPassword" :href="request()" class="text-sm font-medium text-[#0b6b31] hover:text-[#095726] dark:text-[#9fe7a8] dark:hover:text-[#c3efca]" :tabindex="5">
                            Lupa password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="h-12 rounded-2xl border-slate-200 bg-white px-4 shadow-none transition focus:border-[#0b6b31] focus:ring-[#0b6b31]/20 dark:border-slate-700 dark:bg-[#10261c] dark:text-white dark:placeholder:text-slate-400"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-slate-700 dark:bg-[#10261c]/70">
                    <Label for="remember" class="flex items-center space-x-3 text-sm font-medium text-slate-700 dark:text-slate-200">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Ingat sesi saya</span>
                    </Label>
                    <span class="text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Secure Access</span>
                </div>

                <Button
                    type="submit"
                    class="mt-2 h-13 w-full rounded-2xl bg-[linear-gradient(135deg,#0b6b31_0%,#1b8e3f_58%,#f2c94c_100%)] text-base font-semibold text-white shadow-[0_18px_35px_-18px_rgba(11,107,49,0.7)] transition hover:brightness-[1.03]"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    <span v-else class="inline-flex items-center gap-2">
                        Masuk ke Dashboard
                        <ArrowRight class="size-4" />
                    </span>
                </Button>
            </div>

            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-3 text-center text-sm leading-6 text-slate-500 dark:border-slate-700 dark:bg-[#10261c]/60 dark:text-slate-300">
                Registrasi akun publik dinonaktifkan. Jika membutuhkan akses baru, hubungi administrator organisasi.
            </div>
        </Form>
    </AuthBase>
</template>
