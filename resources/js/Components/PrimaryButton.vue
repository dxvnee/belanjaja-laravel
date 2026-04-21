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
        class="h-10 *:inline-flex justify-center items-center px-4 py-2 border rounded-md font-semibold text-md focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150 transform active:scale-[0.99] hover:scale-[1.01]"
        :class="[
            variant === 'secondary'
                ? 'bg-white border-primary-600 text-primary-600 hover:bg-primary-50 focus:bg-primary-50 active:bg-primary-100 focus:ring-primary-300'
                : 'bg-primary-600 dark:bg-primary-900 border-transparent text-white dark:text-gray-800 hover:bg-primary-700 dark:hover:bg-white focus:bg-primary-700 dark:focus:bg-white active:bg-primary-900 dark:active:bg-gray-300 focus:ring-primary-300',
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
