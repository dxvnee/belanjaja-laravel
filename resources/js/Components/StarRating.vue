<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    rating: {
        type: [Number, String],
        default: 0,
    },
    modelValue: {
        type: [Number, String],
        default: 0,
    },
    maxStars: {
        type: Number,
        default: 5,
    },
    interactive: {
        type: Boolean,
        default: false,
    },
    showScore: {
        type: Boolean,
        default: false,
    },
    count: {
        type: [Number, String],
        default: null,
    },
    size: {
        type: String,
        default: "sm", // xs, sm, md, lg, xl
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const hoverRating = ref(0);

const currentRating = computed(() => {
    if (props.interactive) {
        return Number(props.modelValue || 0);
    }
    return Number(props.rating || props.modelValue || 0);
});

const sizeClasses = {
    xs: "text-xs",
    sm: "text-sm",
    md: "text-base",
    lg: "text-xl",
    xl: "text-3xl",
};

const isStarActive = (star) => {
    if (props.interactive && hoverRating.value > 0) {
        return star <= hoverRating.value;
    }
    return star <= Math.round(currentRating.value);
};

const onStarClick = (star) => {
    if (!props.interactive) return;
    emit("update:modelValue", star);
    emit("change", star);
};

const onMouseEnter = (star) => {
    if (!props.interactive) return;
    hoverRating.value = star;
};

const onMouseLeave = () => {
    if (!props.interactive) return;
    hoverRating.value = 0;
};
</script>

<template>
    <div class="inline-flex items-center gap-1.5" :class="sizeClasses[size] || 'text-sm'">
        <div class="flex items-center" @mouseleave="onMouseLeave">
            <button
                v-for="star in maxStars"
                :key="star"
                :type="interactive ? 'button' : undefined"
                :disabled="!interactive"
                @click="onStarClick(star)"
                @mouseenter="onMouseEnter(star)"
                :class="[
                    interactive
                        ? 'cursor-pointer transition-transform hover:scale-125 focus:outline-none'
                        : 'cursor-default',
                    isStarActive(star)
                        ? 'text-amber-400'
                        : 'text-gray-300 dark:text-gray-600',
                ]"
            >
                ★
            </button>
        </div>

        <span
            v-if="showScore && currentRating > 0"
            class="font-semibold text-gray-900 dark:text-gray-100"
        >
            {{ currentRating.toFixed(1) }}
        </span>

        <span
            v-if="count !== null && count !== undefined"
            class="text-gray-400 dark:text-gray-500 font-normal"
        >
            ({{ count }})
        </span>
    </div>
</template>
