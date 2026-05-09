<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0,
    },
    maxValue: {
        type: Number,
        default: Infinity,
    },
});
const emit = defineEmits(['update:modelValue']);

const count = ref(props.modelValue);

watch(count, (newValue) => {
    emit('update:modelValue', newValue);
});

const increase = () => {
    if (count.value < props.maxValue) {
        count.value++;
    }
};
const decrease = () => {
    if (count.value > 1) {
        count.value--;
    }
};

</script>
<template>
    <div class="flex items-center gap-2">
        <button
            @click="decrease"
            class="bg-primary-100 dark:bg-primary-700 text-gray-800 dark:text-gray-200 rounded px-2 py-1"
        >
            -
        </button>
        <span class="text-sm">{{ count }}</span>
        <button
            @click="increase"
            class="bg-primary-100 dark:bg-primary-700 text-gray-800 dark:text-gray-200 rounded px-2 py-1"
        >
            +
        </button>
    </div>
</template>
