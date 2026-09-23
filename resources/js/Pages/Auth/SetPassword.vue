<script setup>
import { Form, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PasswordInput from '@/Components/ui/PasswordInput.vue'

defineProps({
    title: { type: String, default: 'Set your password' },
    invitation: { type: Object, required: true },
})
</script>

<template>
    <AuthLayout
        :title="title"
        heading="Set your password"
        :subheading="`Join ${invitation.workspace?.name || 'the workspace'} and choose a password for your account.`"
    >
        <Form :action="`/invitations/${invitation.token}`" method="post" class="pm-auth__form" #default="{ errors, processing }">
            <div class="pm-auth__field">
                <label class="ti-form-label" for="name">Full name</label>
                <div class="pm-auth__control">
                    <i class="ri-user-3-line" aria-hidden="true"></i>
                    <input id="name" type="text" class="form-control" :value="invitation.name" autocomplete="name" readonly />
                </div>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="email">Email</label>
                <div class="pm-auth__control">
                    <i class="ri-mail-line" aria-hidden="true"></i>
                    <input id="email" type="email" class="form-control" :value="invitation.email" autocomplete="username" readonly />
                </div>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password">Password</label>
                <PasswordInput id="password" name="password" autocomplete="new-password" required autofocus />
                <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
            </div>

            <div class="pm-auth__field">
                <label class="ti-form-label" for="password_confirmation">Confirm password</label>
                <PasswordInput id="password_confirmation" name="password_confirmation" icon="ri-lock-password-line" autocomplete="new-password" required />
                <p v-if="errors.password_confirmation" class="pm-auth__error">{{ errors.password_confirmation }}</p>
            </div>

            <button type="submit" class="pm-focus-cta" :disabled="processing">
                {{ processing ? 'Saving…' : 'Set password and continue' }}
            </button>
        </Form>

        <p class="pm-auth__switch">
            Already activated?
            <Link href="/login" class="pm-auth__link">Sign in</Link>
        </p>
    </AuthLayout>
</template>
