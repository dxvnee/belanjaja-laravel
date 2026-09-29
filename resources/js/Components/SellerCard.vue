<script setup>
import { Link } from "@inertiajs/vue3";
import Card from "@/Components/Card.vue";
import StatusSpan from "@/Components/StatusSpan.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    seller: {
        type: Object,
        required: true,
    },
});

const formatJoinedDate = (dateStr) => {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
};
</script>

<template>
    <Card
        class="mt-8 p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xs"
    >
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
            <Link
                :href="route('seller.show', seller.id)"
                class="flex items-center gap-4 group"
            >
                <img
                    v-if="seller.profile_photo_url"
                    :src="seller.profile_photo_url"
                    :alt="seller.name"
                    class="w-14 h-14 rounded-full object-cover border border-gray-200 dark:border-gray-700 group-hover:ring-2 group-hover:ring-primary-500 transition-all"
                />
                <div
                    v-else
                    class="w-14 h-14 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-xl group-hover:ring-2 group-hover:ring-primary-500 transition-all"
                >
                    {{
                        seller.name ? seller.name.charAt(0).toUpperCase() : "P"
                    }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3
                            class="font-bold text-base text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                        >
                            {{ seller.name }}
                        </h3>
                        <StatusSpan status="penjual" />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Bergabung sejak
                        {{ formatJoinedDate(seller.created_at) }}
                    </p>
                </div>
            </Link>

            <div
                class="flex items-center gap-6 border-t sm:border-t-0 sm:border-l border-gray-200 dark:border-gray-700 pt-3 sm:pt-0 sm:pl-6"
            >
                <div class="text-center sm:text-left">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total Produk
                    </p>
                    <p
                        class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-0.5"
                    >
                        {{ seller.products_count ?? 0 }} Produk
                    </p>
                </div>

                <Link :href="route('seller.show', seller.id)" class="ml-auto">
                    <PrimaryButton type="button">
                        Kunjungi Toko
                    </PrimaryButton>
                </Link>
            </div>
        </div>
    </Card>
</template>
