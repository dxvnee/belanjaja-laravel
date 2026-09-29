<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import ProductCard from "@/Components/ProductCard.vue";
import Pagination from "@/Components/Pagination.vue";
import EmptyState from "@/Components/EmptyState.vue";
import StarRating from "@/Components/StarRating.vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import StatCard from "@/Components/StatCard.vue";
import { router } from "@inertiajs/vue3";

defineProps({
    seller: {
        type: Object,
        required: true,
    },
    products: {
        type: Object,
        required: true,
    },
});

const formatJoinedDate = (dateStr) => {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
};

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};
</script>

<template>
    <AppLayout :title="`${seller.name} - Profil Penjual`">
        <div class="py-8">
            <div class="mx-auto w-full max-w-7xl space-y-8">

                <Card class="p-6 sm:p-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <img
                                v-if="seller.profile_photo_url"
                                :src="seller.profile_photo_url"
                                :alt="seller.name"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-primary-500 shadow-sm"
                            />
                            <div
                                v-else
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-3xl shadow-sm"
                            >
                                {{ seller.name ? seller.name.charAt(0).toUpperCase() : 'P' }}
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ seller.name }}
                                    </h1>
                                    <StatusSpan status="penjual" />
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Bergabung sejak {{ formatJoinedDate(seller.created_at) }}
                                </p>
                                <div v-if="seller.reviews_count > 0" class="mt-2">
                                    <StarRating
                                        :rating="seller.avg_rating"
                                        :count="seller.reviews_count"
                                        show-score
                                        size="sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t sm:border-t-0 sm:border-l border-gray-200 dark:border-gray-700 pt-4 sm:pt-0 sm:pl-8">
                            <StatCard label="Total Produk" :value="seller.products_count ?? 0" />
                            <StatCard label="Total Ulasan" :value="seller.reviews_count ?? 0" />
                        </div>
                    </div>
                </Card>


                <div>
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                            Produk dari {{ seller.name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Daftar semua produk aktif yang dijual oleh penjual ini.
                        </p>
                    </div>

                    <div v-if="products.data.length === 0" class="text-center py-12">
                        <EmptyState message="Penjual ini belum memiliki produk yang aktif." />
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
                    >
                        <div v-for="product in products.data" :key="product.id">
                            <ProductCard
                                :product="product"
                                @click="goToProductDetail(product.id)"
                            />
                        </div>
                    </div>

                    <Pagination :pagination="products" class="mt-8" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
