<script setup>
import { Link, router } from "@inertiajs/vue3";
import Card from "@/Components/Card.vue";
import { useHelpers } from "@/Composable/useHelpers";
import PrimaryButton from "./PrimaryButton.vue";

const { formatPrice, getProductImage } = useHelpers();

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const onImageError = (event) => {
    event.target.src = "/images/placeholder-product.svg";
};
</script>

<template>
    <Card class="flex flex-col justify-between h-full p-4 hover:border-gray-300 dark:hover:border-gray-600 transition shadow-xs">
        <div>
            <!-- Image & Stock Badge -->
            <div class="relative w-full aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700/60 mb-3 group">
                <img
                    :src="getProductImage(product)"
                    :alt="product.name || 'Produk'"
                    @error="onImageError"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                />

                <div class="absolute top-2 right-2 z-10">
                    <span
                        v-if="product.stock > 0"
                        class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-white/95 dark:bg-gray-900/90 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700 shadow-xs backdrop-blur-xs"
                    >
                        Stok: {{ product.stock }}
                    </span>
                    <span
                        v-else
                        class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-white/95 dark:bg-gray-900/90 text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 shadow-xs backdrop-blur-xs"
                    >
                        Stok Habis
                    </span>
                </div>
            </div>

            <!-- Categories Badges (up to 3) -->
            <div class="min-h-[1.35rem] flex flex-wrap gap-1 mb-2">
                <template v-if="product.categories && product.categories.length > 0">
                    <span
                        v-for="cat in product.categories.slice(0, 3)"
                        :key="cat.id"
                        class="inline-block text-[10px] font-medium px-2 py-0.5 rounded-md bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 truncate max-w-[95px]"
                        :title="cat.name"
                    >
                        {{ cat.name }}
                    </span>
                </template>
                <span
                    v-else-if="product.category?.name"
                    class="inline-block text-[10px] font-medium px-2 py-0.5 rounded-md bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800"
                >
                    {{ product.category.name }}
                </span>
            </div>

            <!-- Product Title -->
            <h3
                class="font-semibold text-sm text-gray-900 dark:text-gray-100 line-clamp-2 leading-snug mb-1"
                :title="product.name"
            >
                {{ product.name }}
            </h3>

            <!-- Location with MapPin Icon -->
            <p v-if="product.location" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mb-2">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ product.location }}</span>
            </p>

            <!-- Price -->
            <p class="font-bold text-base text-primary-600 dark:text-primary-400 mb-3">
                {{ formatPrice(product.price) }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700/60">
            <PrimaryButton
                type="button"
                @click="router.visit(route('product.show', { id: product.id }))"
                class="flex-1 text-center py-1.5 px-2.5 text-sm cursor-pointer"
            >
                Lihat
            </PrimaryButton>
            <PrimaryButton
                type="button"
                @click="router.visit(route('product.edit', { id: product.id }))"
                class="flex-1 text-center py-1.5 px-2.5 text-sm cursor-pointer"
            >
                Edit
            </PrimaryButton>
        </div>
    </Card>
</template>
