<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import StatCard from "@/Components/StatCard.vue";
import SellerProductCard from "@/Components/SellerProductCard.vue";
import EmptyState from "@/Components/EmptyState.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import OrderProductCard from "@/Components/OrderProductCard.vue";
import InvoiceButton from "@/Components/InvoiceButton.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { Link, router, usePage } from "@inertiajs/vue3";

const { formatPrice } = useHelpers();

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_revenue: 0,
            total_orders: 0,
            pending_shipment: 0,
            shipped_orders: 0,
            completed_orders: 0,
            total_products: 0,
            active_products: 0,
            out_of_stock_products: 0,
        }),
    },
    recent_orders: {
        type: Array,
        default: () => [],
    },
});

const flash = computed(() => usePage().props.flash ?? {});

// Product filtering & search
const productSearch = ref("");
const productFilter = ref("all"); // 'all', 'in_stock', 'out_of_stock'

const filteredProducts = computed(() => {
    return props.products.filter((p) => {
        const query = productSearch.value.toLowerCase().trim();
        const productName = (p.name || p.title || "").toLowerCase();
        const productLocation = (p.location || "").toLowerCase();
        const matchesQuery =
            !query ||
            productName.includes(query) ||
            productLocation.includes(query) ||
            (p.categories && p.categories.some((c) => c.name?.toLowerCase().includes(query)));

        // Stock filter
        if (productFilter.value === "in_stock") {
            return matchesQuery && p.stock > 0;
        }
        if (productFilter.value === "out_of_stock") {
            return matchesQuery && p.stock <= 0;
        }
        return matchesQuery;
    });
});

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Shipping Modal logic
const shippingModal = ref(false);
const selectedOrder = ref(null);
const trackingNumber = ref("");
const errorTracking = ref("");

const openShippingModal = (order) => {
    selectedOrder.value = order;
    trackingNumber.value = "";
    errorTracking.value = "";
    shippingModal.value = true;
};

const closeShippingModal = () => {
    shippingModal.value = false;
    selectedOrder.value = null;
    trackingNumber.value = "";
    errorTracking.value = "";
};

const submitShip = () => {
    if (!trackingNumber.value.trim()) {
        errorTracking.value = "Nomor resi wajib diisi.";
        return;
    }

    router.post(
        route("admin.orders.ship", selectedOrder.value.id),
        { tracking_number: trackingNumber.value.trim() },
        {
            onSuccess: () => closeShippingModal(),
        }
    );
};
</script>

<template>
    <AppLayout title="Dashboard Penjual">
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Dashboard Penjual
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Pantau performa penjualan, kelola pesanan masuk, dan atur stok produk toko Anda.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <Link
                        :href="route('admin.orders')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-xs"
                    >
                        <span>Semua Pesanan</span>
                        <span
                            v-if="stats.pending_shipment > 0"
                            class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-amber-900 bg-amber-200 dark:bg-amber-800 dark:text-amber-100 rounded-full"
                        >
                            {{ stats.pending_shipment }}
                        </span>
                    </Link>

                    <Link
                        :href="route('jual.index')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition shadow-xs"
                    >
                        <svg
                            class="w-4 h-4 mr-1.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Tambah Produk
                    </Link>
                </div>
            </div>

            <div
                v-if="flash.success"
                class="mt-4 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-lg px-4 py-3 text-sm flex items-center justify-between"
            >
                <span>{{ flash.success }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <StatCard
                variant="dashboard"
                label="Total Pendapatan"
                :value="formatPrice(stats.total_revenue)"
                :subtext="`Dari ${stats.completed_orders + stats.shipped_orders} pesanan berhasil`"
            >
                <template #icon>
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </template>
            </StatCard>

            <StatCard
                variant="dashboard"
                label="Perlu Dikirim"
                :value="`${stats.pending_shipment} Pesanan`"
                :subtext="stats.pending_shipment > 0 ? 'Perlu input nomor resi' : 'Semua pesanan sudah dikirim'"
                :highlight="stats.pending_shipment > 0"
            >
                <template #icon>
                    <div
                        class="w-9 h-9 rounded-lg flex items-center justify-center"
                        :class="stats.pending_shipment > 0 ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </template>
            </StatCard>

            <StatCard
                variant="dashboard"
                label="Total Pesanan"
                :value="stats.total_orders"
                :subtext="`${stats.completed_orders} selesai • ${stats.shipped_orders} dikirim`"
            >
                <template #icon>
                    <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </template>
            </StatCard>

            <StatCard
                variant="dashboard"
                label="Produk Saya"
                :value="`${stats.active_products} Aktif`"
                :subtext="stats.out_of_stock_products > 0 ? `${stats.out_of_stock_products} stok habis / ${stats.total_products} total` : `Total ${stats.total_products} produk`"
            >
                <template #icon>
                    <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </template>
            </StatCard>
        </div>

        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Pesanan Masuk Terbaru
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Pesanan terakhir yang diterima dari pembeli.
                    </p>
                </div>
                <Link
                    v-if="recent_orders.length > 0"
                    :href="route('admin.orders')"
                    class="text-xs sm:text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center"
                >
                    Lihat Semua ({{ stats.total_orders }}) →
                </Link>
            </div>


            <Card v-if="recent_orders.length === 0" class="p-8 text-center">
                <EmptyState message="Belum ada pesanan masuk untuk produk Anda." />
            </Card>


            <div v-else class="space-y-4">
                <Card
                    v-for="order in recent_orders"
                    :key="order.id"
                    class="p-5 shadow-xs transition hover:border-gray-300 dark:hover:border-gray-600"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 mb-3 border-b border-gray-100 dark:border-gray-700/60">
                        <div>
                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                Pesanan #{{ order.id }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                Pembeli: <strong class="text-gray-800 dark:text-gray-200">{{ order.user?.name ?? 'Pengguna' }}</strong> • {{ formatDate(order.created_at) }}
                            </span>
                        </div>
                        <StatusSpan :status="order.status" variant="seller" />
                    </div>


                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-lg mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Kirim ke:</span>
                            <span>{{ order.shipping_address?.name ?? '-' }}, {{ order.shipping_address?.city ?? '-' }}</span>
                        </div>
                        <div v-if="order.shipping_service" class="inline-flex items-center gap-1 font-medium text-indigo-600 dark:text-indigo-400">
                            <span>Layanan: {{ order.shipping_service.toUpperCase() }}</span>
                            <span v-if="order.shipping_cost">({{ formatPrice(order.shipping_cost) }})</span>
                        </div>
                    </div>


                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 mb-3">
                        <OrderProductCard
                            v-for="item in order.items"
                            :key="item.id"
                            :item="item"
                        />
                    </div>


                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2">
                            <InvoiceButton :order-id="order.id" />

                            <PrimaryButton
                                v-if="order.status === 'paid'"
                                type="button"
                                @click="openShippingModal(order)"
                                class="!py-1.5 !text-xs"
                            >
                                Kirim Pesanan
                            </PrimaryButton>
                            <span
                                v-else-if="order.tracking_number"
                                class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-700 dark:text-gray-300"
                            >
                                Resi: {{ order.tracking_number }}
                            </span>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total Pembayaran:</span>
                            <span class="text-base font-bold text-primary-600 dark:text-primary-400">
                                {{ formatPrice(order.total_price) }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>
        </div>


        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Katalog Produk Saya
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Kelola katalog, stok, dan multi-kategori produk yang Anda tawarkan.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">

                    <div class="relative min-w-[200px]">
                        <input
                            v-model="productSearch"
                            type="text"
                            placeholder="Cari produk Anda..."
                            class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-3 py-1.5 focus:ring-1 focus:ring-primary-500 focus:outline-hidden"
                        />
                    </div>


                    <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700 text-xs">
                        <button
                            type="button"
                            @click="productFilter = 'all'"
                            :class="productFilter === 'all' ? 'bg-white dark:bg-gray-700 font-semibold shadow-xs text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300'"
                            class="px-2.5 py-1 rounded-md transition cursor-pointer"
                        >
                            Semua ({{ products.length }})
                        </button>
                        <button
                            type="button"
                            @click="productFilter = 'in_stock'"
                            :class="productFilter === 'in_stock' ? 'bg-white dark:bg-gray-700 font-semibold shadow-xs text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300'"
                            class="px-2.5 py-1 rounded-md transition cursor-pointer"
                        >
                            Tersedia
                        </button>
                        <button
                            type="button"
                            @click="productFilter = 'out_of_stock'"
                            :class="productFilter === 'out_of_stock' ? 'bg-white dark:bg-gray-700 font-semibold shadow-xs text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-300'"
                            class="px-2.5 py-1 rounded-md transition cursor-pointer"
                        >
                            Habis
                        </button>
                    </div>
                </div>
            </div>


            <div
                v-if="filteredProducts.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            >
                <div v-for="product in filteredProducts" :key="product.id" class="h-full">
                    <SellerProductCard :product="product" />
                </div>
            </div>


            <Card
                v-else
                class="p-10 text-center flex flex-col items-center justify-center"
            >
                <EmptyState
                    :message="productSearch ? 'Tidak ada produk yang cocok dengan pencarian Anda.' : 'Belum ada produk yang dijual.'"
                />
                <Link
                    v-if="!productSearch"
                    :href="route('jual.index')"
                    class="mt-4 inline-flex items-center px-4 py-2 text-xs font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition shadow-xs"
                >
                    + Mulai Jual Produk Sekarang
                </Link>
            </Card>
        </div>


        <DialogModal :show="shippingModal" @close="closeShippingModal">
            <template #title>
                Kirim Pesanan #{{ selectedOrder?.id }}
            </template>

            <template #content>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Masukkan nomor resi pengiriman untuk pesanan ini agar pembeli dapat melacak paket secara real-time.
                </p>

                <div v-if="selectedOrder" class="mb-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg text-xs space-y-1">
                    <p class="text-gray-700 dark:text-gray-300">
                        <strong>Tujuan:</strong> {{ selectedOrder.shipping_address?.name }} ({{ selectedOrder.shipping_address?.city }})
                    </p>
                    <p v-if="selectedOrder.shipping_service" class="text-gray-700 dark:text-gray-300">
                        <strong>Layanan Pengiriman:</strong> {{ selectedOrder.shipping_service.toUpperCase() }}
                    </p>
                </div>

                <div>
                    <InputLabel for="tracking_number" value="Nomor Resi Pengiriman" />
                    <TextInput
                        id="tracking_number"
                        v-model="trackingNumber"
                        type="text"
                        class="mt-1 block w-full text-sm"
                        placeholder="Contoh: JP1234567890 / SOC0987654321"
                    />
                    <p v-if="errorTracking" class="text-xs text-red-500 mt-1">
                        {{ errorTracking }}
                    </p>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center gap-2">
                    <SecondaryButton @click="closeShippingModal">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton @click="submitShip">
                        Konfirmasi Kirim
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>
    </AppLayout>
</template>
