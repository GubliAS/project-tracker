<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    title: { type: String, default: 'Reset password' },
    status: { type: String, default: null },
})
</script>

<template>
    <AuthLayout :title="title" heading="Forgot your password?" subheading="Enter your email and we will send a reset link.">
        <p v-if="status" class="pm-auth__status">{{ status }}</p>

        <Form action="/forgot-password" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="email">Email</label>
                <div class="pm-auth__control">
                    <i class="ri-mail-line" aria-hidden="true"></i>
                    <input id="email" name="email" type="email" class="form-control" autocomplete="username" required autofocus />
                </div>
                <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Sending link…' : 'Email reset link' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            Remembered it?
            <Link href="/login" class="pm-auth__link">Back to sign in</Link>
        </p>
    </AuthLayout>
</template>
