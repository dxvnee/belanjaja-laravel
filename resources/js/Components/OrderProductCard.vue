<script setup>
import { Link } from "@inertiajs/vue3";
import { useHelpers } from "@/Composable/useHelpers";

const { formatPrice, getProductImage } = useHelpers();

defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-start gap-3.5 min-w-0 flex-1">
            <Link
                v-if="item.product?.id"
                :href="route('product.show', item.product.id)"
                class="shrink-0 group"
            >
                <img
                    :src="getProductImage(item.product)"
                    :alt="item.product?.name ?? 'Produk'"
                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700 group-hover:opacity-90 transition-opacity bg-gray-50 dark:bg-gray-800"
                />
            </Link>
            <div
                v-else
                class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400"
            >
                <img
                    :src="getProductImage(null)"
                    alt="Produk"
                    class="w-full h-full object-cover rounded-lg"
                />
            </div>

            <div class="min-w-0 flex-1">
                <Link
                    v-if="item.product?.id"
                    :href="route('product.show', item.product.id)"
                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 line-clamp-2 transition-colors"
                >
                    {{ item.product?.name ?? "Produk tidak tersedia" }}
                </Link>
                <p v-else class="text-sm font-semibold text-gray-900 dark:text-gray-100 line-clamp-2">
                    {{ item.product?.name ?? "Produk tidak tersedia" }}
                </p>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span>{{ formatPrice(item.price_snapshot) }}</span>
                    <span>×</span>
                    <span>{{ item.quantity }} barang</span>
                </div>

                <div v-if="$slots.action" class="mt-2">
                    <slot name="action" />
                </div>
            </div>
        </div>

        <div class="sm:text-right shrink-0 flex sm:flex-col justify-between sm:justify-center items-center sm:items-end border-t sm:border-t-0 pt-2 sm:pt-0 border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400 sm:hidden">Total Harga:</span>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">Subtotal</p>
                <p class="font-bold text-sm sm:text-base text-gray-900 dark:text-gray-100">
                    {{ formatPrice(item.subtotal) }}
                </p>
            </div>
        </div>
    </div>
</template>
