<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    title: { type: String, default: 'Sign in' },
    status: { type: String, default: null },
})
</script>

<template>
    <AuthLayout :title="title" heading="Welcome back" subheading="Sign in to continue your project workspace.">
        <p v-if="status" class="pm-auth__status">{{ status }}</p>

        <Form action="/login" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="email">Email</label>
                <div class="pm-auth__control">
                    <i class="ri-mail-line" aria-hidden="true"></i>
                    <input id="email" name="email" type="email" class="form-control" autocomplete="username" required autofocus />
                </div>
                <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">Password</label>
                <div class="pm-auth__control">
                    <i class="ri-lock-2-line" aria-hidden="true"></i>
                    <input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required />
                </div>
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <div class="pm-auth__row">
                <label class="pm-auth__remember">
                    <input type="checkbox" name="remember" value="1" />
                    Remember me
                </label>
                <Link href="/forgot-password" class="pm-auth__link">Forgot password?</Link>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Signing in…' : 'Sign in' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            New to the workspace?
            <Link href="/register" class="pm-auth__link">Create an account</Link>
        </p>
    </AuthLayout>
</template>
