<script setup>
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useHelpers } from "@/Composable/useHelpers";
import { router } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback";
import AddressDialog from "@/Components/AddressDialog.vue";
import { computed, ref } from "vue";
import AddressItem from "@/Components/AddressItem.vue";

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
    isBuyNow: {
        type: Boolean,
        default: false,
    },
    shippingOptions: {
        type: Array,
        default: () => [],
    },
    sellerLocation: {
        type: String,
        default: null,
    },
});

const selectedService = ref("reguler");

const currentShippingOption = computed(() => {
    return props.shippingOptions.find((opt) => opt.service === selectedService.value) 
        || props.shippingOptions[0] 
        || { cost: 10000, name: 'Reguler (Standar)' };
});

const shippingCost = computed(() => currentShippingOption.value?.cost ?? 10000);

const itemsSubtotal = computed(() => {
    return props.products.reduce((sum, item) => {
        return sum + parseFloat(item.product.price) * item.quantity;
    }, 0);
});

const grandTotal = computed(() => itemsSubtotal.value + shippingCost.value);

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
            quantities: props.products.reduce((acc, item) => {
                acc[item.product.id] = item.quantity;
                return acc;
            }, {}),
            is_buy_now: props.isBuyNow,
            address: selectedAddressItem.value,
            shipping_service: selectedService.value,
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

const addresses = ref(props.addresses);
const defaultAddr = props.addresses.find((a) => a.is_default) || props.addresses[0];
const selectedAddress = ref(defaultAddr?.id ?? 1);
const openAddressDialog = ref(false);

const selectedAddressItem = computed(() => {
    return addresses.value.find((a) => a.id == selectedAddress.value);
});

const changeAddress = (id) => {
    selectedAddress.value = id;
    openAddressDialog.value = false;
};

const openDialog = (value) => {
    openAddressDialog.value = value;
};
</script>

<template>
    <AppLayout title="Checkout">
        <Transition name="dialog">
            <AddressDialog
                v-if="openAddressDialog"
                :addresses="addresses"
                :selectedAddress="selectedAddress"
                :changeAddress="(id) => changeAddress(id)"
                :onDismiss="() => openDialog(false)"
            />
        </Transition>

        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Checkout
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-10">
                Berikut adalah produk yang akan Anda beli.
            </p>
        </slot>

        <div class="flex flex-col gap-4">
            <Card class="max-w-none p-5">
                <div class="w-full">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Alamat Pengiriman:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Alamat pengiriman akan disesuaikan dengan alamat yang
                        terdaftar di akun Anda.
                    </p>

                    <div
                        class="flex flex-row items-center justify-between gap-4"
                    >
                        <AddressItem :address="selectedAddressItem" />
                        <PrimaryButton @click="openDialog(true)"
                            >Ubah</PrimaryButton
                        >
                    </div>
                </div>
            </Card>

            <Card class="max-w-none p-5">
                <div class="w-full">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Produk Dipesan:</p>
                    <table class="w-full text-left mt-4 text-sm">
                        <thead class="text-gray-500 dark:text-gray-400">
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
                                class="border-t border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200"
                            >
                                <td class="py-3">
                                    <div
                                        class="flex flex-row items-center gap-3"
                                    >
                                        <img
                                            :src="getProductImage(item.product)"
                                            alt="product image"
                                            class="h-16 w-16 object-cover rounded border border-gray-200 dark:border-gray-700"
                                        />
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ item.product.name }}</p>
                                    </div>
                                </td>

                                <td class="py-3">
                                    {{ formatPrice(item.product.price) }}
                                </td>
                                <td class="py-3">{{ item.quantity }}</td>
                                <td class="py-3 font-semibold text-gray-900 dark:text-gray-100">
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
                <div class="w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">Opsi Pengiriman:</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Tarif ongkir disesuaikan berdasarkan jarak antara lokasi penjual dan kota tujuan Anda.
                            </p>
                        </div>
                        <div v-if="sellerLocation" class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full border border-gray-200 dark:border-gray-700">
                            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Asal: <strong>{{ sellerLocation }}</strong></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div
                            v-for="opt in shippingOptions"
                            :key="opt.service"
                            @click="selectedService = opt.service"
                            :class="[
                                'p-3.5 rounded-lg border cursor-pointer transition-all duration-150 flex flex-col justify-between',
                                selectedService === opt.service
                                    ? 'border-primary-600 bg-primary-50/50 dark:bg-primary-950/30 ring-2 ring-primary-500/20 dark:ring-primary-500/30'
                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                                        {{ opt.name }}
                                    </span>
                                    <span
                                        :class="[
                                            'w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                                            selectedService === opt.service
                                                ? 'border-primary-600 bg-primary-600 text-white'
                                                : 'border-gray-300 dark:border-gray-600'
                                        ]"
                                    >
                                        <svg v-if="selectedService === opt.service" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ opt.description }}
                                </p>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-baseline justify-between">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Est: {{ opt.etd }}
                                </span>
                                <span class="font-bold text-sm text-primary-600 dark:text-primary-400">
                                    {{ formatPrice(opt.cost) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <Card class="max-w-none p-5">
                <div class="w-full flex flex-col items-end gap-3">
                    <div class="w-full max-w-sm space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Subtotal Produk:</span>
                            <span>{{ formatPrice(itemsSubtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Ongkos Kirim ({{ currentShippingOption?.name }}):</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-gray-200 dark:border-gray-700 text-lg font-bold">
                            <span class="text-gray-800 dark:text-gray-200">Total Pembayaran:</span>
                            <span class="text-xl text-primary-600 dark:text-primary-400">{{ formatPrice(grandTotal) }}</span>
                        </div>
                    </div>
                    <PrimaryButton @click="beli"> Beli </PrimaryButton>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
