<script setup>
import { useFeedback } from "@/Composable/useFeedback";
import PrimaryButton from "./PrimaryButton.vue";
import Card from "./Card.vue";
import "../../css/dialog-animation.css";

const { state, closeDialog } = useFeedback();

const typeConfig = {
    success: { color: "text-green-500", icon: "✓" },
    error: { color: "text-red-500", icon: "✕" },
    warning: { color: "text-yellow-500", icon: "⚠" },
    info: { color: "text-blue-500", icon: "ℹ" },
};
</script>

<template>
    <Transition name = "dialog">
        <div
            v-if="state.dialog.show"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @click.self="
                state.dialog.mode === 'alert'
                    ? closeDialog()
                    : closeDialog(false)
            "
        >
            <Card class="dialog-card bg-white rounded-xl w-96 p-6 shadow-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span
                        class="text-xl font-bold"
                        :class="typeConfig[state.dialog.type]?.color"
                    >
                        {{ typeConfig[state.dialog.type]?.icon }}
                    </span>
                    <h2 class="text-lg font-semibold">
                        {{ state.dialog.title }}
                    </h2>
                </div>

                <p class="text-gray-600 mb-6">{{ state.dialog.message }}</p>

                <div class="flex justify-end gap-2">
                    <template v-if="state.dialog.mode === 'confirm'">
                        <PrimaryButton
                            @click="closeDialog(false)"
                            variant="secondary"
                        >
                            Batal
                        </PrimaryButton>
                        <PrimaryButton
                            @click="closeDialog(true)"
                            variant="primary"
                        >
                            Konfirmasi
                        </PrimaryButton>
                    </template>

                    <template v-else>
                        <PrimaryButton @click="closeDialog()" variant="primary">
                            OK
                        </PrimaryButton>
                    </template>
                </div>
            </Card>
        </div>
    </Transition>
</template>
