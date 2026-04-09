<script setup>
import Card from "@/Components/Card.vue";
import InputGambar from "@/Components/InputGambar.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";

const photoSlots = [1, 2, 3];

const form = useForm({
    judul: "",
    harga: "",
    deskripsi: "",
});

const submit = () => {};
</script>

<template>
    <AppLayout title="Jual">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Jual Barang
            </h2>
            <p class="text-md text-black mb-10">
                Pilih kategori barang yang ingin kamu jual!
            </p>
        </slot>

        <div class="flex w-full justify-center">
            <Card class="mx-auto w-full !max-w-none">
                <p class="text-lg font-medium text-black">Unggah Foto Iklan</p>
                <p class="text-sm text-black">
                    Foto Anda akan menjadi foto sampul/thumbnail
                </p>

                <div class="grid grid-cols-1 gap-4 pb-5 pt-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="slot in photoSlots"
                        :key="slot"
                        class="flex flex-col gap-2"
                    >
                        <InputGambar />
                        <p class="text-sm text-black text-center">
                            Foto {{ slot }}
                        </p>
                    </div>
                </div>

                <p class="text-lg font-medium text-black">
                    Berikan Detail Item Anda
                </p>

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="flex-1">
                                <InputLabel for="judul" value="Judul Iklan" />
                                <TextInput
                                    id="judul"
                                    v-model="form.judul"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                    autocomplete="off"
                                    placeholder="Judul Iklan"
                                    icon="iklan"
                                />
                            </div>

                            <div class="flex-1">
                                <InputLabel for="harga" value="Harga" />
                                <TextInput
                                    id="harga"
                                    v-model="form.harga"
                                    type="number"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="off"
                                    placeholder="Harga"
                                    icon="harga"
                                />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <TextInput
                                id="deskripsi"
                                v-model="form.deskripsi"
                                type="textarea"
                                class="mt-1 block w-full h-64"
                                required
                                autocomplete="off"
                                placeholder="Deskripsi"
                                icon="deskripsi"
                            />
                        </div>

                        <div class="flex justify-end pt-2">
                            <PrimaryButton
                                class="w-full sm:w-24"
                                :class="{
                                    'opacity-25': form.processing,
                                }"
                                :disabled="form.processing"
                            >
                                Jual
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
