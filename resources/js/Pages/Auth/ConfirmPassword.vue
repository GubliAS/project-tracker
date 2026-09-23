<script setup>
import { Form } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PasswordInput from '@/Components/ui/PasswordInput.vue'

defineProps({
    title: { type: String, default: 'Confirm password' },
})
</script>

<template>
    <AuthLayout :title="title" heading="Confirm your password" subheading="This is a protected area. Please confirm your password to continue.">
        <Form action="/confirm-password" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">Password</label>
                <PasswordInput id="password" name="password" autocomplete="current-password" required autofocus />
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Confirming…' : 'Confirm' }}
            </button>
        </Form>
    </AuthLayout>
</template>
