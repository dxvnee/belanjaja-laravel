<script setup>
defineProps({
    orderId: {
        type: [Number, String],
        required: true,
    },
    download: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: "Cetak Invoice",
    },
});
</script>

<template>
    <a
        :href="route('orders.invoice', { id: orderId, ...(download ? { download: 1 } : {}) })"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs sm:text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/80 shadow-xs transition duration-150 ease-in-out cursor-pointer"
        :title="label"
    >
        <svg
            class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            />
        </svg>
        <span>{{ label }}</span>
    </a>
</template>
