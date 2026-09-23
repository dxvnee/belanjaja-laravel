<script setup>
defineProps({
    title: String,
});
</script>

<template>
    <div
        class="border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow-xs"
    >
        <slot />
    </div>
</template>
