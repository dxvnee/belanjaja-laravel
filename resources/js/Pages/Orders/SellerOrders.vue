<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { usePage, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import OrderProductCard from "@/Components/OrderProductCard.vue";

const { formatPrice } = useHelpers();

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

// Modal Kirim Pesanan
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
    <AppLayout title="Pesanan Masuk">
        <slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-10">
                <div>
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Pesanan Masuk
                    </h2>
                    <p class="text-md text-gray-600 dark:text-gray-400">
                        Kelola pesanan dari pembeli untuk barang yang kamu jual.
                    </p>
                </div>

                <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700">
                    <Link
                        :href="route('admin.show')"
                        class="px-3.5 py-1.5 rounded-md text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                    >
                        Produk Saya
                    </Link>
                    <span
                        class="px-3.5 py-1.5 rounded-md text-xs sm:text-sm font-semibold bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-xs"
                    >
                        Pesanan Masuk
                    </span>
                </div>
            </div>
        </slot>

        <div class="flex flex-col gap-4">
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
                    <p class="text-gray-500 dark:text-gray-400">Belum ada pesanan masuk.</p>
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
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Pesanan #{{ order.id }} • Pembeli: {{ order.user?.name ?? "-" }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <StatusSpan :status="order.status" variant="seller" />
                    </div>

                    <div class="text-xs text-gray-600 dark:text-gray-400 mb-3 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Alamat Pengiriman:</span>
                        {{ order.shipping_address?.name ?? '-' }} ({{ order.shipping_address?.phone ?? '-' }}) -
                        {{ order.shipping_address?.detail ?? '-' }}, {{ order.shipping_address?.city ?? '' }} {{ order.shipping_address?.postal_code ?? '' }}
                    </div>

                    <div
                        v-if="order.tracking_number"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-3"
                    >
                        Nomor Resi: <span class="font-mono text-gray-800 dark:text-gray-200">{{ order.tracking_number }}</span>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <OrderProductCard
                            v-for="item in order.items"
                            :key="item.id"
                            :item="item"
                        />
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 gap-4">
                        <div>
                            <PrimaryButton
                                v-if="order.status === 'paid'"
                                type="button"
                                @click="openShippingModal(order)"
                            >
                                Kirim Pesanan
                            </PrimaryButton>
                        </div>
                        <div class="flex flex-row gap-2 justify-end items-center">
                            <p class="font-medium text-sm text-gray-600 dark:text-gray-400">Total Pembayaran:</p>
                            <p class="font-bold text-base text-gray-900 dark:text-gray-100">
                                {{ formatPrice(order.total_price) }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <Pagination :pagination="orders" />
        </div>

        <DialogModal :show="shippingModal" @close="closeShippingModal">
            <template #title>
                Kirim Pesanan #{{ selectedOrder?.id }}
            </template>

            <template #content>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Masukkan nomor resi pengiriman untuk pesanan ini agar pembeli dapat melacak paketnya.
                </p>

                <div>
                    <InputLabel for="tracking_number" value="Nomor Resi Pengiriman" />
                    <TextInput
                        id="tracking_number"
                        v-model="trackingNumber"
                        type="text"
                        class="mt-1 block w-full"
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
