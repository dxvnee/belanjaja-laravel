<script>
export const statusLabelMap = {
    buyer: {
        pending: "Menunggu Pembayaran",
        paid: "Sudah Dibayar",
        shipped: "Dikirim",
        completed: "Selesai",
        cancelled: "Dibatalkan",
    },
    seller: {
        pending: "Menunggu Pembayaran",
        paid: "Perlu Dikirim",
        shipped: "Sedang Dikirim",
        completed: "Selesai",
        cancelled: "Dibatalkan",
    },
    default: {
        pending: "Menunggu Pembayaran",
        paid: "Sudah Dibayar",
        shipped: "Dikirim",
        completed: "Selesai",
        cancelled: "Dibatalkan",
    },
};

export const statusColorMap = {
    pending:
        "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 dark:border dark:border-yellow-700/50",
    paid:
        "bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 dark:border dark:border-blue-700/50",
    shipped:
        "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border dark:border-indigo-700/50",
    completed:
        "bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 dark:border dark:border-green-700/50",
    cancelled:
        "bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 dark:border dark:border-red-700/50",
};

export function getStatusLabel(status, variant = "default") {
    const map = statusLabelMap[variant] ?? statusLabelMap.default;
    return map[status] ?? status;
}

export function getStatusClass(status) {
    return (
        statusColorMap[status] ??
        "bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
    );
}
</script>

<script setup>
import { computed } from "vue";

const props = defineProps({
    status: {
        type: String,
        default: "",
    },
    statusLabel: {
        type: String,
        default: null,
    },
    statusColor: {
        type: String,
        default: null,
    },
    variant: {
        type: String,
        default: "default",
    },
});

const computedLabel = computed(() => {
    if (props.statusLabel) return props.statusLabel;
    return getStatusLabel(props.status, props.variant);
});

const computedClass = computed(() => {
    if (props.statusColor) return props.statusColor;
    return getStatusClass(props.status);
});
</script>

<template>
    <span
        class="text-xs px-2.5 py-1 rounded-md whitespace-nowrap inline-flex items-center font-medium"
        :class="computedClass"
    >
        <slot>{{ computedLabel }}</slot>
    </span>
</template>
