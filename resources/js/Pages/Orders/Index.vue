<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { useFeedback } from "@/Composable/useFeedback";
import { usePage, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import ReviewModal from "@/Components/ReviewModal.vue";
import StarRating from "@/Components/StarRating.vue";

const { formatPrice } = useHelpers();
const { confirm } = useFeedback();

const props = defineProps({
    orders: Object,
});

const flash = computed(() => usePage().props.flash ?? {});

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const cancelOrder = async (id) => {
    const confirmed = await confirm(
        "Batalkan Pesanan",
        "Apakah Anda yakin ingin membatalkan pesanan ini?",
    );
    if (confirmed) {
        router.post(route("orders.cancel", id));
    }
};

const completeOrder = async (id) => {
    const confirmed = await confirm(
        "Konfirmasi Penerimaan",
        "Apakah Anda yakin telah menerima pesanan ini dengan baik?",
    );
    if (confirmed) {
        router.post(route("orders.complete", id));
    }
};

const showReviewModal = ref(false);
const reviewingProduct = ref(null);
const reviewingOrder = ref(null);

const getReview = (order, productId) => {
    return order.reviews?.find((r) => r.product_id === productId);
};

const openReviewModal = (order, item) => {
    reviewingOrder.value = order;
    reviewingProduct.value = item.product;
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    reviewingProduct.value = null;
    reviewingOrder.value = null;
};
</script>

<template>
    <AppLayout title="Pesanan Saya">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Pesanan Saya
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-10">
                Daftar pesanan yang telah Anda buat.
            </p>
        </slot>

        <div class="flex flex-col gap-4">
            <!-- Flash success message -->
            <div
                v-if="flash.success"
                class="bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-lg px-4 py-3"
            >
                {{ flash.success }}
            </div>

            <div
                v-if="flash.error"
                class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-lg px-4 py-3"
            >
                {{ flash.error }}
            </div>

            <div v-if="orders.data.length === 0">
                <Card class="max-w-none p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        Belum ada pesanan.
                    </p>
                </Card>
            </div>

            <Card
                v-for="order in orders.data"
                :key="order.id"
                class="max-w-none p-5"
            >
                <div class="w-full">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                Pesanan #{{ order.id }}
                            </p>
                            <p
                                class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                            >
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <StatusSpan :status="order.status" />
                    </div>

                    <div
                        v-if="order.tracking_number"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-3"
                    >
                        Nomor Resi:
                        <span
                            class="font-mono text-gray-800 dark:text-gray-200"
                            >{{ order.tracking_number }}</span
                        >
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="pb-2 font-medium">Produk</th>
                                <th class="pb-2 font-medium">Harga Satuan</th>
                                <th class="pb-2 font-medium">Jumlah</th>
                                <th class="pb-2 font-medium text-right">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in order.items"
                                :key="item.id"
                                class="border-t border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200"
                            >
                                <td class="py-2.5">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ item.product?.name ?? "-" }}
                                    </div>
                                    <div v-if="order.status === 'completed'" class="mt-1">
                                        <div
                                            v-if="getReview(order, item.product_id)"
                                            class="inline-flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800"
                                        >
                                            <StarRating :rating="getReview(order, item.product_id).rating" size="xs" />
                                            <span class="text-gray-400 dark:text-gray-500">•</span>
                                            <span class="text-gray-600 dark:text-gray-300 truncate max-w-xs">
                                                {{ getReview(order, item.product_id).comment || "Sudah diulas" }}
                                            </span>
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            @click="openReviewModal(order, item)"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-amber-400" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Beri Ulasan
                                        </button>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    {{ formatPrice(item.price_snapshot) }}
                                </td>
                                <td class="py-2.5">{{ item.quantity }}</td>
                                <td
                                    class="py-2.5 text-right font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatPrice(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 gap-4"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                v-if="order.status === 'pending'"
                                :href="
                                    route('orders.payment', { id: order.id })
                                "
                                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-xs text-white bg-primary-600 hover:bg-primary-700 focus:outline-none transition duration-150 ease-in-out"
                            >
                                Bayar Sekarang
                            </Link>
                            <SecondaryButton
                                v-if="order.status === 'pending'"
                                type="button"
                                class="!text-red-600 dark:!text-red-400 hover:!bg-red-50 dark:hover:!bg-red-950/40"
                                @click="cancelOrder(order.id)"
                            >
                                Batalkan Pesanan
                            </SecondaryButton>
                            <PrimaryButton
                                v-if="order.status === 'shipped'"
                                type="button"
                                @click="completeOrder(order.id)"
                            >
                                Konfirmasi Pesanan Diterima
                            </PrimaryButton>
                        </div>
                        <div
                            class="flex flex-row gap-2 justify-end items-center"
                        >
                            <p
                                class="font-medium text-sm text-gray-600 dark:text-gray-400"
                            >
                                Total Pembayaran:
                            </p>
                            <p
                                class="font-bold text-base text-gray-900 dark:text-gray-100"
                            >
                                {{ formatPrice(order.total_price) }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <Pagination :pagination="orders" />
        </div>

        <ReviewModal
            :show="showReviewModal"
            :order="reviewingOrder"
            :product="reviewingProduct"
            @close="closeReviewModal"
        />
    </AppLayout>
</template>
