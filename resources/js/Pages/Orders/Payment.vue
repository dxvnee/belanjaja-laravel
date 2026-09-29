<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { useFeedback } from "@/Composable/useFeedback";
import { useMidtrans } from "@/Composable/useMidtrans";
import { router, Link } from "@inertiajs/vue3";
import { onMounted } from "vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    snapToken: {
        type: String,
        default: null,
    },
    midtransClientKey: {
        type: String,
        default: "",
    },
    isProduction: {
        type: Boolean,
        default: false,
    },
});

const { formatPrice, getProductImage } = useHelpers();
const {
    showLoading,
    hideLoading,
    showSuccess,
    showError,
    showInfo,
    showWarning,
} = useFeedback();
const { isProcessing, loadSnapScript, pay } = useMidtrans();

onMounted(async () => {
    if (props.snapToken) {
        try {
            await loadSnapScript(props.midtransClientKey, props.isProduction);
        } catch (err) {
            console.error("Gagal memuat modul pembayaran:", err);
        }
    }
});

const handlePay = async () => {
    if (!props.snapToken) {
        showError("Gagal", "Sesi pembayaran tidak ditemukan. Silakan muat ulang halaman.");
        return;
    }

    try {
        await pay(props.snapToken, {
            onSuccess: (result) => {
                showLoading("Menyelesaikan pesanan...");
                router.post(

                    route("orders.pay", { id: props.order.id }),
                    {
                        payment_method: result.payment_type || "online_payment",
                        transaction_status: result.transaction_status || "settlement",
                    },
                    {
                        onSuccess: () => {
                            hideLoading();
                            showSuccess("Pembayaran Berhasil", "Pembayaran Anda telah berhasil diverifikasi.");
                        },
                        onError: () => {
                            hideLoading();
                            showError("Gagal", "Gagal memperbarui status pesanan.");
                        },
                    }
                );
            },
            onPending: () => {
                showInfo(
                    "Menunggu Pembayaran",
                    "Pesanan Anda tersimpan. Silakan selesaikan pembayaran sesuai instruksi yang diberikan.",
                    () => {
                        router.visit(route("orders.index"));
                    }
                );
            },
            onError: () => {
                showError("Pembayaran Gagal", "Pembayaran gagal diproses atau telah dibatalkan.");
            },
            onClose: () => {
                showWarning(
                    "Pembayaran Belum Selesai",
                    "Anda dapat melanjutkan pembayaran kapan saja melalui halaman Pesanan Saya."
                );
            },
        });
    } catch (error) {
        showError("Kesalahan", error.message || "Terjadi kesalahan saat memproses pembayaran.");
    }
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <AppLayout title="Pembayaran">
        <slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Pembayaran Pesanan
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-8">
                        Selesaikan transaksi untuk pesanan #{{ order.id }}.
                    </p>
                </div>
                <Link
                    :href="route('orders.index')"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                >
                    &larr; Kembali ke Pesanan Saya
                </Link>
            </div>
        </slot>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Daftar Produk -->
                <Card class="p-6 max-w-none">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                        Daftar Produk ({{ order.items.length }})
                    </h3>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto pr-1">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="py-3 flex items-center justify-between text-sm gap-4"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="item.product"
                                    :src="getProductImage(item.product)"
                                    :alt="item.product.name"
                                    class="w-14 h-14 object-cover rounded-md border border-gray-200 dark:border-gray-700 flex-shrink-0"
                                />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1">
                                        {{ item.product?.name ?? "Produk" }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ item.quantity }} &times; {{ formatPrice(item.price_snapshot) }}
                                    </p>
                                </div>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                {{ formatPrice(item.subtotal) }}
                            </span>
                        </div>
                    </div>
                </Card>

                <!-- Alamat Pengiriman -->
                <Card v-if="order.shipping_address" class="p-6 max-w-none">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-3 mb-3">
                        Alamat Pengiriman
                    </h3>
                    <div class="text-sm space-y-1 text-gray-600 dark:text-gray-300">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ order.shipping_address.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.shipping_address.phone }}</p>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">{{ order.shipping_address.detail }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ order.shipping_address.subdistrict }}, {{ order.shipping_address.city }}, {{ order.shipping_address.province }} {{ order.shipping_address.postal_code }}
                        </p>
                    </div>
                </Card>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <Card class="p-6 max-w-none h-full">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                            Ringkasan Pembayaran
                        </h3>
                        <StatusSpan :status="order.status" />
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">ID Pesanan</span>
                            <span class="font-mono text-gray-900 dark:text-gray-100">#{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Waktu Pemesanan</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-3 font-semibold text-base">
                            <span class="text-gray-800 dark:text-gray-200">Total Tagihan</span>
                            <span class="text-xl font-bold text-primary-600 dark:text-primary-400">
                                {{ formatPrice(order.total_price) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 space-y-3">
                        <PrimaryButton
                            class="w-full flex justify-center items-center py-3 text-sm font-semibold"
                            :loading="isProcessing"
                            @click="handlePay"
                        >
                            Bayar Sekarang
                        </PrimaryButton>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
