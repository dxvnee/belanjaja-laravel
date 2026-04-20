<script setup>
defineProps({
    product: {
        type: Object,
        required: true,
    },
    onClick: {
        type: Function,
        default: null,
    },
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const getProductImage = (product) => {
    if (product.images && product.images.length > 0) {
        return `/storage/${product.images[0].image_path}`;
    }
    return "/images/placeholder-product.png";
};
</script>

<template>
    <div @click="onClick" class="aspect-square w-full overflow-hidden bg-gray-200">
        <img
            :src="getProductImage(product)"
            :alt="product.name"
            class="h-full w-full object-cover hover:scale-110 transition-transform duration-300"
        />
    </div>
    <div class="p-3">
        <h3
            class="font-semibold text-sm text-gray-800 dark:text-gray-200 line-clamp-2 mb-1"
        >
            {{ product.name }}
        </h3>
        <p class="text-primary-600 dark:text-primary-400 font-bold text-base">
            {{ formatPrice(product.price) }}
        </p>
        <p
            v-if="product.description"
            class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2"
        >
            {{ product.description }}
        </p>
        <div
            v-if="product.stock <= 0"
            class="mt-2 text-xs text-red-500 font-medium"
        >
            Stok Habis
        </div>
        <div v-else-if="product.stock < 5" class="mt-2 text-xs text-orange-500">
            Stok: {{ product.stock }}
        </div>
    </div>
</template>
