<script setup>
import { router } from "@inertiajs/vue3";
import IconButton from "./IconButton.vue";
import { useFeedback } from "../Composable/useFeedback";
import { ref, computed, watch } from "vue";
import Counter from "./Counter.vue";
import Checkbox from "./Checkbox.vue";
import Card from "./Card.vue";
import StarRating from "./StarRating.vue";
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
        :class="[
            checkoutMode ? 'flex flex-row items-center' : 'flex flex-col h-full',
            'bg-white w-full dark:bg-gray-800 rounded-lg overflow-hidden hover:scale-[1.01] transition-transform duration-300 cursor-pointer',
        ]"
    >
        <!-- Product Image -->
        <div
            @click="$emit('click-product')"
            :class="[
                checkoutMode ? 'size-40 shrink-0' : 'w-full aspect-square shrink-0',
                'overflow-hidden bg-gray-100 dark:bg-gray-700',
            ]"
        >
            <img
                :src="getProductImage(product)"
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
            />
        </div>

        <!-- Product Info Body -->
        <div
            :class="[
                checkoutMode
                    ? 'p-3 flex-[2] min-w-0'
                    : 'p-3.5 flex-1 flex flex-col justify-between min-w-0',
            ]"
        >
            <!-- Top Content -->
            <div class="flex-1 flex flex-col">
                <!-- Category Badge -->
                <div class="min-h-[1.25rem] mb-1 flex items-center">
                    <span
                        v-if="product.category?.name"
                        class="inline-block text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-1.5 py-0.5 rounded"
                    >
                        {{ product.category.name }}
                    </span>
                </div>

                <!-- Product Name (uniform 2 lines height) -->
                <h3
                    class="font-semibold text-sm text-gray-800 dark:text-gray-200 line-clamp-2 h-10 leading-snug mb-1"
                    :title="product.name"
                >
                    {{ product.name }}
                </h3>

                <!-- Price -->
                <p
                    class="text-primary-600 dark:text-primary-400 font-bold text-base mb-1"
                >
                    {{ formatPrice(product.price) }}
                </p>

                <!-- Rating (uniform height) -->
                <div class="min-h-[1.25rem] flex items-center mb-1">
                    <StarRating
                        v-if="product.reviews_count > 0"
                        :rating="product.reviews_avg_rating"
                        :count="product.reviews_count"
                        show-score
                        size="xs"
                    />
                </div>

                <!-- Description -->
                <p
                    v-if="product.description"
                    class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-0.5"
                >
                    {{ product.description }}
                </p>
            </div>

            <!-- Bottom Content: Pinned Stock / Quantity -->
            <div
                class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between"
            >
                <div
                    v-if="product.stock <= 0"
                    class="text-xs text-red-500 font-medium flex items-center gap-1.5"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    <span>Stok Habis</span>
                </div>
                <div
                    v-else
                    class="text-xs text-orange-500 font-medium flex items-center gap-1.5"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    <span>
                        {{
                            quantity > 0
                                ? `Jumlah: ${quantity}`
                                : `Stok: ${product.stock}`
                        }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Checkout Mode Actions -->
        <div
            v-if="checkoutMode"
            class="flex-1 flex flex-row items-center justify-between p-3 shrink-0"
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
            <Checkbox
                class="button-small-click cursor-pointer"
                v-model:checked="checked_ref"
            />
        </div>
    </Card>
</template>
