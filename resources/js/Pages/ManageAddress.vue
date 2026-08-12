<script setup>
import Card from "@/Components/Card.vue";
import CreateAddressDialog from "@/Components/CreateAddressDialog.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback.js";
import IconButton from "@/Components/IconButton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import AddressItem from "@/Components/AddressItem.vue";

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
            <div class="flex flex-col mb-2">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Atur Alamat
                </h2>
                <p class="text-md text-black">
                    Berikut adalah produk yang akan Anda beli.
                </p>
            </div>
        </slot>

        <div>
            <div class="flex justify-end items-center mb-4">
                <PrimaryButton @click="openDialogChange(true)">
                    + Tambah Alamat Baru
                </PrimaryButton>
            </div>

            <div v-if="addresses.length != 0" class="space-y-4">
                <Card
                    v-for="address in addresses"
                    :key="address.id"
                    class="p-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <AddressItem :address="address"/>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <IconButton
                                @click="editAddress(address)"
                                class="size-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors"
                                title="Ubah Alamat"
                                icons="edit"
                            />
                            <IconButton
                                @click="deleteAddress(address.id)"
                                class="size-2 text-gray-500 hover:text-red-650 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-full transition-colors"
                                title="Hapus Alamat"
                                icons="trash"
                            />
                        </div>
                    </div>
                </Card>
            </div>

            <div
                v-else
                class="flex flex-col justify-center items-center h-96 bg-white dark:bg-gray-900 rounded-lg border border-gray-300 dark:border-gray-700 p-6 shadow-sm"
            >
                <EmptyState/>
            </div>
        </div>
    </AppLayout>
</template>
