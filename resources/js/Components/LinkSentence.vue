<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    link: String,
    text1: String,
    text2: String,
})

</script>

<template>
    <div class="flex flex-row gap-1">
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ text1 }}</p>
        <Link
            :href="link"
            class="text-sm font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 rounded-md transition-colors"
        >
            {{ text2 }}
        </Link>
    </div>
</template>
