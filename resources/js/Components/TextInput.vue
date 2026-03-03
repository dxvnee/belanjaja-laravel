<script setup>
import { onMounted, ref, useAttrs } from "vue";
import { Icons } from "@/Icons";

const attrs = useAttrs();
defineProps({
    modelValue: String,
    icon: {
        type: String,
        default: null,
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative">
        <component
            v-if="icon && Icons[icon]"
            :is="Icons[icon]"
            class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
        />
        <input
            ref="input"
            v-bind="attrs"
            :class="[
                'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-300 dark:focus:border-primary-300 focus:ring-primary-300 dark:focus:ring-primary-300 rounded-md shadow-sm',
                icon ? 'pl-10' : 'pl-5'
            ]"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>
