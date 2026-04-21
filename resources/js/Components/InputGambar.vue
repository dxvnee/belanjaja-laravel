<script setup>
import { computed, watch } from "vue";

const props = defineProps({
    modelValue: File,
});
const emit = defineEmits(["update:modelValue"]);

const handleFile = (e) => {
    const file = e.target.files[0];
    emit("update:modelValue", file);
};

const previewUrl = computed(() => {
    return props.modelValue
        ? window.URL.createObjectURL(props.modelValue)
        : null;
});
</script>

<template>
    <label
        :class="[
            'w-full h-48 border-2 border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-gray-400 transition-all duration-200 hover:scale-[1.01]',
            previewUrl ? '' : 'border-dashed'
        ]"
    >
        <input
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
</template>
