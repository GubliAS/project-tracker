<script setup>
import { Form } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    title: { type: String, default: 'Confirm password' },
})
</script>

<template>
    <AuthLayout :title="title" heading="Confirm your password" subheading="This is a protected area. Please confirm your password to continue.">
        <Form action="/confirm-password" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">Password</label>
                <div class="pm-auth__control">
                    <i class="ri-lock-2-line" aria-hidden="true"></i>
                    <input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required autofocus />
                </div>
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Confirming…' : 'Confirm' }}
            </button>
        </Form>
    </AuthLayout>
</template>
