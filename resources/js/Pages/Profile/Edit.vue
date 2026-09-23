<script setup>
import { Form, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import PasswordInput from '@/Components/ui/PasswordInput.vue'

defineProps({
    title: { type: String, default: 'Account' },
    status: { type: String, default: null },
})

const page = usePage()
const user = computed(() => page.props.auth?.user ?? {})
const firstName = computed(() => String(user.value.name || 'there').split(' ')[0])
const initials = computed(() => {
    const parts = String(user.value.name || '?').split(' ').filter(Boolean).slice(0, 2)

    return parts.map((part) => part[0]).join('').toUpperCase() || '?'
})
</script>

<template>
    <AppLayout :title="title">
        <div class="pm-dash">
            <PageHeader :title="title" subtitle="Manage your name, email, and password." />

            <div class="pm-profile-wrap">
                <p v-if="status === 'profile-updated'" class="pm-auth__status">Your profile has been updated.</p>
                <p v-else-if="status === 'password-updated'" class="pm-auth__status">Your password has been updated.</p>

                <div class="pm-profile">
                <aside class="pm-profile__hero">
                    <span class="pm-profile__glow" aria-hidden="true"></span>
                    <div class="pm-profile__copy">
                        <span class="pm-focus-pill">
                            <i class="ri-user-smile-fill" aria-hidden="true"></i>
                            Your account
                        </span>
                        <div class="pm-profile__who">
                            <span class="pm-initials">{{ initials }}</span>
                            <div>
                                <h2>Hello, {{ firstName }}</h2>
                                <p>Keep your details current so invites and updates reach the right inbox.</p>
                            </div>
                        </div>
                        <ul class="pm-auth__chips">
                            <li><i class="ri-mail-line" aria-hidden="true"></i> {{ user.email }}</li>
                            <li><i class="ri-shield-user-line" aria-hidden="true"></i> Signed in</li>
                        </ul>
                    </div>
                    <div class="pm-profile__art">
                        <img src="/assets/img/profile-illustration.png" alt="">
                    </div>
                </aside>

                <div class="pm-profile__forms">
                    <div class="box pm-profile__card">
                        <div class="box-header">
                            <h5 class="box-title">Profile details</h5>
                        </div>
                        <div class="box-body">
                            <Form
                                action="/profile"
                                method="patch"
                                class="pm-auth__form pm-profile__form"
                                #default="{ errors, processing }"
                            >
                                <div class="pm-profile__row">
                                    <div class="pm-auth__field">
                                        <label class="ti-form-label" for="name">Full name</label>
                                        <div class="pm-auth__control">
                                            <i class="ri-user-line" aria-hidden="true"></i>
                                            <input id="name" name="name" type="text" class="form-control" :value="user.name" required>
                                        </div>
                                        <p v-if="errors.name" class="pm-auth__error">{{ errors.name }}</p>
                                    </div>

                                    <div class="pm-auth__field">
                                        <label class="ti-form-label" for="email">Email</label>
                                        <div class="pm-auth__control">
                                            <i class="ri-mail-line" aria-hidden="true"></i>
                                            <input id="email" name="email" type="email" class="form-control" :value="user.email" required>
                                        </div>
                                        <p v-if="errors.email" class="pm-auth__error">{{ errors.email }}</p>
                                    </div>
                                </div>

                                <div class="pm-profile__actions">
                                    <button type="submit" class="ti-btn ti-btn-primary" :disabled="processing">
                                        {{ processing ? 'Saving…' : 'Save changes' }}
                                    </button>
                                </div>
                            </Form>
                        </div>
                    </div>

                    <div class="box pm-profile__card">
                        <div class="box-header">
                            <h5 class="box-title">Password</h5>
                        </div>
                        <div class="box-body">
                            <Form
                                action="/password"
                                method="put"
                                reset-on-success
                                class="pm-auth__form pm-profile__form"
                                #default="{ errors, processing }"
                            >
                                <div class="pm-profile__row">
                                    <div class="pm-auth__field">
                                        <label class="ti-form-label" for="current_password">Current password</label>
                                        <PasswordInput id="current_password" name="current_password" autocomplete="current-password" required />
                                        <p v-if="errors.current_password" class="pm-auth__error">{{ errors.current_password }}</p>
                                    </div>

                                    <div class="pm-auth__field">
                                        <label class="ti-form-label" for="password">New password</label>
                                        <PasswordInput id="password" name="password" icon="ri-key-2-line" autocomplete="new-password" required />
                                        <p v-if="errors.password" class="pm-auth__error">{{ errors.password }}</p>
                                    </div>
                                </div>

                                <div class="pm-profile__row">
                                    <div class="pm-auth__field">
                                        <label class="ti-form-label" for="password_confirmation">Confirm password</label>
                                        <PasswordInput id="password_confirmation" name="password_confirmation" icon="ri-checkbox-circle-line" autocomplete="new-password" required />
                                    </div>
                                </div>

                                <div class="pm-profile__actions">
                                    <button type="submit" class="ti-btn ti-btn-primary" :disabled="processing">
                                        {{ processing ? 'Updating…' : 'Update password' }}
                                    </button>
                                </div>
                            </Form>
                        </div>
                    </div>

                    <div class="box pm-profile__card pm-profile__session">
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
        </div>
    </AppLayout>
</template>
