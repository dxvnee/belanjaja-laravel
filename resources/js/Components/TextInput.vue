<script setup>
import { computed, onMounted, ref, useAttrs } from "vue";
import { Icons } from "@/Icons";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/20/solid";

const attrs = useAttrs();
const props = defineProps({
    modelValue: String,
    icon: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "text",
    },
    iconClick: {
        type: Function,
        default: null,
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);
const showPassword = ref(false);
const isTextarea = computed(() => props.type === "textarea");

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) {
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
            :class="[
                'h-5 w-5 text-gray-400 absolute left-3 z-10',
                isTextarea ? 'top-3' : 'top-1/2 -translate-y-1/2',
                iconClick ? 'cursor-pointer' : 'pointer-events-none',
            ]"
            @click="iconClick ? iconClick() : null"
        />
        <textarea
            v-if="isTextarea"
            ref="input"
            v-bind="attrs"
            :class="[
                'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-300 dark:focus:border-primary-300 focus:ring-primary-300 dark:focus:ring-primary-300 rounded-md shadow-sm',
                icon ? 'pl-10' : 'pl-5',
                'pt-3',
            ]"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <input
            v-else
            ref="input"
            v-bind="attrs"
            :class="[
                'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-300 dark:focus:border-primary-300 focus:ring-primary-300 dark:focus:ring-primary-300 rounded-md shadow-sm',
                icon ? 'pl-10' : 'pl-5',
            ]"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :type="type === 'password' ? (showPassword ? 'text' : 'password') : type"
        />
        <component
            v-if="!isTextarea && type === 'password'"
            :is="showPassword ? EyeSlashIcon : EyeIcon"
            @click="togglePassword"
            class="h-5 w-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer"
        />
    </div>
</template>
