<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import ProductCard from "@/Components/ProductCard.vue";
import Pagination from "@/Components/Pagination.vue";
import EmptyState from "@/Components/EmptyState.vue";
import { router } from "@inertiajs/vue3";

defineProps({
    products: Object,
    query: {
        type: String,
        default: "",
    },
});

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};
</script>

<template>
    <AppLayout title="Pencarian Produk" routeName="dashboard.search">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Hasil Pencarian
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-6">
                <template v-if="query">
                    Menampilkan hasil pencarian untuk "{{ query }}"
                </template>
                <template v-else>
                    Menampilkan semua produk yang tersedia
                </template>
            </p>
        </slot>

        <div
            v-if="products.data.length === 0"
            class="text-center py-12"
        >
            <EmptyState
                :message="
                    query
                        ? `Tidak ada produk yang cocok dengan '${query}'`
                        : 'Belum ada produk yang tersedia'
                "
            />
        </div>

        <div
            v-else
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
        >
            <div v-for="product in products.data" :key="product.id" class="h-full">
                <ProductCard
                    :onClick="() => goToProductDetail(product.id)"
                    :product="product"
                />
            </div>
        </div>

        <Pagination :pagination="products" />
    </AppLayout>
</template>
