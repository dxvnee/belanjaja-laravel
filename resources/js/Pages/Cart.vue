<script setup>
import { router } from "@inertiajs/vue3";
import ProductCard from "@/Components/ProductCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed, watch } from "vue";
import { useFeedback } from "@/Composable/useFeedback";
import { useHelpers } from "@/Composable/useHelpers";
import Card from "@/Components/Card.vue";

const { confirm } = useFeedback();
const { formatPrice } = useHelpers();

const props = defineProps({
    cart_items: {
        type: Array,
        default: () => [],
    },
});

const product_cart = ref([]);

const goToProductDetail = (id) => {
    router.visit(route("product.show", { id }));
};

const beli = async () => {
    const confirmed = await confirm(
        "Checkout",
        "Apakah Anda yakin ingin melanjutkan ke checkout?",
    );

    if (!confirmed) return;

    router.visit(route("checkout.index"), {
        method: "get",
        data: {
            product_ids: product_cart.value,
        },
    });
};

const addProductToCart = (product) => {
    const index = product_cart.value.indexOf(product.id);

    if (index === -1) product_cart.value.push(product.id);
    else product_cart.value.splice(index, 1);
};

const total_price = computed(() =>
    props.cart_items
        .filter((item) => product_cart.value.includes(item.product.id))
        .reduce(
            (sum, item) => sum + parseFloat(item.product.price) * item.quantity,
            0,
        ),
);
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

            <Card
                class="sticky bottom-0 py-4 px-4 flex flex-col items-end justify-center"
            >
                <div class="flex flex-row">
                    <p class="text-xl text-black pe-2">Total:</p>
                    <p class="text-xl font-bold text-primary-600">
                        {{ formatPrice(total_price) }}
                    </p>
                </div>

                <PrimaryButton
                    class="flex items-center min-w-20 mt-3"
                    @click="beli"
                >
                    Checkout
                </PrimaryButton>
            </Card>
        </div>

        <div v-else class="flex w-full justify-center">
            <p class="text-lg font-medium text-black">
                Belum ada produk di keranjangmu.
            </p>
        </div>
    </AppLayout>
</template>
