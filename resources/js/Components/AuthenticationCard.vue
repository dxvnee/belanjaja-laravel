<script setup lang="ts">
import Card from "./Card.vue";
</script>

<template>
    <div
        class="min-h-screen flex flex-col justify-center items-center p-5 bg-primary-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200"
    >
        <div class="flex flex-row items-center pb-5">
            <slot name="logo" />
            <div class="flex flex-col gap-1">
                <h1 class="text-5xl font-black text-primary-600 dark:text-primary-400">Belanjaja!</h1>
                <p class="text-md text-gray-600 dark:text-gray-400">Beli semua dengan mudah :)</p>
            </div>
        </div>

        <Card class="p-10 w-full sm:max-w-md">
            <slot />
        </Card>
    </div>
</template>
