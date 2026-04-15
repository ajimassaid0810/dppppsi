<script setup lang="ts">
import { reactive } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';


// inisialisasi form pakai useForm
const form = useForm({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'admin_cabang'
});

// submit handler
const submit = () => {
  form.post('/register', {
    onSuccess: () => {
      form.reset('password', 'password_confirmation');
    },
  });
};
</script>

<template>
  <AuthBase
    title="Create an account"
    description="Enter your details below to create your account"
  >
    <Head title="Register" />

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <!-- Username -->
        <div class="grid gap-2">
          <Label for="username">Username</Label>
          <Input
            id="username"
            type="text"
            required
            autofocus
            autocomplete="username"
            v-model="form.username"
            placeholder="Username"
          />
          <InputError :message="form.errors.username" />
        </div>

        <!-- Email -->
        <div class="grid gap-2">
          <Label for="email">Email address</Label>
          <Input
            id="email"
            type="email"
            autocomplete="email"
            v-model="form.email"
            placeholder="email@example.com"
          />
          <InputError :message="form.errors.email" />
        </div>

        

        <!-- Password -->
        <div class="grid gap-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            type="password"
            required
            autocomplete="new-password"
            v-model="form.password"
            placeholder="Password"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
          <Label for="password_confirmation">Confirm password</Label>
          <Input
            id="password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            v-model="form.password_confirmation"
            placeholder="Confirm password"
          />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <!-- Hidden Role -->
        <input type="hidden" name="role" v-model="form.role" />

        <!-- Submit -->
        <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
          <LoaderCircle
            v-if="form.processing"
            class="mr-2 h-4 w-4 animate-spin"
          />
          Create account
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Already have an account?
        <TextLink
          :href="login()"
          class="underline underline-offset-4"
        >
          Log in
        </TextLink>
      </div>
    </form>
  </AuthBase>
</template>
