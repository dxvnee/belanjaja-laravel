<script setup>
import { computed } from "vue";
import BannerList from "@/Components/BannerList.vue";
import EmptyState from "@/Components/EmptyState.vue";
import ProductCard from "@/Components/ProductCard.vue";
import Pagination from "@/Components/Pagination.vue";
import CategoryFilter from "@/Components/CategoryFilter.vue";
import FeedTabs from "@/Components/FeedTabs.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    products: Object,
    categories: {
        type: Array,
        default: () => [],
    },
    selectedCategory: {
        type: String,
        default: null,
    },
    totalProductsCount: {
        type: Number,
        default: 0,
    },
    activeFeed: {
        type: String,
        default: "for-you",
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

const currentCategory = computed(() => {
    if (!props.selectedCategory) return null;
    return props.categories.find((c) => c.slug === props.selectedCategory);
});

const feedTitle = computed(() => {
    if (props.selectedCategory && currentCategory.value) {
        if (props.activeFeed === "popular")
            return `Produk ${currentCategory.value.name} Terpopuler`;
        if (props.activeFeed === "near-you")
            return `Produk ${currentCategory.value.name} di Sekitar ${props.userCity ?? "Kotamu"}`;
        if (props.activeFeed === "latest")
            return `Produk ${currentCategory.value.name} Terbaru`;
        return `Rekomendasi ${currentCategory.value.name} Untuk Kamu`;
    }

    if (props.activeFeed === "popular") return "Produk Terpopuler";
    if (props.activeFeed === "near-you")
        return props.userCity
            ? `Produk di Sekitar ${props.userCity}`
            : "Produk di Dekat Kotamu";
    if (props.activeFeed === "latest") return "Produk Terbaru";
    return "Rekomendasi Pilihan Untuk Kamu";
});

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};
</script>

<template>
    <AppLayout title="Belanjaja" routeName="dashboard.search">
        <div class="py-6 sm:py-8">
            <BannerList />
        </div>

        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Category Section -->
            <div class="space-y-3">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-1"
                >
                    <div>
                        <h2
                            class="font-bold text-xl sm:text-2xl text-gray-900 dark:text-gray-100 tracking-tight"
                        >
                            {{
                                currentCategory
                                    ? `Kategori: ${currentCategory.name}`
                                    : "Kategori Pilihan"
                            }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pilih kategori barang yang ingin kamu beli atau
                            jelajahi koleksi kami.
                        </p>
                    </div>

                    <div v-if="selectedCategory" class="pt-1 sm:pt-0">
                        <button
                            type="button"
                            @click="
                                router.get(
                                    route('dashboard'),
                                    { feed: activeFeed },
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )
                            "
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline cursor-pointer"
                        >
                            <span>Tampilkan Semua Kategori</span>
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Category Pills Filter -->
                <CategoryFilter
                    :categories="categories"
                    :selected="selectedCategory"
                    :totalCount="totalProductsCount"
                />
            </div>

            <!-- Feed Tabs (Dynamic Personalized Recommendation & Exploration) -->
            <FeedTabs
                :activeFeed="activeFeed"
                :selectedCategory="selectedCategory"
                :userCity="userCity"
                :hasHistory="hasHistory"
            />

            <!-- Products List Header -->
            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2">
                    <h3
                        class="font-bold text-base sm:text-lg text-gray-800 dark:text-gray-200"
                    >
                        {{ feedTitle }}
                    </h3>
                    <span
                        class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700"
                    >
                        {{ products.total ?? products.data.length }} produk
                    </span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="products.data.length === 0"
                class="text-center py-12 text-gray-500"
            >
                <EmptyState
                    :message="
                        selectedCategory
                            ? `Belum ada produk dalam kategori '${currentCategory?.name ?? selectedCategory}'`
                            : 'Belum ada produk yang tersedia'
                    "
                />
            </div>

            <div
                v-else
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="h-full"
                >
                    <ProductCard
                        :onClick="() => goToProductDetail(product.id)"
                        :product="product"
                    />
                </div>
            </div>

            <Pagination :pagination="products" />
        </div>
    </AppLayout>
</template>
