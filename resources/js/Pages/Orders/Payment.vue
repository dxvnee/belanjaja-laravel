<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { useFeedback } from "@/Composable/useFeedback";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const { formatPrice } = useHelpers();
const { showLoading, hideLoading, showSuccess, showError, confirm } = useFeedback();

const paymentMethods = [
    { id: "bca", name: "BCA Virtual Account", category: "Transfer Bank", va: "8801208954321098" },
    { id: "mandiri", name: "Mandiri Virtual Account", category: "Transfer Bank", va: "8960812345678901" },
    { id: "bni", name: "BNI Virtual Account", category: "Transfer Bank", va: "8810203040506070" },
    { id: "gopay", name: "GoPay / QRIS", category: "E-Wallet", qris: true },
];

const selectedMethod = ref(paymentMethods[0]);

const pay = async () => {
    const isConfirmed = await confirm(
        "Konfirmasi Pembayaran",
        `Apakah Anda yakin ingin membayar sebesar ${formatPrice(props.order.total_price)} menggunakan ${selectedMethod.value.name}?`
    );

    if (!isConfirmed) return;

    showLoading("Memproses pembayaran...");

    router.visit(route("orders.pay", { id: props.order.id }), {
        method: "post",
        data: {
            payment_method: selectedMethod.value.id,
        },
        onSuccess: () => {
            showSuccess("Berhasil!", "Pembayaran berhasil diverifikasi!");
        },
        onError: () => {
            showError("Gagal!", "Gagal memproses pembayaran. Silakan coba lagi.");
        },
        onFinish: () => {
            hideLoading();
        },
    });
};
</script>

<template>
    <AppLayout title="Pembayaran">
        <slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pembayaran Pesanan
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-6">
                Selesaikan pembayaran untuk pesanan #{{ order.id }} Anda.
            </p>
        </slot>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Ringkasan Pesanan (1/3 lebar) -->
            <div class="lg:col-span-1 space-y-6">
                <Card class="p-5 max-w-none">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Ringkasan Tagihan
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Pesanan</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">#{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="font-semibold text-yellow-600">Menunggu Pembayaran</span>
                        </div>
                        <div class="flex justify-between border-t pt-3 font-semibold text-base">
                            <span class="text-gray-800 dark:text-gray-200">Total Pembayaran</span>
                            <span class="text-primary-600 dark:text-primary-400">{{ formatPrice(order.total_price) }}</span>
                        </div>
                    </div>
                </Card>

                <Card class="p-5 max-w-none">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Daftar Produk
                    </h3>
                    <div class="divide-y max-h-60 overflow-y-auto">
                        <div v-for="item in order.items" :key="item.id" class="py-2.5 flex justify-between text-sm">
                            <div class="pr-4">
                                <p class="font-medium text-gray-850 dark:text-gray-200 leading-tight">{{ item.product?.name ?? "-" }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ item.quantity }} x {{ formatPrice(item.price_snapshot) }}</p>
                            </div>
                            <span class="font-semibold text-gray-850 dark:text-gray-200 self-center">
                                {{ formatPrice(item.subtotal) }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Metode Pembayaran & Simulasi Midtrans (2/3 lebar) -->
            <div class="lg:col-span-2">
                <Card class="p-6 max-w-none h-full flex flex-col justify-between">
                    <div>
                        <!-- Header Simulasi Midtrans -->
                        <div class="flex items-center justify-between border-b pb-4 mb-6">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></div>
                                <span class="text-xs font-semibold tracking-wider text-blue-500 uppercase">Simulasi Midtrans Payment</span>
                            </div>
                            <span class="text-xs text-gray-400">Merchant: Belanjaja Store</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pilihan Metode -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Pilih Metode Pembayaran
                                </h4>
                                <div class="space-y-2">
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="method.id"
                                        type="button"
                                        class="w-full text-left p-3.5 rounded-lg border transition-all duration-200 flex items-center justify-between"
                                        :class="[
                                            selectedMethod.id === method.id
                                                ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-950/20 ring-1 ring-primary-500'
                                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
                                        ]"
                                        @click="selectedMethod = method"
                                    >
                                        <div>
                                            <p class="font-medium text-sm text-gray-900 dark:text-gray-150">
                                                {{ method.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ method.category }}
                                            </p>
                                        </div>
                                        <div
                                            class="w-4 h-4 rounded-full border flex items-center justify-center"
                                            :class="[
                                                selectedMethod.id === method.id
                                                    ? 'border-primary-500 bg-primary-500'
                                                    : 'border-gray-300'
                                            ]"
                                        >
                                            <div v-if="selectedMethod.id === method.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Detail & Petunjuk Pembayaran -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-5 rounded-xl border border-gray-100 dark:border-gray-800 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-350 mb-3">
                                        Petunjuk Pembayaran
                                    </h4>
                                    
                                    <!-- Jika Virtual Account -->
                                    <div v-if="selectedMethod.va" class="space-y-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Nomor Virtual Account</p>
                                            <div class="flex items-center justify-between mt-1 bg-white dark:bg-gray-800 p-2.5 rounded-md border border-gray-150 dark:border-gray-700">
                                                <span class="font-mono font-bold text-gray-850 dark:text-gray-150 tracking-wider">
                                                    {{ selectedMethod.va }}
                                                </span>
                                                <button 
                                                    type="button" 
                                                    class="text-xs text-primary-600 font-medium hover:text-primary-700"
                                                    @click="navigator?.clipboard?.writeText(selectedMethod.va)"
                                                >
                                                    Salin
                                                </button>
                                            </div>
                                        </div>
                                        <ol class="text-xs text-gray-500 dark:text-gray-400 list-decimal list-inside space-y-1.5">
                                            <li>Gunakan Mobile Banking atau ATM terdekat.</li>
                                            <li>Pilih menu Transfer / Virtual Account.</li>
                                            <li>Masukkan nomor Virtual Account di atas.</li>
                                            <li>Konfirmasi nominal tagihan sudah sesuai.</li>
                                        </ol>
                                    </div>

                                    <!-- Jika QRIS / GoPay -->
                                    <div v-else-if="selectedMethod.qris" class="flex flex-col items-center py-2 space-y-4">
                                        <!-- Mock QR Code menggunakan CSS murni agar tidak overcode -->
                                        <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-xs flex flex-col items-center">
                                            <div class="w-32 h-32 bg-gray-900 flex flex-wrap p-2 gap-1 rounded-sm">
                                                <div v-for="n in 16" :key="n" class="w-6 h-6 border-2 border-white rounded-xs" :class="n % 3 === 0 ? 'bg-white' : 'bg-transparent'"></div>
                                            </div>
                                            <span class="text-[10px] font-mono tracking-widest text-gray-400 mt-2">NMID: ID102021029</span>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">
                                            Buka aplikasi e-wallet Anda (GoPay, OVO, ShopeePay, Dana, LinkAja) lalu scan QR code di atas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Konfirmasi Bayar -->
                    <div class="border-t pt-4 mt-8 flex justify-end">
                        <PrimaryButton 
                            class="px-6 py-2.5 w-full md:w-auto" 
                            @click="pay"
                        >
                            Konfirmasi Pembayaran
                        </PrimaryButton>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
