<script setup>
import { Icons } from '@/Icons';

defineProps({
    fun: {
        type: Function,
        required: true,
    },
    icons: {
        type: String,
        required: true,
    },
});
</script>
<template>
    <component
        :is="Icons[icons]"
        class="size-6 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 cursor-pointer transition-colors button-small-click"
        @click="fun"
    ></component>
</template>
