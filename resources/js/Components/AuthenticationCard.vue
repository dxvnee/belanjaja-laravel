<template>
    <div
        class="min-h-screen flex flex-col justify-center items-center p-5 bg-primary-50"
    >
        <div class="flex flex-row items-center">
            <slot name="logo" />
            <div class="flex flex-col gap-1">
                <h1 class="text-5xl font-black text-primary-600">Belanjaja!</h1>
                <h1 class="text-md text-black">Beli semua dengan mudah :)</h1>
            </div>
        </div>

        <div
            class="w-full max-w-md mt-2 px-6 py-4 bg-white dark:bg-gray-800 overflow-hidden rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
