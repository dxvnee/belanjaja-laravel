<script setup>
import { router } from "@inertiajs/vue3";
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    cart_items: {
        type: Array,
        default: () => [],
    },
});

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};
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

        <div
            v-if="cart_items.length > 0"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
        >
            <div v-for="item in cart_items" :key="item.id">
                <ProductCard
                    :onClick="() => goToProductDetail(item.product.id)"
                    :product="item.product"
                    :deleteIcon="true"
                    :quantity="item.quantity"
                />
            </div>
        </div>

        <div v-else class="flex w-full justify-center">
            <p class="text-lg font-medium text-black">
                Belum ada produk di keranjangmu.
            </p>
        </div>
    </AppLayout>
</template>
