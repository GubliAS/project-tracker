<script setup>
import { Form, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
    title: { type: String, default: 'Account' },
    status: { type: String, default: null },
})

const page = usePage()
const user = computed(() => page.props.auth?.user ?? {})
</script>

<template>
    <AppLayout :title="title">
        <div class="pm-dash">
            <PageHeader :title="title" subtitle="Manage your name, email, and password." />

            <p v-if="status === 'profile-updated'" class="pm-auth__status">Your profile has been updated.</p>
            <p v-else-if="status === 'password-updated'" class="pm-auth__status">Your password has been updated.</p>

            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 xl:col-span-7">
                    <div class="box">
                        <div class="box-header">
                            <h5 class="box-title">Profile details</h5>
                        </div>
                        <div class="box-body">
                            <Form
                                action="/profile"
                                method="patch"
                                class="pm-auth__form"
                                #default="{ errors, processing }"
                            >
                                <div class="pm-auth__field">
                                    <label class="ti-form-label" for="name">Full name</label>
                                    <input id="name" name="name" type="text" class="form-control" :value="user.name" required />
                                    <p v-if="errors.name" class="pm-auth__error">{{ errors.name }}</p>
                                </div>

                                <div class="pm-auth__field">
                                    <label class="ti-form-label" for="email">Email</label>
                                    <input id="email" name="email" type="email" class="form-control" :value="user.email" required />
                                    <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
                                </div>

                                <button type="submit" class="ti-btn ti-btn-primary" :disabled="processing">
                                    {{ processing ? 'Saving…' : 'Save changes' }}
                                </button>
                            </Form>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-5">
                    <div class="box">
                        <div class="box-header">
                            <h5 class="box-title">Password</h5>
                        </div>
                        <div class="box-body">
                            <Form
                                action="/password"
                                method="put"
                                reset-on-success
                                class="pm-auth__form"
                                #default="{ errors, processing }"
                            >
                                <div class="pm-auth__field">
                                    <label class="ti-form-label" for="current_password">Current password</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required />
                                    <p v-if="errors.current_password" class="pm-auth__error">{{ errors.current_password }}</p>
                                </div>

                                <div class="pm-auth__field">
                                    <label class="ti-form-label" for="password">New password</label>
                                    <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" required />
                                    <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
                                </div>

                                <div class="pm-auth__field">
                                    <label class="ti-form-label" for="password_confirmation">Confirm password</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required />
                                </div>

                                <button type="submit" class="ti-btn ti-btn-primary" :disabled="processing">
                                    {{ processing ? 'Updating…' : 'Update password' }}
                                </button>
                            </Form>
                        </div>
                    </div>

                    <div class="box mt-6">
                        <div class="box-body">
                            <p class="text-sm text-textmuted mb-3">Signed in as <strong>{{ user.email }}</strong></p>
                            <Link href="/logout" method="post" as="button" class="ti-btn ti-btn-light">
                                <i class="ri-logout-box-line me-1"></i> Log out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
