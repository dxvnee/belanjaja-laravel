<script setup>
import { router } from "@inertiajs/vue3";
import IconButton from "./IconButton.vue";
import { useFeedback } from "../Composable/useFeedback";
import { ref, computed, watch } from "vue";
import Counter from "./Counter.vue";
import Checkbox from "./Checkbox.vue";
import Card from "./Card.vue";
import { useHelpers } from "../Composable/useHelpers";

const { confirm, showSuccess, showError, showLoading, hideLoading } =
    useFeedback();
const { formatPrice, getProductImage } = useHelpers();

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    checkoutMode: {
        type: Boolean,
        default: false,
    },
    checked: {
        type: Boolean,
        default: false,
    },
    quantity: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits([
    "click-product",
    "update:checked",
    "update:quantity",
    "toggle-select",
    "qty-changed",
]);

const checked_ref = computed({
    get: () => props.checked,
    set: (value) => {
        emit("update:checked", value);
        emit("toggle-select", { product: props.product, checked: value });
    },
});

const quantity_ref = ref(props.quantity);
const price_total = computed(() => props.product.price * quantity_ref.value);

const deleteFromCart = async (productId, productName) => {
    const confirmed = await confirm(
        "Hapus dari keranjang",
        `Hapus "${productName}" dari keranjang?`,
    );

    if (!confirmed) return;

    showLoading("Menghapus produk...");

    router.delete(route("cart.remove", productId), {
        preserveScroll: true,
        onSuccess: () =>
            showSuccess(
                "Berhasil",
                `Produk "${productName}" telah dihapus dari keranjang.`,
            ),
        onError: (errors) =>
            showError(
                "Gagal",
                `Gagal menghapus produk "${productName}" dari keranjang.`,
            ),
        onFinish: () => hideLoading(),
    });
};

const isSaving = ref(false);
let debounceTimer = null;

watch(quantity_ref, (newQty) => {
    isSaving.value = true;
    emit("update:quantity", newQty); 
    emit("qty-changed", {
        productId: props.product.id,
        qty: newQty,
    });

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        updateQty(newQty);
        isSaving.value = false;
    }, 500);
});

function updateQty(qty) {
    router.patch(
        route("cart.updateQty", props.product.id),
        {
            quantity: qty,
        },
        {
            preserveScroll: true,
            onError: () =>
                showError(
                    "Gagal",
                    `Gagal memperbarui jumlah "${props.product.name}" di keranjang. Silakan coba lagi.`,
                ),
        },
    );
}
</script>

<template>
    <Card
        :class="`${checkoutMode ? 'flex flex-row' : ''} bg-white w-full dark:bg-gray-800 rounded-lg overflow-hidden hover:scale-[1.01] transition-transform duration-300 cursor-pointer`"
    >
        <div
            @click="$emit('click-product')"
            :class="`${checkoutMode ? 'size-40' : ''} aspect-square overflow-hidden bg-gray-200`"
        >
            <img
                :src="getProductImage(product)"
                :alt="product.name"
                class="w-full object-cover transition-transform duration-300"
            />
        </div>
        <div class="p-3 flex-[2]">
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
                <div v-else class="mt-2 text-xs text-orange-500">
                    {{
                        quantity > 0
                            ? `Jumlah: ${quantity}`
                            : `Stok: ${product.stock}`
                    }}
                </div>
            </div>
        </div>
        <div
            v-if="checkoutMode"
            class="flex-1 flex flex-row items-center justify-between p-3"
        >
            <Counter
                v-model="quantity_ref"
                :max-value="product.stock"
            ></Counter>

            <p
                class="text-primary-600 dark:text-primary-400 font-bold text-base"
            >
                {{ formatPrice(price_total) }}
            </p>
            <IconButton
                icons="trash"
                :fun="() => deleteFromCart(product.id, product.name)"
            />
            <Checkbox class="button-small-click cursor-pointer" v-model:checked="checked_ref" />
        </div>
    </Card>
</template>
