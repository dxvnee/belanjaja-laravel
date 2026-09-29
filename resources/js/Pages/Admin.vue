<script setup>
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

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
    <AppLayout title="Jualan Saya">
        <slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-10">
                <div>
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Jualan Saya
                    </h2>
                    <p class="text-md text-gray-600 dark:text-gray-400">
                        Lihat dan kelola iklan yang sudah kamu buat!
                    </p>
                </div>

                <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700">
                    <span
                        class="px-3.5 py-1.5 rounded-md text-xs sm:text-sm font-semibold bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs"
                    >
                        Produk Saya
                    </span>
                    <Link
                        :href="route('admin.orders')"
                        class="px-3.5 py-1.5 rounded-md text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                    >
                        Pesanan Masuk
                    </Link>
                </div>
            </div>
        </slot>

        <div
            v-if="products.length > 0"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
        >
            <div v-for="product in products" :key="product.id" class="h-full">
                <ProductCard
                    :onClick="() => goToProductDetail(product.id)"
                    :product="product"
                />
            </div>
        </div>

        <div v-else class="flex w-full justify-center py-10">
            <p class="text-lg font-medium text-gray-600 dark:text-gray-400">
                Belum ada iklan yang dibuat.
            </p>
        </div>
    </AppLayout>
</template>
