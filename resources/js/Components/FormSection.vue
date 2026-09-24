<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="$emit('submitted')">
                <div class="border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 rounded-lg shadow-xs overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <slot name="form" />
                        </div>
                    </div>

                    <div v-if="hasActions" class="flex items-center justify-end px-5 py-3 sm:px-6 bg-gray-50/75 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700/80">
                        <slot name="actions" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
