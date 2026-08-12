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
        class="size-6 text-gray-600 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer transition-all button-small-click"
        @click="fun"
    ></component>
</template>
