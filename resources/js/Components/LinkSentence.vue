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
        <p class="text-sm">{{ text1 }}</p>
        <Link
            :href="link"
            class="text-sm font-bold text-primary-600 hover:text-primary-800 rounded-md"
        >
            {{ text2 }}
        </Link>
    </div>
</template>
