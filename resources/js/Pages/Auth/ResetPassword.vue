<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PasswordInput from '@/Components/ui/PasswordInput.vue'

defineProps({
    title: { type: String, default: 'Choose a new password' },
    email: { type: String, default: '' },
    token: { type: String, default: '' },
})
</script>

<template>
    <AuthLayout :title="title" heading="Set a new password" subheading="Choose a strong password for your workspace account.">
        <Form action="/reset-password" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <input type="hidden" name="token" :value="token" />

            <div class="pm-auth__field">
                <label class="ti-form-label" for="email">Email</label>
                <div class="pm-auth__control">
                    <i class="ri-mail-line" aria-hidden="true"></i>
                    <input id="email" name="email" type="email" class="form-control" :value="email" autocomplete="username" required />
                </div>
                <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">New password</label>
                <PasswordInput id="password" name="password" autocomplete="new-password" required autofocus />
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password_confirmation">Confirm password</label>
                <PasswordInput id="password_confirmation" name="password_confirmation" icon="ri-lock-password-line" autocomplete="new-password" required />
                <p v-if="errors.password_confirmation" class="pm-auth__error">{{ errors.password_confirmation }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Saving…' : 'Reset password' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            <Link href="/login" class="pm-auth__link">Back to sign in</Link>
        </p>
    </AuthLayout>
</template>
