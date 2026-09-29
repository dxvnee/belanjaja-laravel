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
        <div
            @click="$emit('click-product')"
            :class="[
                checkoutMode ? 'size-40 shrink-0' : 'w-full aspect-square shrink-0',
                'relative overflow-hidden bg-gray-100 dark:bg-gray-700',
            ]"
        >
            <div
                v-if="product.recommendation_badge && !checkoutMode"
                class="absolute top-2 left-2 z-10 pointer-events-none"
            >
                <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs bg-white/95 dark:bg-gray-900/95 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-800/80 backdrop-blur-xs flex items-center gap-1"
                >
                    {{ product.recommendation_badge }}
                </span>
            </div>

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
                <div class="min-h-[1.25rem] mb-1 flex items-center gap-1 overflow-hidden flex-wrap">
                    <template v-if="product.categories && product.categories.length > 0">
                        <span
                            v-for="cat in product.categories.slice(0, 3)"
                            :key="cat.id"
                            class="inline-block text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-1.5 py-0.5 rounded truncate max-w-[80px]"
                            :title="cat.name"
                        >
                            {{ cat.name }}
                        </span>
                    </template>
                    <span
                        v-else-if="product.category?.name"
                        class="inline-block text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-1.5 py-0.5 rounded"
                    >
                        {{ product.category.name }}
                    </span>
                </div>

                <h3
                    class="font-semibold text-sm text-gray-800 dark:text-gray-200 line-clamp-2 h-10 leading-snug mb-1"
                    :title="product.name"
                >
                    {{ product.name }}
                </h3>

                <p
                    class="text-primary-600 dark:text-primary-400 font-bold text-base mb-1"
                >
                    {{ formatPrice(product.price) }}
                </p>

                <div class="min-h-[1.25rem] flex items-center mb-1">
                    <StarRating
                        v-if="product.reviews_count > 0"
                        :rating="product.reviews_avg_rating"
                        :count="product.reviews_count"
                        show-score
                        size="xs"
                    />
                </div>

                <div class="min-h-[1.25rem] flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                    <template v-if="product.location">
                        <svg
                            class="w-3.5 h-3.5 text-gray-400 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span class="truncate">{{ product.location }}</span>
                    </template>
                </div>

                <p
                    v-if="product.description"
                    class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-0.5"
                >
                    {{ product.description }}
                </p>
            </div>

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
