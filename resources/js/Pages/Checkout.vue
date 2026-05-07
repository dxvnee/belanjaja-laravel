<script>

</script>

<template>
    <div class="flex flex-col items-center justify-center gap-4 py-10">
        <h1 class="text-2xl font-bold">Halaman Beli</h1>
        <p class="text-gray-600">Ini adalah halaman untuk melakukan pembelian.</p>
        <button
            @click="$inertia.visit('/produk')"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
            Lihat Produk
        </button>
    </div>
</template>
