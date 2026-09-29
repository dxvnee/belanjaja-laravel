<script setup>
import Card from "@/Components/Card.vue";
import InputGambar from "@/Components/InputGambar.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    submit: {
        type: Function,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    photoSlots: {
        type: Array,
        default: () => [1, 2, 3],
    },
    submitLabel: {
        type: String,
        default: "Jual",
    },
});

const availableCategories = [
    { id: 1, name: "Elektronik" },
    { id: 2, name: "Fashion" },
    { id: 3, name: "Rumah Tangga" },
    { id: 4, name: "Hobi" },
    { id: 5, name: "Kendaraan" },
    { id: 6, name: "Lainnya" },
];

if (!Array.isArray(props.form.kategori_ids)) {
    props.form.kategori_ids = props.form.kategori ? [Number(props.form.kategori)] : [1];
}

const toggleCategory = (id) => {
    if (!Array.isArray(props.form.kategori_ids)) {
        props.form.kategori_ids = [Number(props.form.kategori) || 1];
    }
    const idx = props.form.kategori_ids.indexOf(id);
    if (idx > -1) {
        if (props.form.kategori_ids.length > 1) {
            props.form.kategori_ids.splice(idx, 1);
        }
    } else {
        if (props.form.kategori_ids.length < 3) {
            props.form.kategori_ids.push(id);
        }
    }
    props.form.kategori = props.form.kategori_ids[0] || null;
};
</script>

<template>
    <div class="flex w-full justify-center">
        <Card class="mx-auto w-full !max-w-none p-5">
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Unggah Foto Iklan</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Foto Anda akan menjadi foto sampul/thumbnail
            </p>

            <form @submit.prevent="submit">
                <div
                    class="grid grid-cols-1 gap-4 pb-5 pt-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="slot in photoSlots"
                        :key="slot"
                        class="flex flex-col gap-2"
                    >
                        <InputGambar
                            v-model="form['photo' + slot]"
                            :existingImage="form.images?.[slot - 1]?.image_path ? '/storage/' + form.images[slot - 1].image_path : null"
                        />
                        <InputError
                            class="mt-1"
                            :message="form.errors[`photo${slot}`]"
                        />

                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                            Foto {{ slot }}
                        </p>
                    </div>
                </div>

                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Berikan Detail Item Anda
                </p>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="flex-1">
                            <InputLabel for="judul" value="Judul Iklan" />
                            <TextInput
                                id="judul"
                                v-model="form.judul"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="off"
                                placeholder="Judul Iklan"
                                icon="iklan"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.judul"
                            />
                        </div>

                        <div class="flex-1">
                            <InputLabel for="harga" value="Harga" />
                            <TextInput
                                id="harga"
                                v-model="form.harga"
                                type="number"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Harga"
                                icon="harga"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.harga"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <InputLabel for="stok" value="Stok" />
                            <TextInput
                                id="stok"
                                v-model="form.stok"
                                type="number"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="off"
                                placeholder="Stok"
                                icon="stock"
                                min="0"
                                max="99998"
                                @keydown="
                                    (e) =>
                                        ['e', 'E', '+', '-', '.'].includes(e.key) &&
                                        e.preventDefault()
                                "
                            />
                            <InputError class="mt-2" :message="form.errors.stok" />
                        </div>

                        <div>
                            <InputLabel for="lokasi" value="Lokasi / Asal Pengiriman" />
                            <TextInput
                                id="lokasi"
                                v-model="form.lokasi"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                                placeholder="Contoh: Jakarta Selatan, Bandung..."
                                icon="location"
                            />
                            <InputError class="mt-2" :message="form.errors.lokasi" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <InputLabel for="kategori" value="Kategori Produk" />
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                Pilih 1 - 3 kategori (Terpilih: {{ form.kategori_ids?.length || 1 }}/3)
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="cat in availableCategories"
                                :key="cat.id"
                                type="button"
                                @click="toggleCategory(cat.id)"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold border transition cursor-pointer',
                                    (form.kategori_ids?.includes(cat.id) || (!form.kategori_ids && form.kategori == cat.id))
                                        ? 'bg-primary-600 text-white border-primary-600 shadow-xs ring-2 ring-primary-500/20'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500'
                                ]"
                            >
                                <svg
                                    v-if="form.kategori_ids?.includes(cat.id) || (!form.kategori_ids && form.kategori == cat.id)"
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ cat.name }}</span>
                            </button>
                        </div>
                        <p v-if="form.kategori_ids?.length >= 3" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1.5">
                            Maksimal 3 kategori telah tercapai. Klik kategori terpilih untuk membatalkan sebelum memilih yang lain.
                        </p>
                        <InputError
                            class="mt-2"
                            :message="form.errors.kategori || form.errors.kategori_ids"
                        />
                    </div>
                    <div>
                        <InputLabel for="deskripsi" value="Deskripsi" />
                        <TextInput
                            id="deskripsi"
                            v-model="form.deskripsi"
                            type="textarea"
                            class="mt-1 block w-full h-64"
                            required
                            autocomplete="off"
                            placeholder="Deskripsi"
                            icon="deskripsi"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.deskripsi"
                        />
                    </div>

                    <div class="flex justify-end pt-2">
                        <PrimaryButton
                            class="w-full sm:w-24"
                            :class="{
                                'opacity-25': form.processing,
                            }"
                            :disabled="form.processing"
                            type="submit"
                        >
                            {{ submitLabel }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </Card>
    </div>
</template>
