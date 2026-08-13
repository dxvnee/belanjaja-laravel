<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        v-if="pagination.total > 0"
        class="flex items-center justify-between py-6 mt-6 border-t border-gray-200 dark:border-gray-800 text-sm"
    >
        <div class="text-gray-500 dark:text-gray-400">
            Halaman
            <span class="font-medium text-gray-900 dark:text-gray-200">
                {{ pagination.current_page }}
            </span>
            dari
            <span class="font-medium text-gray-900 dark:text-gray-200">
                {{ pagination.last_page }}
            </span>
        </div>

        <div class="flex items-center gap-3">
            <Link
                v-if="pagination.prev_page_url"
                :href="pagination.prev_page_url"
                class="px-3 py-1.5 rounded-lg bg-primary-600 text-white dark:text-gray-300 hover:bg-primary-700 dark:hover:bg-gray-750 transition duration-150 font-medium"
            >
                &larr;
            </Link>
            <span
                v-else
                class="px-3 py-1.5 rounded-lg bg-primary-600-900/50 text-gray-300 dark:text-gray-600 cursor-not-allowed font-medium"
            >
                &larr;
            </span>

            <Link
                v-if="pagination.next_page_url"
                :href="pagination.next_page_url"
                class="px-3 py-1.5 rounded-lg bg-primary-600 text-white dark:text-gray-300 hover:bg-primary-700 dark:hover:bg-gray-750 transition duration-150 font-medium"
            >
                &rarr;
            </Link>
            <span
                v-else
                class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 text-gray-300 dark:text-gray-600 cursor-not-allowed font-medium"
            >
                &rarr;
            </span>
        </div>
    </div>
</template>
