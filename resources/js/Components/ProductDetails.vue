<script setup>
import Card from "@/Components/Card.vue";
import InputGambar from "@/Components/InputGambar.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
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
</script>

<template>
    <div class="flex w-full justify-center">
        <Card class="mx-auto w-full !max-w-none p-5">
            <p class="text-lg font-medium text-black">Unggah Foto Iklan</p>
            <p class="text-sm text-black">
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
                            :existingImage="
                                form.images?.[slot - 1]?.image_path
                                    ? '/storage/' +
                                      form.images[slot - 1].image_path
                                    : null
                            "
                        />
                        <InputError
                            class="mt-1"
                            :message="form.errors[`photo${slot}`]"
                        />

                        <p class="text-sm text-black text-center">
                            Foto {{ slot }}
                        </p>
                    </div>
                </div>

                <p class="text-lg font-medium text-black">
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
                        <InputLabel for="kategori" value="Kategori" />
                        <SelectInput
                            id="kategori"
                            v-model="form.kategori"
                            class="mt-1 block w-full"
                            required
                            autocomplete="off"
                            placeholder="Pilih Kategori"
                            :option="[
                                { value: 1, label: 'Elektronik' },
                                { value: 2, label: 'Fashion' },
                                { value: 3, label: 'Rumah Tangga' },
                                { value: 4, label: 'Hobi' },
                                { value: 5, label: 'Kendaraan' },
                                { value: 6, label: 'Lainnya' },
                            ]"
                            icon="kategori"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.deskripsi"
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
