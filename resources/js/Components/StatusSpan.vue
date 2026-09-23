<script setup>
const props = defineProps({
    statusLabel: {
        type: String,
        required: true,
    },
    statusColor: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span
        class="text-xs px-2.5 py-1 rounded-full whitespace-nowrap"
        :class="statusColor"
    >
        {{ statusLabel }}
    </span>
</template>
