<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    activeFeed: {
        type: String,
        default: "for-you",
    },
    selectedCategory: {
        type: String,
        default: null,
    },
    userCity: {
        type: String,
        default: null,
    },
    hasHistory: {
        type: Boolean,
        default: false,
    },
});

const changeFeed = (feed) => {
    router.get(
        route("dashboard"),
        {
            feed,
            ...(props.selectedCategory ? { category: props.selectedCategory } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const refreshFeed = () => {
    router.get(
        route("dashboard"),
        {
            feed: props.activeFeed,
            ...(props.selectedCategory ? { category: props.selectedCategory } : {}),
            refresh_feed: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700/80 pb-3">
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar scroll-smooth">
            <button
                type="button"
                @click="changeFeed('for-you')"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all whitespace-nowrap cursor-pointer',
                    activeFeed === 'for-you'
                        ? 'bg-primary-600 text-white shadow-xs'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'
                ]"
            >
                <span>✨</span>
                <span>Untuk Kamu</span>
                <span
                    v-if="hasHistory"
                    class="hidden md:inline-block text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-white/20 text-white"
                >
                    Personal
                </span>
            </button>

            <button
                type="button"
                @click="changeFeed('popular')"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all whitespace-nowrap cursor-pointer',
                    activeFeed === 'popular'
                        ? 'bg-primary-600 text-white shadow-xs'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'
                ]"
            >
                <span>🔥</span>
                <span>Terpopuler</span>
            </button>

            <button
                type="button"
                @click="changeFeed('near-you')"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all whitespace-nowrap cursor-pointer',
                    activeFeed === 'near-you'
                        ? 'bg-primary-600 text-white shadow-xs'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'
                ]"
            >
                <span>📍</span>
                <span>{{ userCity ? `Di ${userCity}` : 'Dekat Kotamu' }}</span>
            </button>

            <button
                type="button"
                @click="changeFeed('latest')"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all whitespace-nowrap cursor-pointer',
                    activeFeed === 'latest'
                        ? 'bg-primary-600 text-white shadow-xs'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'
                ]"
            >
                <span>🆕</span>
                <span>Terbaru</span>
            </button>
        </div>

        <div class="flex items-center justify-end shrink-0">
            <button
                type="button"
                @click="refreshFeed"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer border border-gray-200 dark:border-gray-700"
                title="Acak produk rekomendasi baru"
            >
                <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400 transition-transform active:rotate-180 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Acak Rekomendasi</span>
            </button>
        </div>
    </div>
</template>
