<script setup>
import Card from "@/Components/Card.vue";
import CreateAddressDialog from "@/Components/CreateAddressDialog.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback.js";

const { confirm, showSuccess, showError, showLoading, hideLoading } =
    useFeedback();

defineProps({
    addresses: {
        type: Array,
        default: () => [],
    },
});

const openDialog = ref(false);
const editingAddress = ref(null);

const openDialogChange = (value, address = null) => {
    editingAddress.value = address;
    openDialog.value = value;
};

const editAddress = (address) => {
    openDialogChange(true, address);
};

const deleteAddress = async (id) => {
    const confirmed = await confirm(
        "Hapus Alamat",
        "Apakah Anda yakin ingin menghapus alamat ini?",
    );
    if (!confirmed) return;

    showLoading("Menghapus alamat...");
    router.delete(route("address.destroy", id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess("Berhasil!", "Alamat berhasil dihapus!");
        },
        onError: () => {
            showError("Gagal!", "Gagal menghapus alamat!");
        },
        onFinish: () => {
            hideLoading();
        },
    });
};
</script>

<template>
    <AppLayout title="Atur Alamat">
        <CreateAddressDialog
            v-if="openDialog"
            :address="editingAddress"
            :onDismiss="() => openDialogChange(false)"
        />
        <slot name="header">
            <div class="flex flex-row items-center justify-between">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Atur Alamat
                </h2>
            </div>
        </slot>

        <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Daftar Alamat Pengiriman
                </h3>
                <PrimaryButton @click="openDialogChange(true)">
                    <span class="flex items-center gap-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-4 h-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4.5v15m7.5-7.5h-15"
                            />
                        </svg>
                        Tambah Alamat Baru
                    </span>
                </PrimaryButton>
            </div>

            <!-- List Alamat -->
            <div v-if="addresses.length != 0" class="space-y-4">
                <Card
                    v-for="address in addresses"
                    :key="address.id"
                    class="p-5 border border-gray-250 dark:border-gray-800 hover:shadow-md transition-shadow duration-200"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <!-- Icon Pin Alamat -->
                            <div
                                class="mt-1 flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <!-- Nama & No HP -->
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span
                                        class="font-bold text-gray-900 dark:text-gray-100 text-lg"
                                    >
                                        {{ address.name }}
                                    </span>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400 font-medium px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800"
                                    >
                                        {{ address.phone }}
                                    </span>
                                </div>

                                <!-- Detail Alamat -->
                                <p
                                    class="mt-2 text-gray-700 dark:text-gray-300 leading-relaxed text-sm"
                                >
                                    {{ address.detail }}
                                </p>

                                <!-- Wilayah & Kode Pos -->
                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ address.subdistrict }},
                                    {{ address.city }},
                                    {{ address.province }}
                                    {{ address.postal_code }}
                                </p>
                            </div>
                        </div>

                        <!-- Aksi (Edit & Delete) -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                @click="editAddress(address)"
                                class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors"
                                title="Ubah Alamat"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="deleteAddress(address.id)"
                                class="p-2 text-gray-500 hover:text-red-650 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-full transition-colors"
                                title="Hapus Alamat"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-5 h-5 text-red-500"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.74 9-.34 9m-4.72 0-.34-9m9.03-3.03a37.95 37.95 0 0 0-4.47-.28 37.95 37.95 0 0 0-4.47.28M15.35 4.85l-.08-.49c-.15-.9-1.02-1.57-1.92-1.57h-2.7c-.9 0-1.77.67-1.92 1.57l-.08.49M19.5 7.125v12.562c0 1.017-.83 1.838-1.838 1.838H6.338C5.33 21.362 4.5 20.54 4.5 19.525V7.125"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col justify-center items-center h-96 bg-white dark:bg-gray-900 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 shadow-sm"
            >
                <div
                    class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-4 shadow-inner"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-8 h-8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
                        />
                    </svg>
                </div>
                <p
                    class="text-gray-500 dark:text-gray-400 font-medium mb-4 text-center"
                >
                    Belum ada alamat pengiriman terdaftar.
                </p>
                <PrimaryButton @click="openDialogChange(true)"
                    >Tambah Alamat Sekarang</PrimaryButton
                >
            </div>
        </div>
    </AppLayout>
</template>
