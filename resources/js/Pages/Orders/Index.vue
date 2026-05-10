<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const { formatPrice } = useHelpers();

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
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
        pending: "bg-yellow-100 text-yellow-800",
        paid: "bg-blue-100 text-blue-800",
        shipped: "bg-indigo-100 text-indigo-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return map[status] ?? "bg-gray-100 text-gray-800";
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
            <p class="text-md text-black mb-10">
                Daftar pesanan yang telah Anda buat.
            </p>
        </slot>

        <div class="flex flex-col gap-4">
            <!-- Flash success message -->
            <div
                v-if="flash.success"
                class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3"
            >
                {{ flash.success }}
            </div>

            <div v-if="orders.length === 0">
                <Card class="max-w-none p-8 text-center">
                    <p class="text-gray-500">Belum ada pesanan.</p>
                </Card>
            </div>

            <Card
                v-for="order in orders"
                :key="order.id"
                class="max-w-none p-5"
            >
                <div class="w-full">
                    <!-- Order Header -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-4 border-b"
                    >
                        <div>
                            <p class="text-sm text-gray-500">
                                Pesanan #{{ order.id }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'text-xs font-semibold px-3 py-1 rounded-full',
                                statusClass(order.status),
                            ]"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>

                    <!-- Order Items -->
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-500">
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
                                class="border-t"
                            >
                                <td class="py-2">
                                    {{ item.product?.name ?? "-" }}
                                </td>
                                <td class="py-2">
                                    {{ formatPrice(item.price_snapshot) }}
                                </td>
                                <td class="py-2">{{ item.quantity }}</td>
                                <td class="py-2 text-right">
                                    {{ formatPrice(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Order Footer -->
                    <div class="flex justify-end mt-4 pt-4 border-t">
                        <div class="flex flex-row gap-2">
                            <p class="font-medium">Total Pembayaran:</p>
                            <p class="font-bold">
                                {{ formatPrice(order.total_price) }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
