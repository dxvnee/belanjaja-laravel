<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { usePage, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import Pagination from "@/Components/Pagination.vue";

const { formatPrice } = useHelpers();

const props = defineProps({
    orders: Object,
});

const flash = computed(() => usePage().props.flash ?? {});

const statusLabel = (status) => {
    const map = {
        pending: "Menunggu Pembayaran",
        paid: "Sudah Dibayar",
        shipped: "Dikirim",
        completed: "Selesai",
        cancelled: "Dibatalkan",
    };
    return map[status] ?? status;
};

const statusClass = (status) => {
    const map = {
        pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 dark:border dark:border-yellow-700/50",
        paid: "bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 dark:border dark:border-blue-700/50",
        shipped: "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border dark:border-indigo-700/50",
        completed: "bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 dark:border dark:border-green-700/50",
        cancelled: "bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 dark:border dark:border-red-700/50",
    };
    return map[status] ?? "bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300";
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
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

            <div v-if="orders.data.length === 0">
                <Card class="max-w-none p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada pesanan.</p>
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
                                Pesanan #{{ order.id }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <StatusSpan
                            :status-color="statusClass(order.status)"
                            :status-label="statusLabel(order.status)"
                        />
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
                                    {{ item.product?.name ?? "-" }}
                                </td>
                                <td class="py-2.5">
                                    {{ formatPrice(item.price_snapshot) }}
                                </td>
                                <td class="py-2.5">{{ item.quantity }}</td>
                                <td class="py-2.5 text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ formatPrice(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 gap-4">
                        <div>
                            <Link
                                v-if="order.status === 'pending'"
                                :href="route('orders.payment', { id: order.id })"
                                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-xs text-white bg-primary-600 hover:bg-primary-700 focus:outline-none transition duration-150 ease-in-out"
                            >
                                Bayar Sekarang
                            </Link>
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
    </AppLayout>
</template>
