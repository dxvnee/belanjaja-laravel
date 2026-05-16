<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { useFeedback } from "@/Composable/useFeedback";
import ProductDetails from "@/Components/ProductDetails.vue";

const { confirm, showSuccess, showError, showLoading, hideLoading } =
    useFeedback();

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const photoSlots = [1, 2, 3];

const form = useForm({
    judul: props.product.name,
    harga: props.product.price,
    stok: props.product.stock,
    deskripsi: props.product.description,
    kategori: props.product.category_id,
    images: props.product.images || [],
    photo1: null,
    photo2: null,
    photo3: null,
});

const submit = async () => {
    const confirmed = await confirm(
        "Konfirmasi Edit",
        "Apakah Anda yakin ingin menyimpan perubahan?",
    );

    if (!confirmed) return;

    showLoading("Menyimpan perubahan...");

    form.post(route("product.update", { id: props.product.id }), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => showSuccess("Berhasil", "Produk berhasil diperbarui!"),
        onError: () =>
            showError("Gagal", "Gagal memperbarui produk. Silakan coba lagi."),
        onFinish: () => hideLoading(),
    });
};
</script>

<template>
    <AppLayout title="Edit Produk">
        <slot name="header">
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Edit Produk
            </h2>
        </slot>

        <ProductDetails
            :photoSlots="photoSlots"
            :form="form"
            :submit="submit"
            submitLabel="Simpan"
        />
    </AppLayout>
</template>
