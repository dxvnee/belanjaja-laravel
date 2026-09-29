<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: 0,
    },
});
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-700/40 p-4 rounded-lg text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ label }}
        </p>
        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
            <slot>{{ value }}</slot>
        </p>
    </div>
</template>
