<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const activeTab = ref('profile');
</script>

<template>
    <AppLayout title="Profil Saya">
        <!-- Header Halaman -->
        <div class="flex flex-col mb-6">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pengaturan Akun
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400">
                Kelola informasi akun, kata sandi, dan preferensi keamanan Anda.
            </p>
        </div>

        <!-- Profil Singkat & Navigasi Cepat -->
        <div class="mb-6 p-5 sm:p-6 border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 rounded-lg shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img
                        :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name"
                        class="size-16 rounded-full object-cover border-2 border-primary-500/20 dark:border-primary-400/20"
                    />
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $page.props.auth.user.name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $page.props.auth.user.email }}
                        </p>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                                Akun Terverifikasi
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/60 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md transition-colors"
                    >
                        <svg class="size-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        Pesanan Saya
                    </Link>

                    <Link
                        :href="route('address.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/60 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md transition-colors"
                    >
                        <svg class="size-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Atur Alamat
                    </Link>

                    <Link
                        :href="route('admin.show')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/60 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md transition-colors"
                    >
                        <svg class="size-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.614A2.993 2.993 0 009 9.35c.66 0 1.282-.213 1.792-.577a3.002 3.002 0 004.416 0c.51.364 1.132.577 1.792.577a2.993 2.993 0 002.458-.964 3.001 3.001 0 003.75.614m-16.5 0a3.001 3.001 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 0c.34 0 .668-.056.974-.16" />
                        </svg>
                        Jualan Saya
                    </Link>
                </div>
            </div>
        </div>

        <!-- Navigasi Tab Profil -->
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 mb-8 overflow-x-auto">
            <button
                type="button"
                @click="activeTab = 'profile'"
                :class="[
                    activeTab === 'profile'
                        ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400 font-semibold'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'pb-3 px-3.5 border-b-2 text-sm transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer'
                ]"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                Informasi Akun
            </button>

            <button
                v-if="$page.props.jetstream.canUpdatePassword || $page.props.jetstream.canManageTwoFactorAuthentication"
                type="button"
                @click="activeTab = 'security'"
                :class="[
                    activeTab === 'security'
                        ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400 font-semibold'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'pb-3 px-3.5 border-b-2 text-sm transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer'
                ]"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                Keamanan & Kata Sandi
            </button>

            <button
                type="button"
                @click="activeTab = 'sessions'"
                :class="[
                    activeTab === 'sessions'
                        ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400 font-semibold'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600',
                    'pb-3 px-3.5 border-b-2 text-sm transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer'
                ]"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                </svg>
                Sesi & Akun
            </button>
        </div>

        <!-- Konten Tab -->
        <div>
            <!-- Tab 1: Informasi Akun -->
            <div v-show="activeTab === 'profile'">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                </div>
            </div>

            <!-- Tab 2: Keamanan & Kata Sandi -->
            <div v-show="activeTab === 'security'">
                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <SectionBorder v-if="$page.props.jetstream.canUpdatePassword" />

                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                    />
                </div>
            </div>

            <!-- Tab 3: Sesi & Pengaturan Akun -->
            <div v-show="activeTab === 'sessions'">
                <LogoutOtherBrowserSessionsForm :sessions="sessions" />

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
