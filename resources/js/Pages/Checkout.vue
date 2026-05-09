<script setup>
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useHelpers } from "@/Composable/useHelpers";

const { formatPrice, getProductImage } = useHelpers();

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

</script>

<template>
    <AppLayout title="Checkout">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Checkout
            </h2>
            <p class="text-md text-black mb-10">
                Berikut adalah produk yang akan Anda beli.
            </p>
        </slot>

        <div class="flex flex-col gap-4">
            <Card class="max-w-none p-5">
                <div class="w-full">
                    <p>Alamat Pengiriman:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Alamat pengiriman akan disesuaikan dengan alamat yang
                        terdaftar di akun Anda.
                    </p>

                    <div class="flex">
                        <h1 class="font-bold">
                            Eigiya Daramuli Kale (081366366550)
                        </h1>
                        <p>
                            Jalan Tanimbar No. 16, RT.7/RW.5, Cimone Jaya, KOTA
                            TANGERANG - KARAWACI, BANTEN, ID 15114
                        </p>
                        <PrimaryButton> Ubah Alamat </PrimaryButton>
                    </div>
                </div>
            </Card>

            <Card class="max-w-none p-5">
                <div class="w-full">
                    <p>Produk Dipesan:</p>
                    <table class="w-full text-left mt-4">
                        <thead>
                            <tr>
                                <th class="pb-2">Nama Produk</th>
                                <th class="pb-2">Harga</th>
                                <th class="pb-2">Jumlah</th>
                                <th class="pb-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item) in products"
                                :key="item.id"
                                class="border-t"
                            >
                                <td class="py-2">
                                    <div
                                        class="flex flex-row items-center gap-3"
                                    >
                                        <img
                                            :src="getProductImage(item.product)"
                                            alt="product image"
                                            class="h-16 w-16 object-cover rounded"
                                        />
                                        <p>{{ item.product.name }}</p>
                                    </div>
                                </td>

                                <td class="py-2">
                                    {{ formatPrice(item.product.price) }}
                                </td>
                                <td class="py-2">{{ item.quantity }}</td>
                                <td class="py-2">
                                    {{
                                        formatPrice(
                                            item.product.price * item.quantity,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <Card class="max-w-none p-5">
                <div class="w-full flex flex-col items-end gap-4">
                    <p class="text-lg font-bold">
                        Total Pembayaran: {{ formatPrice(total) }}
                    </p>
                    <PrimaryButton> Lanjut ke Pembayaran </PrimaryButton>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
