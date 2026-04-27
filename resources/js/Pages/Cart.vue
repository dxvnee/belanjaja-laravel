<script setup>
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <AppLayout title="Admin">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Keranjang Saya
            </h2>
            <p class="text-md text-black mb-10">
                Lihat dan kelola produk yang ada di keranjangmu!
            </p>
        </slot>

        <div v-if="products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div v-for="product in products" :key="product.id">
                <ProductCard
                    :onClick="() => goToProductDetail(product.id)"
                    :product="product"
                    :deleteIcon="true"
                />
            </div>
        </div>

        <div v-else  class="flex w-full justify-center">
            <p class="text-lg font-medium text-black">
                Belum ada produk di keranjangmu.
            </p>
        </div>
    </AppLayout>
</template>
