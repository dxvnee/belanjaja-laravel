<script setup>
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { router } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback";
import AddressDialog from "@/Components/AddressDialog.vue";
import { ref } from "vue";

const { formatPrice, getProductImage } = useHelpers();
const { showLoading, hideLoading, showSuccess, showError, confirm } =
    useFeedback();

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    addresses: {
        type: Array,
        default: () => [],
    },
});

const total = props.products.reduce((sum, item) => {
    return sum + parseFloat(item.product.price) * item.quantity;
}, 0);

const beli = async () => {
    const confirmed = await confirm(
        "Konfirmasi Pembelian",
        "Apakah Anda yakin ingin membeli produk ini?",
    );

    if (!confirmed) return;

    showLoading("Memproses pesanan...");

    router.visit(route("checkout.store"), {
        method: "post",
        data: {
            product_ids: props.products.map((item) => item.product.id),
        },
        onSuccess: () => {
            showSuccess("Pesanan berhasil dibuat!");
        },
        onError: () => {
            showError("Gagal membuat pesanan. Silakan coba lagi.");
        },
        onFinish: () => {
            hideLoading();
        },
    });
};

const openAddressDialog = ref(false);

const openDialog = (value) => {
    openAddressDialog.value = value;
};
</script>

<template>
    <AppLayout title="Checkout">
        <AddressDialog
            v-if="openAddressDialog"
            :addresses="addresses"
            :onDismiss="() => openDialog(false)"
        />

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

                    <div class="flex gap-4">
                        <h1 class="font-bold">
                            Eigiya Daramuli Kale (081366366550)
                        </h1>
                        <p>
                            Jalan Tanimbar No. 16, RT.7/RW.5, Cimone Jaya, KOTA
                            TANGERANG - KARAWACI, BANTEN, ID 15114
                        </p>
                        <PrimaryButton @click="openDialog(true)"
                            >Ubah</PrimaryButton
                        >
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
                                v-for="item in products"
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
                    <div class="flex flex-row gap-2">
                        <p class="text-lg">Total Pembayaran:</p>
                        <p class="text-lg font-bold">
                            {{ formatPrice(total) }}
                        </p>
                    </div>
                    <PrimaryButton @click="beli"> Beli </PrimaryButton>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
