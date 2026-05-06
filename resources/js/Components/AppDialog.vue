<script setup>
import { useFeedback } from "@/Composable/useFeedback";

const { state, closeDialog } = useFeedback();

const typeConfig = {
    success: { color: "text-green-500", icon: "✓" },
    error: { color: "text-red-500", icon: "✕" },
    warning: { color: "text-yellow-500", icon: "⚠" },
    info: { color: "text-blue-500", icon: "ℹ" },
};
</script>

<template>
    <div
        v-if="state.dialog.show"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        @click.self="
            state.dialog.mode === 'alert' ? closeDialog() : closeDialog(false)
        "
    >
        <div class="bg-white rounded-xl w-96 p-6 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <span
                    class="text-xl font-bold"
                    :class="typeConfig[state.dialog.type]?.color"
                >
                    {{ typeConfig[state.dialog.type]?.icon }}
                </span>
                <h2 class="text-lg font-semibold">{{ state.dialog.title }}</h2>
            </div>

            <p class="text-gray-600 mb-6">{{ state.dialog.message }}</p>

            <div class="flex justify-end gap-2">
                <template v-if="state.dialog.mode === 'confirm'">
                    <button
                        @click="closeDialog(false)"
                        class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200"
                    >
                        Batal
                    </button>
                    <button
                        @click="closeDialog(true)"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    >
                        Konfirmasi
                    </button>
                </template>

                <template v-else>
                    <button
                        @click="closeDialog()"
                        class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900"
                    >
                        OK
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
