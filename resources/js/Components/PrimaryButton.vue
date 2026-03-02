<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex justify-center items-center px-4 py-2 bg-primary-600 dark:bg-primary-900 border border-transparent rounded-md font-semibold text-md text-white dark:text-gray-800 hover:bg-primary-700 dark:hover:bg-white focus:bg-primary-700 dark:focus:bg-white active:bg-primary-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-300 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150
    transform active:scale-[0.99] hover:scale-[1.01]">
        <slot />
    </button>
</template>
