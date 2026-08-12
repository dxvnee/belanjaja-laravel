<script setup>
import BannerList from "@/Components/BannerList.vue";
import EmptyState from "@/Components/EmptyState.vue";
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};
</script>

<template>
    <AppLayout title="Belanjaja">
        <div class="py-12">
            <BannerList />
        </div>
        <div class="mx-auto w-full max-w-7xl">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-2"
            >
                Produk Baru
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-6">
                Pilih kategori barang yang ingin kamu beli!
            </p>

            <div
                v-if="products.length === 0"
                class="text-center py-12 text-gray-500"
            >
                <EmptyState message="Belum ada produk yang tersedia" />
            </div>

            <div
                v-else
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
            >
                <div v-for="product in products" :key="product.id">
                    <ProductCard
                        :onClick="() => goToProductDetail(product.id)"
                        :product="product"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
