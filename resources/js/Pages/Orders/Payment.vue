<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { useFeedback } from "@/Composable/useFeedback";
import { useMidtrans } from "@/Composable/useMidtrans";
import { router, Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

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
const { showLoading, hideLoading, showSuccess, showError, showInfo, showWarning, confirm } = useFeedback();
const { isSnapLoaded, isProcessing, loadSnapScript, pay } = useMidtrans();

onMounted(async () => {
    if (props.snapToken) {
        try {
            await loadSnapScript(props.midtransClientKey, props.isProduction);
        } catch (err) {
            console.error("Gagal memuat Midtrans Snap:", err);
        }
    }
});

// Bayar menggunakan Midtrans Snap SDK asli
const handleMidtransPay = async () => {
    if (!props.snapToken) {
        showError("Token Tidak Ditemukan", "Token pembayaran Midtrans belum dibuat. Silakan coba lagi.");
        return;
    }

    try {
        await pay(props.snapToken, {
            onSuccess: (result) => {
                showLoading("Menyelesaikan pembayaran...");
                router.post(
                    route("orders.pay", { id: props.order.id }),
                    {
                        payment_method: result.payment_type || "midtrans",
                        transaction_status: result.transaction_status || "settlement",
                    },
                    {
                        onSuccess: () => {
                            hideLoading();
                            showSuccess("Pembayaran Berhasil!", "Pembayaran Anda telah diverifikasi oleh Midtrans.");
                        },
                        onError: () => {
                            hideLoading();
                            showError("Gagal!", "Gagal memperbarui status pesanan.");
                        },
                    }
                );
            },
            onPending: (result) => {
                showInfo(
                    "Menunggu Pembayaran",
                    "Pesanan Anda tersimpan. Silakan selesaikan pembayaran sesuai petunjuk yang diberikan.",
                    () => {
                        router.visit(route("orders.index"));
                    }
                );
            },
            onError: (error) => {
                showError("Pembayaran Gagal", "Pembayaran Midtrans gagal atau dibatalkan.");
            },
            onClose: () => {
                showWarning(
                    "Pembayaran Ditutup",
                    "Anda menutup jendela pembayaran. Anda dapat membayarnya kapan saja melalui halaman Pesanan Saya."
                );
            },
        });
    } catch (error) {
        showError("Kesalahan", error.message || "Terjadi kesalahan saat memproses pembayaran.");
    }
};

// Simulasi Instan Fallback (untuk testing cepat tanpa simulator Midtrans)
const handleInstantPay = async () => {
    const isConfirmed = await confirm(
        "Simulasi Pembayaran",
        `Apakah Anda ingin langsung menyelesaikan pembayaran sebesar ${formatPrice(props.order.total_price)} secara instan?`
    );

    if (!isConfirmed) return;

    showLoading("Memproses pembayaran...");

    router.post(
        route("orders.pay", { id: props.order.id }),
        {
            payment_method: "instant_simulation",
            transaction_status: "settlement",
        },
        {
            onSuccess: () => {
                hideLoading();
                showSuccess("Berhasil!", "Pembayaran berhasil disimulasikan!");
            },
            onError: () => {
                hideLoading();
                showError("Gagal!", "Gagal memproses simulasi pembayaran.");
            },
        }
    );
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

const paymentChannels = [
    { name: "QRIS / GoPay", type: "E-Wallet", color: "bg-emerald-50 text-emerald-700 border-emerald-200" },
    { name: "ShopeePay", type: "E-Wallet", color: "bg-orange-50 text-orange-700 border-orange-200" },
    { name: "BCA Virtual Account", type: "Bank Transfer", color: "bg-blue-50 text-blue-700 border-blue-200" },
    { name: "Mandiri Bill", type: "Bank Transfer", color: "bg-indigo-50 text-indigo-700 border-indigo-200" },
    { name: "BNI Virtual Account", type: "Bank Transfer", color: "bg-teal-50 text-teal-700 border-teal-200" },
    { name: "BRI Virtual Account", type: "Bank Transfer", color: "bg-sky-50 text-sky-700 border-sky-200" },
    { name: "Permata VA", type: "Bank Transfer", color: "bg-purple-50 text-purple-700 border-purple-200" },
    { name: "Kartu Kredit/Debit", type: "Visa / Mastercard", color: "bg-rose-50 text-rose-700 border-rose-200" },
];
</script>

<template>
    <AppLayout title="Pembayaran Midtrans">
        <slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                        Pembayaran Pesanan
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Selesaikan transaksi pesanan #{{ order.id }} Anda secara aman.
                    </p>
                </div>
                <Link
                    :href="route('orders.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary-600 transition"
                >
                    &larr; Kembali ke Pesanan Saya
                </Link>
            </div>
        </slot>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Rincian Pesanan & Alamat (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Ringkasan Tagihan -->
                <Card class="p-5 max-w-none shadow-sm">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4 flex items-center justify-between">
                        <span>Ringkasan Tagihan</span>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                            Menunggu Pembayaran
                        </span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Pesanan</span>
                            <span class="font-mono font-medium text-gray-900 dark:text-gray-100">#{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Waktu Pemesanan</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode</span>
                            <span class="font-medium text-primary-600">Midtrans Snap</span>
                        </div>
                        <div class="flex justify-between border-t pt-3 font-semibold text-base">
                            <span class="text-gray-800 dark:text-gray-200">Total Pembayaran</span>
                            <span class="text-xl font-black text-primary-600 dark:text-primary-400">
                                {{ formatPrice(order.total_price) }}
                            </span>
                        </div>
                    </div>
                </Card>

                <!-- Alamat Pengiriman -->
                <Card v-if="order.shipping_address" class="p-5 max-w-none shadow-sm">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 border-b pb-3 mb-3">
                        Alamat Pengiriman
                    </h3>
                    <div class="text-sm space-y-1 text-gray-600 dark:text-gray-300">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ order.shipping_address.name }}
                        </p>
                        <p class="text-xs text-gray-500">{{ order.shipping_address.phone }}</p>
                        <p class="mt-2">{{ order.shipping_address.detail }}</p>
                        <p class="text-xs text-gray-500">
                            {{ order.shipping_address.subdistrict }}, {{ order.shipping_address.city }}, {{ order.shipping_address.province }} {{ order.shipping_address.postal_code }}
                        </p>
                    </div>
                </Card>

                <!-- Daftar Produk Pesanan -->
                <Card class="p-5 max-w-none shadow-sm">
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 border-b pb-3 mb-3">
                        Item Pesanan ({{ order.items.length }})
                    </h3>
                    <div class="divide-y max-h-64 overflow-y-auto pr-1">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="py-3 flex items-center justify-between text-sm gap-3"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="item.product"
                                    :src="getProductImage(item.product)"
                                    :alt="item.product.name"
                                    class="w-12 h-12 object-cover rounded-md border border-gray-200 flex-shrink-0"
                                />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1 leading-snug">
                                        {{ item.product?.name ?? "Produk" }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ item.quantity }} x {{ formatPrice(item.price_snapshot) }}
                                    </p>
                                </div>
                            </div>
                            <span class="font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                {{ formatPrice(item.subtotal) }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Kolom Kanan: Gateway Midtrans Snap (2/3) -->
            <div class="lg:col-span-2">
                <Card class="p-6 max-w-none h-full flex flex-col justify-between shadow-sm">
                    <div class="space-y-6">
                        <!-- Header Midtrans Branding -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-xl shadow-md">
                                    M
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            Midtrans Payment Gateway
                                        </h3>
                                        <span
                                            :class="[
                                                'text-[11px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider',
                                                isProduction
                                                    ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                                                    : 'bg-amber-100 text-amber-800 border border-amber-300'
                                            ]"
                                        >
                                            {{ isProduction ? 'Live' : 'Sandbox Mode' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Sistem pembayaran terenkripsi 256-bit SSL otomatis & terverifikasi resmi.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 self-start sm:self-center">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-semibold text-emerald-600">Snap Gateway Siap</span>
                            </div>
                        </div>

                        <!-- Card Aksi Utama: Tombol Bayar Snap -->
                        <div class="bg-gradient-to-br from-primary-50 via-white to-blue-50 dark:from-gray-800 dark:via-gray-850 dark:to-gray-900 border border-primary-200 dark:border-primary-800/40 rounded-2xl p-6 sm:p-8 text-center space-y-5">
                            <div class="w-16 h-16 rounded-full bg-primary-600 text-white flex items-center justify-center mx-auto shadow-lg shadow-primary-500/30">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>

                            <div class="max-w-md mx-auto">
                                <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    Siap Melakukan Pembayaran
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Klik tombol di bawah untuk membuka popup pembayaran Midtrans Snap dan memilih metode yang Anda inginkan.
                                </p>
                            </div>

                            <div class="max-w-sm mx-auto pt-2">
                                <PrimaryButton
                                    type="button"
                                    class="w-full py-3.5 text-base font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
                                    :loading="isProcessing"
                                    @click="handleMidtransPay"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Bayar Sekarang (Midtrans Snap)</span>
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Channel Pembayaran yang Didukung -->
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">
                                Pilihan Metode Pembayaran Midtrans Snap
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div
                                    v-for="(channel, idx) in paymentChannels"
                                    :key="idx"
                                    class="p-2.5 rounded-lg border text-center transition"
                                    :class="channel.color"
                                >
                                    <p class="font-bold text-xs">{{ channel.name }}</p>
                                    <p class="text-[10px] opacity-80 mt-0.5">{{ channel.type }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Box Informasi Sandbox / Simulator -->
                        <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-xl p-4 text-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-400">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <span>Petunjuk Pengujian Sandbox:</span>
                            </div>
                            <p class="text-amber-700 dark:text-amber-300 leading-relaxed">
                                Saat pop-up Snap terbuka, Anda dapat memilih metode Virtual Account (BCA/Mandiri/BNI) atau QRIS.
                                Untuk menyelesaikan simulasi transfer tanpa uang asli, salin nomor Virtual Account dan gunakan
                                <a
                                    href="https://simulator.sandbox.midtrans.com/"
                                    target="_blank"
                                    class="underline font-bold hover:text-amber-900 dark:hover:text-amber-200"
                                >
                                    Simulator Midtrans Resmi &nearr;
                                </a>.
                            </p>
                        </div>
                    </div>

                    <!-- Footer & Opsi Simulasi Instan -->
                    <div class="border-t pt-5 mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <button
                            type="button"
                            @click="handleInstantPay"
                            class="text-xs text-gray-500 hover:text-primary-600 underline text-left"
                        >
                            &bull; Atau klik di sini untuk simulasi verifikasi langsung (Quick Test)
                        </button>

                        <div class="flex items-center gap-3 justify-end">
                            <Link
                                :href="route('orders.index')"
                                class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium"
                            >
                                Bayar Nanti
                            </Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
