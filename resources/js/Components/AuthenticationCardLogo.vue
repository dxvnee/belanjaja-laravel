<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="text-primary-600 hover:text-primary-700 transition">
        <svg
            class="size-32"
            viewBox="0 0 48  48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Body Tas (Solid) -->
            <path
                d="M12 16h24l-2.5 22H14.5L12 16z"
                fill="currentColor"
            />

            <!-- Handle (Solid) -->
            <path
                d="M18 16v-4a6 6 0 0112 0v4"
                stroke="currentColor"
                stroke-width="4"
                stroke-linecap="round"
            />

            <!-- Smile (Putih) -->
            <path
                d="M19 27c1.5 2 3.5 3 5 3s3.5-1 5-3"
                stroke="white"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </Link>
</template>
