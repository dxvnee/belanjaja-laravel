<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";

const container = ref(null);
const current = ref(0);

const scrollToIndex = (index) => {
    const el = container.value;
    const maxScrollLeft = el.scrollWidth - el.clientWidth;
    const step = maxScrollLeft / 4;

    el.scrollTo({
        left: step * index,
        behavior: "smooth",
    });
    current.value = index;
};

const buttonColor = (index) => {
    return index === current.value
        ? "bg-primary-600 h-3 w-3"
        : "bg-primary-300 dark:bg-primary-700 h-2 w-2";
};

const onMouseWheel = (event) => {
    const el = container.value;
    const maxScrollLeft = el.scrollWidth - el.clientWidth;

    el.scrollTo({
        left: el.scrollLeft + event.deltaY,
        behavior: "smooth",
    });

    current.value = Math.round((el.scrollLeft + event.deltaY) / (maxScrollLeft / 4));
    console.log(el.scrollLeft, event.deltaY, current.value);

};
</script>

<template>
    <AppLayout title="Belanjaja">
        <div class="py-12">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="h-[180px] sm:h-[220px] md:h-64">
                    <div
                        ref="container"
                        class="h-full w-full overflow-x-auto overflow-y-hidden no-scrollbar"
                        @wheel="onMouseWheel"
                    >
                        <div class="flex h-full w-max flex-nowrap gap-5 pe-4">
                            <div
                                v-for="i in 5"
                                :key="i"
                                class="h-full w-[82vw] shrink-0 sm:w-[62vw] md:w-[46vw] lg:w-[34vw]"
                            >
                                <img
                                    src="/images/iklan1.png"
                                    alt="iklan1"
                                    class="h-full w-full rounded-lg object-cover"
                                    draggable="false"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="m-2 flex items-center justify-center gap-1 sm:mt-3">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="flex justify-center items-center m-1"
                        >
                            <button
                                :class="buttonColor(i - 1) + ' rounded-xl'"
                                @click="scrollToIndex(i - 1)"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
