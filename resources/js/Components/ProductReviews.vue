<script setup>
import StarRating from "@/Components/StarRating.vue";

defineProps({
    reviews: {
        type: Array,
        default: () => [],
    },
    avgRating: {
        type: [Number, String],
        default: 0,
    },
    reviewsCount: {
        type: Number,
        default: 0,
    },
});

const formatReviewDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};
</script>

<template>
    <div class="mt-12 bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Ulasan Pembeli
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ulasan dan penilaian dari pembeli yang telah menerima pesanan.
                </p>
            </div>
            <div v-if="reviewsCount > 0" class="flex items-center gap-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 px-4 py-2.5 rounded-lg">
                <span class="text-3xl font-bold text-amber-500">
                    {{ Number(avgRating).toFixed(1) }}
                </span>
                <div>
                    <StarRating :rating="avgRating" size="sm" />
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ reviewsCount }} ulasan pembeli
                    </p>
                </div>
            </div>
        </div>

        <div v-if="reviews && reviews.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
                v-for="review in reviews"
                :key="review.id"
                class="py-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="review.user?.profile_photo_url"
                            :src="review.user.profile_photo_url"
                            :alt="review.user.name"
                            class="w-9 h-9 rounded-full object-cover"
                        />
                        <div
                            v-else
                            class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-sm"
                        >
                            {{ review.user?.name ? review.user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ review.user?.name ?? 'Pembeli' }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <StarRating :rating="review.rating" size="xs" />
                                <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatReviewDate(review.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="review.comment" class="mt-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line pl-12">
                    {{ review.comment }}
                </p>
            </div>
        </div>

        <div v-else class="py-10 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada ulasan untuk produk ini.
            </p>
        </div>
    </div>
</template>
