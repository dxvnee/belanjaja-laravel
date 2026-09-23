<script setup>
import LoadingDots from './LoadingDots.vue';

defineProps({
    type: {
        type: String,
        default: "submit",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "primary",
    },
});
</script>

<template>
    <button
        :type="type"
        class="h-10 *:inline-flex justify-center items-center px-4 py-2 border rounded-md font-semibold text-md focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 button-click"
        :class="[
            variant === 'secondary'
                ? 'bg-white dark:bg-gray-800 border-primary-600 dark:border-primary-500 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-gray-700 active:bg-primary-100 dark:active:bg-gray-600 focus:ring-primary-500'
                : 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white border-transparent focus:ring-primary-500',
            { 'opacity-25': loading }
        ]"
        :disabled="loading"
    >
        <slot v-if="!loading" />

        <span v-else class="flex items-center gap-2">
            <loading-dots />
        </span>
    </button>
</template>
