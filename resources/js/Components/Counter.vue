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
            class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded px-2.5 py-1 button-click transition-colors"
        >
            -
        </button>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-4 text-center">{{ count }}</span>
        <button
            @click="increase"
            class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded px-2.5 py-1 button-click transition-colors"
        >
            +
        </button>
    </div>
</template>
