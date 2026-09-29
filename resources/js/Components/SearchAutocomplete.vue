<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useHelpers } from "@/Composable/useHelpers";
import { Icons } from "@/Icons";
import axios from "axios";

const props = defineProps({
    routeName: {
        type: String,
        default: "dashboard.search",
    },
    placeholder: {
        type: String,
        default: "Cari barang yang kamu butuhkan...",
    },
    id: {
        type: String,
        default: "search-autocomplete",
    },
});

const page = usePage();
const { formatPrice, getProductImage } = useHelpers();

const searchQuery = ref(page.props.query || "");
const previewResults = ref([]);
const isOpen = ref(false);
const isLoading = ref(false);
const selectedIndex = ref(-1);
const containerRef = ref(null);
let debounceTimer = null;

const fetchPreview = () => {
    clearTimeout(debounceTimer);
    const query = searchQuery.value.trim();

    if (query.length < 1) {
        previewResults.value = [];
        isOpen.value = false;
        isLoading.value = false;
        return;
    }

    isLoading.value = true;
    isOpen.value = true;
    selectedIndex.value = -1;

    debounceTimer = setTimeout(async () => {
        try {
            const res = await axios.get(route("dashboard.search.preview"), {
                params: { query },
            });
            // Only update if query hasn't changed in the meantime
            if (searchQuery.value.trim() === query) {
                previewResults.value = res.data.products || [];
            }
        } catch (error) {
            console.error("Gagal memuat pratinjau pencarian:", error);
            previewResults.value = [];
        } finally {
            isLoading.value = false;
        }
    }, 250);
};

const onInput = () => {
    fetchPreview();
};

const onFocus = () => {
    if (searchQuery.value.trim().length >= 1) {
        isOpen.value = true;
        if (previewResults.value.length === 0 && !isLoading.value) {
            fetchPreview();
        }
    }
};

const goToProduct = (product) => {
    isOpen.value = false;
    router.visit(route("product.show", product.id));
};

const submitSearch = () => {
    const query = searchQuery.value.trim();
    if (!query) return;

    isOpen.value = false;
    router.get(
        route(props.routeName || "dashboard.search"),
        { query },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const onKeyDown = (e) => {
    if (!isOpen.value) {
        if (e.key === "ArrowDown" && searchQuery.value.trim().length >= 1) {
            isOpen.value = true;
        }
        return;
    }

    if (e.key === "ArrowDown") {
        e.preventDefault();
        if (previewResults.value.length > 0) {
            selectedIndex.value =
                (selectedIndex.value + 1) % previewResults.value.length;
        }
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (previewResults.value.length > 0) {
            selectedIndex.value =
                (selectedIndex.value - 1 + previewResults.value.length) %
                previewResults.value.length;
        }
    } else if (e.key === "Enter") {
        e.preventDefault();
        if (
            selectedIndex.value >= 0 &&
            previewResults.value[selectedIndex.value]
        ) {
            goToProduct(previewResults.value[selectedIndex.value]);
        } else {
            submitSearch();
        }
    } else if (e.key === "Escape") {
        isOpen.value = false;
    }
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
    clearTimeout(debounceTimer);
});

watch(
    () => page.props.query,
    (newQuery) => {
        if (newQuery !== undefined) {
            searchQuery.value = newQuery;
        }
    },
);
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <form @submit.prevent="submitSearch" class="relative w-full">
            <div class="relative flex items-center">
                <component
                    :is="Icons.search"
                    class="h-5 w-5 text-gray-400 absolute left-3 z-10 cursor-pointer"
                    @click="submitSearch"
                />
                <input
                    :id="id"
                    v-model="searchQuery"
                    type="text"
                    autocomplete="off"
                    :placeholder="placeholder"
                    @input="onInput"
                    @focus="onFocus"
                    @keydown="onKeyDown"
                    class="w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm transition-colors"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    @click="
                        searchQuery = '';
                        isOpen = false;
                        previewResults = [];
                    "
                    class="absolute right-3 p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Hapus pencarian"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </form>

        <div
            v-if="isOpen && searchQuery.trim().length >= 1"
            class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60 transition-all"
        >
            <div
                class="px-3.5 py-2 bg-gray-50 dark:bg-gray-800/80 flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400"
            >
                <span>Pratinjau Produk</span>
                <span
                    v-if="isLoading"
                    class="flex items-center gap-1.5 text-primary-600 dark:text-primary-400"
                >
                    <svg
                        class="animate-spin h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <span>Mencari...</span>
                </span>
                <span v-else-if="previewResults.length > 0">
                    {{ previewResults.length }} produk teratas
                </span>
            </div>

            <div
                v-if="isLoading && previewResults.length === 0"
                class="p-3 space-y-2.5"
            >
                <div
                    v-for="i in 3"
                    :key="i"
                    class="flex items-center gap-3 animate-pulse"
                >
                    <div
                        class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0"
                    ></div>
                    <div class="flex-1 space-y-2">
                        <div
                            class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"
                        ></div>
                        <div
                            class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"
                        ></div>
                    </div>
                </div>
            </div>

            <div
                v-else-if="previewResults.length > 0"
                class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50"
            >
                <div
                    v-for="(product, idx) in previewResults"
                    :key="product.id"
                    @click="goToProduct(product)"
                    @mouseenter="selectedIndex = idx"
                    :class="[
                        'flex items-center gap-3 p-3 transition-colors cursor-pointer group',
                        selectedIndex === idx
                            ? 'bg-primary-50/70 dark:bg-primary-950/40 text-primary-900 dark:text-primary-100'
                            : 'hover:bg-gray-50 dark:hover:bg-gray-750',
                    ]"
                >
                    <img
                        :src="getProductImage(product)"
                        :alt="product.name"
                        class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shrink-0 bg-gray-50 dark:bg-gray-900 group-hover:opacity-90 transition-opacity"
                    />

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span
                                v-if="product.category?.name"
                                class="text-[10px] font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded"
                            >
                                {{ product.category.name }}
                            </span>
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400"
                            >
                                {{ product.name }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 mt-1 text-xs">
                            <span
                                class="font-bold text-primary-600 dark:text-primary-400"
                            >
                                {{ formatPrice(product.price) }}
                            </span>
                            <span>•</span>

                            <span
                                v-if="product.stock > 0"
                                class="inline-flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"
                                ></span>
                                Stok: {{ product.stock }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 font-medium text-red-500"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-red-500"
                                ></span>
                                Stok Habis
                            </span>

                            <template v-if="product.location">
                                <span>•</span>
                                <span
                                    class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400 truncate max-w-[120px]"
                                >
                                    <svg
                                        class="w-3 h-3 text-gray-400 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    <span class="truncate">{{
                                        product.location
                                    }}</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <svg
                        class="w-4 h-4 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </div>
            </div>

            <div
                v-else-if="!isLoading"
                class="p-6 text-center text-sm text-gray-500 dark:text-gray-400"
            >
                <p>
                    Tidak ada produk yang cocok dengan "<span
                        class="font-medium text-gray-800 dark:text-gray-200"
                        >{{ searchQuery }}</span
                    >"
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Coba gunakan kata kunci yang lebih umum.
                </p>
            </div>

            <div
                class="p-2.5 bg-gray-50 dark:bg-gray-800/90 text-center border-t border-gray-100 dark:border-gray-700"
            >
                <button
                    type="button"
                    @click="submitSearch"
                    class="w-full text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 py-1 transition-colors flex items-center justify-center gap-1.5"
                >
                    <span>Lihat semua hasil untuk "{{ searchQuery }}"</span>
                    <svg
                        class="w-3.5 h-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
