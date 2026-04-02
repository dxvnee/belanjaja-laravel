<script setup>
import { computed } from "vue";

const props = defineProps({
    size: {
        type: String,
        default: 'md' // sm | md | lg
    }
});

const dotClass = computed(() => {
    switch (props.size) {
        case 'sm': return 'w-1.5 h-1.5';
        case 'lg': return 'w-3 h-3';
        default: return 'w-2 h-2';
    }
});
</script>

<template>
    <div class="flex items-center gap-1 text-white">
        <span :class="[dotClass, 'animate-bounce [animation-delay:-0.3s] rounded-full bg-current']"></span>
        <span :class="[dotClass, 'animate-bounce [animation-delay:-0.15s] rounded-full bg-current']"></span>
        <span :class="[dotClass, 'animate-bounce rounded-full bg-current']"></span>
    </div>
</template>
