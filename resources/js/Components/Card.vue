<script setup>
defineProps({
    title: String,
});
</script>

<template>
    <div
        class="w-full max-w-md mt-2 px-6 py-4 border border-gray-300 bg-white dark:bg-gray-800 overflow-hidden rounded-lg"
    >
        <slot />
    </div>
</template>
