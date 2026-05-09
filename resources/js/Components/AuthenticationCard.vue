<script setup lang="ts">
import Card from "./Card.vue";
</script>

<template>
    <div
        class="min-h-screen flex flex-col justify-center items-center p-5 bg-primary-50"
    >
        <div class="flex flex-row items-center">
            <slot name="logo" />
            <div class="flex flex-col gap-1">
                <h1 class="text-5xl font-black text-primary-600">Belanjaja!</h1>
                <h1 class="text-md text-black">Beli semua dengan mudah :)</h1>
            </div>
        </div>

        <card class="p-10">
            <slot />
        </card>
    </div>
</template>
