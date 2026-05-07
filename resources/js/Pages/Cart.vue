<script setup>
import { router } from "@inertiajs/vue3";
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed } from "vue";
import { useFeedback } from "@/Composable/useFeedback";

const { confirm } = useFeedback();

defineProps({
    cart_items: {
        type: Array,
        default: () => [],
    },
});

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};

const beli = async () => {
    const confirmed = await confirm(
        "Checkout",
        "Apakah Anda yakin ingin melanjutkan ke checkout?",
    );

    if (!confirmed) return;

    router.visit(route("checkout.index"));
};

const product_cart = ref([]);

const addProductToCart = (product) => {
    const product_add = {
        id: product.id,
        price: product.price,
    };

    const index = product_cart.value.findIndex((p) => p.id === product_add.id);

    if (index === -1) product_cart.value.push(product_add);
    else product_cart.value.splice(index, 1);
};

const total_price = computed(() =>
    product_cart.value.reduce((sum, p) => sum + parseFloat(p.price), 0),
);

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
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

        <div v-if="cart_items.length > 0" class="grid grid-rows-1 gap-5">
            <div v-for="item in cart_items" :key="item.id">
                <ProductCard
                    :onClick="() => goToProductDetail(item.product.id)"
                    :checkedFun="() => addProductToCart(item.product)"
                    :product="item.product"
                    :quantity="item.quantity"
                    :checkoutMode="true"
                />
            </div>

            <div
                class="sticky bottom-0 px-4 py-2 bg-white rounded shadow-md flex flex-col items-end justify-center"
            >
                <div class="flex flex-row">
                    <p class="text-xl text-black pe-2">
                        Total:
                    </p>
                    <p class="text-xl font-bold text-primary-600">
                        {{ formatPrice(total_price) }}
                    </p>
                </div>

                <PrimaryButton class="flex items-center min-w-20 mt-3" @click="beli">
                    Checkout
                </PrimaryButton>
            </div>
        </div>

        <div v-else class="flex w-full justify-center">
            <p class="text-lg font-medium text-black">
                Belum ada produk di keranjangmu.
            </p>
        </div>
    </AppLayout>
</template>
