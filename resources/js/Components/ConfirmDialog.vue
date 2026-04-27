<script setup>
import { useConfirm } from "../Composable/UseConfirm";

const { show, options, confirmAction, cancelAction } = useConfirm();
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        @click.self="cancelAction"
    >
        <div class="bg-white rounded-xl w-96 p-6 shadow-lg">
            <h2 class="text-lg font-semibold mb-2">
                {{ options.title }}
            </h2>

            <p class="text-gray-600 mb-6">
                {{ options.message }}
            </p>

            <div class="flex justify-end gap-2">
                <button
                    @click="cancelAction"
                    class="px-4 py-2 bg-gray-100 rounded"
                >
                    Batal
                </button>

                <button
                    @click="confirmAction"
                    class="px-4 py-2 bg-red-500 text-white rounded"
                >
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</template>
