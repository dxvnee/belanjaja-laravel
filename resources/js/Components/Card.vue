<script setup>
defineProps({
    title: String,
});
</script>

<template>
    <div
        class="border border-gray-300 bg-white dark:bg-gray-800 overflow-hidden rounded-lg"
    >
        <slot />
    </div>
</template>
