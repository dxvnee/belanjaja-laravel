<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const currentImageIndex = ref(0);

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const getCurrentImage = () => {
    if (props.product.images && props.product.images.length > 0) {
        return `/storage/${props.product.images[currentImageIndex.value].image_path}`;
    }
    return "/images/placeholder-product.png";
};

const selectImage = (index) => {
    currentImageIndex.value = index;
};

const addToCart = () => {
    // TODO: Implement add to cart functionality
    console.log("Add to cart:", props.product.id);
};

const buyNow = () => {
    // TODO: Implement buy now functionality
    console.log("Buy now:", props.product.id);
};
</script>

<template>
    <AppLayout :title="product.name">
        <div class="py-12">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div
                            class="aspect-square w-full overflow-hidden rounded-lg bg-gray-200"
                        >
                            <img
                                :src="getCurrentImage()"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Thumbnails -->
                        <div
                            v-if="product.images && product.images.length > 1"
                            class="grid grid-cols-4 gap-2"
                        >
                            <div
                                v-for="(image, index) in product.images"
                                :key="image.id"
                                class="aspect-square cursor-pointer overflow-hidden rounded-lg border-2 transition-all"
                                :class="
                                    currentImageIndex === index
                                        ? 'border-primary-600'
                                        : 'border-gray-200 hover:border-gray-400'
                                "
                                @click="selectImage(index)"
                            >
                                <img
                                    :src="`/storage/${image.image_path}`"
                                    :alt="`${product.name} ${index + 1}`"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <div>
                            <h1
                                class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ product.name }}
                            </h1>
                            <p
                                class="mt-4 text-4xl font-bold text-primary-600 dark:text-primary-400"
                            >
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>

                        <!-- Stock Info -->
                        <div class="flex items-center gap-4">
                            <div
                                v-if="product.stock > 0"
                                class="rounded-lg bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                            >
                                Stok: {{ product.stock }}
                            </div>
                            <div
                                v-else
                                class="rounded-lg bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
                            >
                                Stok Habis
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="border-t border-gray-200 pt-6">
                            <h2
                                class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                            >
                                Deskripsi Produk
                            </h2>
                            <p
                                class="mt-3 whitespace-pre-line text-gray-600 dark:text-gray-400"
                            >
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3 border-t border-gray-200 pt-6">
                            <PrimaryButton
                                class="w-full justify-center text-base"
                                :disabled="product.stock <= 0"
                                @click="buyNow"
                            >
                                Beli Sekarang
                            </PrimaryButton>
                            <PrimaryButton
                                variant="secondary"
                                class="w-full justify-center text-base"
                                :disabled="product.stock <= 0"
                                @click="addToCart"
                            >
                                Tambah ke Keranjang
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
