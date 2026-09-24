<script setup>
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import AppNavbar from "@/Components/AppNavbar.vue";
import BottomBar from "@/Components/BottomBar.vue";
import AppDialog from "@/Components/AppDialog.vue";
import LoadingDialog from "@/Components/LoadingDialog.vue";

defineProps({
    title: String,
    routeName: String
});
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div
            class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200"
        >
            <AppNavbar :routeName="routeName" />

            <header
                v-if="$slots.header"
                class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1">
                <div class="py-6 mx-auto w-full max-w-7xl px-4 sm:px-20 lg:px-20">
                    <slot />
                </div>
            </main>

            <LoadingDialog />
            <AppDialog />

            <BottomBar />
        </div>
    </div>
</template>
