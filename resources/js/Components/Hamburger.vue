<script setup>
const props = defineProps({
    showingNavigationDropdown: Boolean,
});

const emit = defineEmits(["update:showingNavigationDropdown"]);

const toggleNavigationDropdown = () => {
    emit("update:showingNavigationDropdown", !props.showingNavigationDropdown);
};
</script>

<template>
    <div class="-me-2 flex items-center sm:hidden">
        <button
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
            @click="toggleNavigationDropdown"
        >
            <svg
                class="size-6"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 24 24"
            >
                <path
                    :class="{
                        hidden: props.showingNavigationDropdown,
                        'inline-flex': !props.showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                    :class="{
                        hidden: !props.showingNavigationDropdown,
                        'inline-flex': props.showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
</template>
