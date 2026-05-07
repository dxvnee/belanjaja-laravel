<script setup>
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import { theme } from "@/theme.js";
import AppNavbar from "@/Components/AppNavbar.vue";
import BottomBar from "@/Components/BottomBar.vue";
import AppDialog from "@/Components/AppDialog.vue";
import LoadingDialog from "@/Components/LoadingDialog.vue";

defineProps({
    title: String,
});
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div
            class="min-h-screen flex flex-col"
            :style="{ backgroundColor: theme.background }"
        >
            <AppNavbar />

            <header
                v-if="$slots.header"
                class="bg-white dark:bg-gray-800 shadow"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1">
                <div class="py-6 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>

            <LoadingDialog />
            <AppDialog />

            <BottomBar />
        </div>
    </div>
</template>
