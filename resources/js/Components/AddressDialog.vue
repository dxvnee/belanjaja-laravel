<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import Card from "./Card.vue";
import PrimaryButton from "./PrimaryButton.vue";
import IconButton from "./IconButton.vue";
import AddressItem from "./AddressItem.vue";

const props = defineProps({
    addresses: {
        type: Array,
        default: () => [],
    },
    onDismiss: {
        type: Function,
        default: () => {},
    },
    selectedAddress: {
        type: Number,
        default: 0,
    },
    changeAddress: {
        type: Function,
        default: () => {},
    },
});

const selectedAddress = ref(props.selectedAddress);

const tambahAlamat = () => {
    props.onDismiss();
    router.visit(route("address.index"));
};

const setSelectedAddress = (id) => {
    selectedAddress.value = id;
};

const ubahAlamat = (id) => {
    props.changeAddress(id);
    props.onDismiss();
};
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-xs bg-black/50"
    >
        <Card class="dialog-card w-[700px] p-5">
            <div class="flex flex-col justify-between items-center gap-4">
                <div
                    class="flex flex-row w-full justify-start items-center gap-2"
                >
                    <IconButton :fun="onDismiss" icons="back" />
                    <p class="text-xl font-bold">Ubah Alamat</p>
                </div>

                <div class="flex w-full flex-col h-96">
                    <div
                        v-if="addresses.length != 0"
                        v-for="address in addresses"
                        class="flex w-full cursor-pointer pb-2"
                    >
                        <Card
                            class="w-full p-4"
                            :class="[
                                selectedAddress == address.id
                                    ? 'ring-2 ring-primary-500'
                                    : 'ring-gray-200',
                            ]"
                            @click="setSelectedAddress(address.id)"
                        >
                            <AddressItem :address="address" />
                        </Card>
                    </div>
                    <div v-else class="flex w-full justify-center items-center">
                        <p>Belum ada alamat!</p>
                    </div>
                </div>

                <div class="flex flex-row justify-end w-full gap-2">
                    <PrimaryButton variant="secondary" @click="tambahAlamat"
                        >Tambah Alamat</PrimaryButton
                    >
                    <PrimaryButton @click="ubahAlamat(selectedAddress)"
                        >Ubah Alamat</PrimaryButton
                    >
                </div>
            </div>
        </Card>
    </div>
</template>
