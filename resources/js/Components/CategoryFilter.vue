<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    selected: {
        type: String,
        default: null,
    },
    totalCount: {
        type: Number,
        default: 0,
    },
});

const selectCategory = (slug) => {
    if (props.selected === slug || slug === null) {
        router.get(
            route("dashboard"),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    } else {
        router.get(
            route("dashboard"),
            { category: slug },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

const getCategoryIcon = (slug) => {
    switch (slug) {
        case "elektronik":
            return "📱";
        case "fashion":
            return "👕";
        case "makanan-minuman":
            return "🍔";
        case "kesehatan":
            return "💊";
        case "peralatan-rumah":
            return "🏠";
        default:
            return "📦";
    }
};
</script>

<template>
    <div class="w-full">
        <!-- Category Pills Container -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 pt-1 no-scrollbar scroll-smooth">
            <!-- All Categories Pill -->
            <button
                type="button"
                @click="selectCategory(null)"
                :class="[
                    'group inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all duration-200 cursor-pointer shrink-0 border',
                    !selected
                        ? 'bg-primary-600 text-white border-primary-600 shadow-sm shadow-primary-600/30'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-750'
                ]"
            >
                <span class="text-base leading-none">✨</span>
                <span>Semua Kategori</span>
                <span
                    v-if="totalCount > 0"
                    :class="[
                        'text-[11px] px-1.5 py-0.5 rounded-full font-semibold transition-colors',
                        !selected
                            ? 'bg-white/20 text-white'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-950/40 group-hover:text-primary-600 dark:group-hover:text-primary-400'
                    ]"
                >
                    {{ totalCount }}
                </span>
            </button>

            <!-- Individual Categories -->
            <button
                v-for="category in categories"
                :key="category.id"
                type="button"
                @click="selectCategory(category.slug)"
                :class="[
                    'group inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium whitespace-nowrap transition-all duration-200 cursor-pointer shrink-0 border',
                    selected === category.slug
                        ? 'bg-primary-600 text-white border-primary-600 shadow-sm shadow-primary-600/30'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-750'
                ]"
            >
                <span class="text-base leading-none">{{ getCategoryIcon(category.slug) }}</span>
                <span>{{ category.name }}</span>
                <span
                    v-if="category.products_count !== undefined"
                    :class="[
                        'text-[11px] px-1.5 py-0.5 rounded-full font-semibold transition-colors',
                        selected === category.slug
                            ? 'bg-white/20 text-white'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-950/40 group-hover:text-primary-600 dark:group-hover:text-primary-400'
                    ]"
                >
                    {{ category.products_count }}
                </span>
            </button>
        </div>
    </div>
</template>
