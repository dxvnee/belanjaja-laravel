<script setup>
defineProps({
    message: {
        type: String,
        default: "Belum ada data yang tersedia",
    },
});
</script>

<template>
    <div class="text-center py-12 text-gray-500">
        <p class="text-lg">{{ message }}</p>
    </div>
</template>
