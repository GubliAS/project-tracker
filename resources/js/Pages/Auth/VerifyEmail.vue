<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    title: { type: String, default: 'Verify email' },
    status: { type: String, default: null },
})
</script>

<template>
    <AuthLayout :title="title" heading="Check your inbox" subheading="We sent a verification link to your email. Open it to unlock the workspace.">
        <p v-if="status === 'verification-link-sent'" class="pm-auth__status">
            A fresh verification link has been sent.
        </p>

        <Form action="/email/verification-notification" method="post" class="pm-auth__form" #default="{ processing }">
            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Sending…' : 'Resend verification email' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            <Link href="/logout" method="post" as="button" class="pm-auth__link">Log out</Link>
        </p>
    </AuthLayout>
</template>
