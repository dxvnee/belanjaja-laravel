<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback";
import ProductDetails from "@/Components/ProductDetails.vue";

const { confirm, showSuccess, showError, showLoading, hideLoading } =
    useFeedback();

const photoSlots = [1, 2, 3];

const form = useForm({
    judul: "",
    harga: "",
    stok: "",
    deskripsi: "",
    kategori: "",
    photo1: null,
    photo2: null,
    photo3: null,
});

const submit = async () => {
    const confirmed = await confirm(
        "Konfirmasi Jual",
        "Apakah Anda yakin ingin menjual barang ini?",
    );

    if (!confirmed) return;

    showLoading("Menjual barang...");

    form.post(route("jual.store"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => showSuccess("Berhasil", "Barang berhasil dijual!"),
        onError: (errors) =>
            showError("Gagal", "Gagal menjual barang. Silakan coba lagi."),
        onFinish: () => {
            form.reset();
            hideLoading();
        },
    });
};
</script>

<template>
    <AppLayout title="Jual">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Jual Barang
            </h2>
            <p class="text-md text-gray-600 dark:text-gray-400 mb-10">
                Pilih kategori barang yang ingin kamu jual!
            </p>
        </slot>

        <ProductDetails
            :photoSlots="photoSlots"
            :form="form"
            :submit="submit"
        />
    </AppLayout>
</template>
