<script setup>
import { router } from "@inertiajs/vue3";
import IconButton from "./IconButton.vue";
import { useConfirm } from "../Composable/UseConfirm";

const { confirm } = useConfirm();

defineProps({
    product: {
        type: Object,
        required: true,
    },
    onClick: {
        type: Function,
        default: null,
    },
    deleteIcon: {
        type: Boolean,
        default: false,
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

const deleteFromCart = async (productId, productName) => {
    const confirmed = await confirm({
        title: "Hapus dari Keranjang?",
        message: `Hapus "${productName}" dari keranjang belanjamu?`,
    });

    if (!confirmed) return;

    router.post(route("cart.remove", productId), {
        preserveScroll: true,
    });
};


<template>
    <div
        @click="onClick"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:scale-[1.01] transition-transform duration-300 cursor-pointer"
    >
        <div
            @click="onClick"
            class="aspect-square w-full overflow-hidden bg-gray-200"
        >
            <img
                :src="getProductImage(product)"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-300"
            />
        </div>
        <div class="p-3">
            <h3
                class="font-semibold text-sm text-gray-800 dark:text-gray-200 line-clamp-2 mb-1"
            >
                {{ product.name }}
            </h3>
            <p
                class="text-primary-600 dark:text-primary-400 font-bold text-base"
            >
                {{ formatPrice(product.price) }}
            </p>
            <p
                v-if="product.description"
                class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2"
            >
                {{ product.description }}
            </p>

            <div class="mt-4 flex items-center justify-between">
                <div
                    v-if="product.stock <= 0"
                    class="mt-2 text-xs text-red-500 font-medium"
                >
                    Stok Habis
                </div>
                <div
                    v-else-if="product.stock < 5"
                    class="mt-2 text-xs text-orange-500"
                >
                    Stok: {{ product.stock }}
                </div>
                <div>
                    <IconButton
                        v-if="deleteIcon"
                        icons="trash"
                        :fun="() => deleteFromCart(product.id, product.name)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
