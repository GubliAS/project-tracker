<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PasswordInput from '@/Components/ui/PasswordInput.vue'

defineProps({
    title: { type: String, default: 'Create account' },
})
</script>

<template>
    <AuthLayout :title="title" heading="Create your account" subheading="Join the team workspace in a minute.">
        <Form action="/register" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="name">Full name</label>
                <div class="pm-auth__control">
                    <i class="ri-user-3-line" aria-hidden="true"></i>
                    <input id="name" name="name" type="text" class="form-control" autocomplete="name" required autofocus />
                </div>
                <p v-if="errors.name" class="pm-auth__error">{{ errors.name }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="email">Email</label>
                <div class="pm-auth__control">
                    <i class="ri-mail-line" aria-hidden="true"></i>
                    <input id="email" name="email" type="email" class="form-control" autocomplete="username" required />
                </div>
                <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">Password</label>
                <PasswordInput id="password" name="password" autocomplete="new-password" required />
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password_confirmation">Confirm password</label>
                <PasswordInput id="password_confirmation" name="password_confirmation" icon="ri-lock-password-line" autocomplete="new-password" required />
                <p v-if="errors.password_confirmation" class="pm-auth__error">{{ errors.password_confirmation }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Creating account…' : 'Create account' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            Already have an account?
            <Link href="/login" class="pm-auth__link">Sign in</Link>
        </p>
    </AuthLayout>
</template>
