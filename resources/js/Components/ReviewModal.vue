<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import StarRating from "@/Components/StarRating.vue";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    order: {
        type: Object,
        default: null,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "submitted"]);

const ratingLabels = {
    1: "Sangat Buruk",
    2: "Buruk",
    3: "Cukup",
    4: "Bagus",
    5: "Sangat Bagus",
};

const form = useForm({
    order_id: null,
    product_id: null,
    rating: 5,
    comment: "",
});

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen && props.order && props.product) {
            form.order_id = props.order.id;
            form.product_id = props.product.id;
            form.rating = 5;
            form.comment = "";
            form.clearErrors();
        }
    }
);

const close = () => {
    form.reset();
    form.clearErrors();
    emit("close");
};

const submitReview = () => {
    form.post(route("reviews.store"), {
        preserveScroll: true,
        onSuccess: () => {
            close();
            emit("submitted");
        },
    });
};
</script>

<template>
    <DialogModal :show="show" @close="close">
        <template #title>
            Beri Ulasan Produk
        </template>

        <template #content>
            <div class="space-y-4">
                <div v-if="product" class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Produk yang diulas:</p>
                    <p class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                        {{ product.name }}
                    </p>
                </div>

                <div>
                    <InputLabel value="Rating Produk" />
                    <div class="flex items-center gap-3 mt-1.5">
                        <StarRating v-model="form.rating" :interactive="true" size="xl" />
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                            {{ ratingLabels[form.rating] }}
                        </span>
                    </div>
                    <InputError :message="form.errors.rating" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="comment" value="Ulasan & Komentar (Opsional)" />
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 shadow-xs text-sm"
                        placeholder="Bagikan pengalaman Anda mengenai kualitas produk, kesesuaian deskripsi, atau kepuasan Anda..."
                    ></textarea>
                    <InputError :message="form.errors.comment" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Batal
            </SecondaryButton>
            <PrimaryButton
                class="ms-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="submitReview"
            >
                Kirim Ulasan
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
