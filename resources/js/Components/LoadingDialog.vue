<script setup>
import LoadingDots from "./LoadingDots.vue";
import { useFeedback } from "@/Composable/useFeedback";

const { state } = useFeedback();
</script>

<template>
    <div
        v-if="state.loading.show"
        class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex flex-col items-center gap-3 min-w-32 border border-gray-200 dark:border-gray-700 shadow-xl">
            <loading-dots size="lg" color="text-primary-600 dark:text-primary-400" />
            <p v-if="state.loading.message" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ state.loading.message }}
            </p>
        </div>
    </div>
</template>
