<script setup>
import { computed, ref } from "vue";
import Card from "./Card.vue";
import Modal from "./Modal.vue";
import PrimaryButton from "./PrimaryButton.vue";
import { useCrop } from "@/Composable/useCrop";

const props = defineProps({
    modelValue: [File, Object, null],
    existingImage: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue"]);

const imageInput = ref(null);

const {
    imageSrc,
    imgRef,
    initCrop,
    applyCrop,
    closeCrop,
} = useCrop({
    aspectRatio: 1,
    outputWidth: 800,
    outputHeight: 800,
    quality: 0.8,
    fileName: "product.webp",
});

const handleFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    initCrop(file);
};

const handleApplyCrop = async () => {
    try {
        const optimizedFile = await applyCrop();
        if (optimizedFile) {
            emit("update:modelValue", optimizedFile);
        }
    } catch (error) {
        console.error("Gagal melakukan crop gambar:", error);
    } finally {
        if (imageInput.value) {
            imageInput.value.value = "";
        }
    }
};

const handleClose = () => {
    closeCrop();
    if (imageInput.value) {
        imageInput.value.value = "";
    }
};

const previewUrl = computed(() => {
    if (props.modelValue instanceof File) {
        return window.URL.createObjectURL(props.modelValue);
    }
    return props.existingImage || null;
});
</script>

<template>
    <label
        :class="[
            'w-full h-48 border-2 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center cursor-pointer hover:border-gray-400 dark:hover:border-gray-500 transition-all duration-200 hover:scale-[1.01]',
            previewUrl ? '' : 'border-dashed',
        ]"
    >
        <input
            ref="imageInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFile"
        />
        <img
            v-if="previewUrl"
            :src="previewUrl"
            alt="preview"
            class="h-full w-full object-cover rounded-lg"
        />
        <span v-else class="text-5xl text-gray-400 leading-none select-none"
            >+</span
        >
    </label>

    <Teleport to="body">
        <Modal :show="Boolean(imageSrc)">
            <Card>
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center"
                >
                    <h3
                        class="font-semibold text-lg text-gray-900 dark:text-gray-100"
                    >
                        Sesuaikan Potongan Foto Produk
                    </h3>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg"
                        @click.prevent.stop="handleClose"
                    >
                        ✕
                    </button>
                </div>

                <div
                    class="w-full h-[65vh] sm:h-[72vh] bg-black flex items-center justify-center overflow-hidden"
                >
                    <img
                        ref="imgRef"
                        :src="imageSrc"
                        alt="Crop target"
                        class="block max-w-full"
                    />
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-3 bg-gray-50 dark:bg-gray-900/50"
                >
                    <PrimaryButton
                        type="button"
                        variant="secondary"
                        @click.prevent.stop="handleClose"
                    >
                        Batal
                    </PrimaryButton>
                    <PrimaryButton
                        type="button"
                        @click.prevent.stop="handleApplyCrop"
                    >
                        Potong & Terapkan
                    </PrimaryButton>
                </div>
            </Card>
        </Modal>
    </Teleport>
</template>
