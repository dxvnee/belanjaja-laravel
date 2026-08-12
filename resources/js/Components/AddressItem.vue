<script setup>
defineProps({
    address: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                {{ address.name }}
            </span>
            <span
                class="text-sm text-gray-500 dark:text-gray-400 font-medium px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800"
            >
                {{ address.phone }}
            </span>
        </div>

        <p
            class="mt-2 text-gray-700 dark:text-gray-300 leading-relaxed text-sm"
        >
            {{ address.detail }}
        </p>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ address.subdistrict }}, {{ address.city }},
            {{ address.province }}
            {{ address.postal_code }}
        </p>
    </div>
</template>
