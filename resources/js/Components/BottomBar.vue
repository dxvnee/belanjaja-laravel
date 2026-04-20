<script setup>
import { Link } from "@inertiajs/vue3";

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="mt-10 border-t border-slate-200 bg-white/90 backdrop-blur-sm">
        <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-12">
                <div class="md:col-span-5">
                    <h3 class="text-2xl font-black tracking-tight text-primary-600">
                        Belanjaja!
                    </h3>
                    <p class="mt-3 max-w-md text-sm leading-6 text-slate-600">
                        Platform jual beli yang cepat, aman, dan nyaman untuk kamu yang
                        ingin menjual maupun mencari barang terbaik setiap hari.
                    </p>
                </div>

                <div class="md:col-span-3">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-900">
                        Navigasi
                    </h4>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li>
                            <Link :href="route('dashboard')" class="hover:text-primary-600">
                                Beranda
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('jual.index')" class="hover:text-primary-600">
                                Jual Barang
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('profile.show')" class="hover:text-primary-600">
                                Profil
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-900">
                        Dukungan
                    </h4>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li>Email: sup  port@belanjaja.id</li>
                        <li>Jam operasional: 08.00 - 21.00 WIB</li>
                        <li>Lokasi: Indonesia</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2 border-t border-slate-200 pt-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>Copyright {{ currentYear }} Belanjaja. All rights reserved.</p>
                <p>Dibuat untuk pengalaman jual beli yang lebih baik.</p>
            </div>
        </div>
    </footer>
</template>
