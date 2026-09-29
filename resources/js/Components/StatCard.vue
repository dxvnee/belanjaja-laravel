<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: 0,
    },
    subtext: {
        type: String,
        default: "",
    },
    highlight: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "simple", // 'simple' or 'dashboard'
    },
});
</script>

<template>
    <div
        v-if="variant === 'dashboard'"
        :class="[
            highlight
                ? 'border-amber-300 dark:border-amber-600/80 bg-amber-50/40 dark:bg-amber-950/20'
                : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800',
            'border p-5 rounded-xl shadow-xs transition text-left flex flex-col justify-between',
        ]"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ label }}
            </span>
            <div v-if="$slots.icon" class="shrink-0">
                <slot name="icon" />
            </div>
        </div>
        <div class="mt-3">
            <p
                :class="[
                    highlight ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-gray-100',
                    'text-2xl font-bold truncate',
                ]"
            >
                <slot>{{ value }}</slot>
            </p>
            <p v-if="subtext || $slots.subtext" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <slot name="subtext">{{ subtext }}</slot>
            </p>
        </div>
    </div>

    <div v-else class="bg-gray-50 dark:bg-gray-700/40 p-4 rounded-lg text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ label }}
        </p>
        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
            <slot>{{ value }}</slot>
        </p>
    </div>
</template>
