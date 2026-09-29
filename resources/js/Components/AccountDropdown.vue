<script setup>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { useDarkMode } from "@/Composable/useDarkMode";

defineProps({
    logout: Function,
});

const { isDark, initTheme, toggleDarkMode } = useDarkMode();

</script>

<template>

    <Dropdown align="right" width="48">
        <template #trigger>
            <button
                v-if="$page.props.jetstream.managesProfilePhotos"
                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
            >
                <img
                    class="size-8 rounded-full object-cover"
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                />
            </button>

            <span v-else class="inline-flex rounded-md">
                <button
                    type="button"
                    class="inline-flex items-center whitespace-nowrap px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                >
                    {{ $page.props.auth.user.name }}

                    <svg
                        class="ms-2 -me-0.5 size-4"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                </button>
            </span>
        </template>

        <template #content>
            <!-- Account Management -->
            <div class="block px-4 py-2 text-xs text-gray-400">
                Manage Account
            </div>

            <DropdownLink :href="route('profile.show')"> Profile </DropdownLink>

            <DropdownLink :href="route('admin.show')"> Dashboard Penjual </DropdownLink>

            <DropdownLink :href="route('admin.orders')"> Pesanan masuk </DropdownLink>

            <DropdownLink :href="route('orders.index')"> Pembelian saya </DropdownLink>

            <DropdownLink as="button" type="button" @click="toggleDarkMode">
                <div class="flex items-center justify-between w-full">
                    <span>Mode Tampilan</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200">
                        {{ isDark ? '🌙 Dark' : '☀️ Light' }}
                    </span>
                </div>
            </DropdownLink>

            <div class="border-t border-gray-200 dark:border-gray-600" />

            <!-- Authentication -->
            <form @submit.prevent="logout">
                <DropdownLink as="button"> Log Out </DropdownLink>
            </form>
        </template>
    </Dropdown>
</template>
