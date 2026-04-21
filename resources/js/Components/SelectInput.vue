<script setup>
import { Icons } from '@/Icons';

defineProps({
    modelValue: String,
    icon: {
        type: String,
        default: null,
    },
    option: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Pilih...',
    },
});

defineEmits(["update:modelValue"]);

</script>

<template>
    <component
        v-if="icon && Icons[icon]"
        :is="Icons[icon]"
        :class="['h-5 w-5 text-gray-400 absolute left-3 z-10']"
        @click="iconClick ? iconClick() : null"
    />
    <select
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-300 dark:focus:border-primary-300 focus:ring-primary-300 dark:focus:ring-primary-300 rounded-md shadow-sm"
    >
        <option value="" disabled selected>{{ placeholder }}</option>
        <option v-for="opt in option" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        <slot />
    </select>
</template>
