<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Card from "./Card.vue";
import PrimaryButton from "./PrimaryButton.vue";
import IconButton from "./IconButton.vue";
import TextInput from "./TextInput.vue";
import InputLabel from "./InputLabel.vue";
import InputError from "./InputError.vue";
import { useFeedback } from "@/Composable/useFeedback.js";

const { confirm, showSuccess, showError, showLoading, hideLoading } =
    useFeedback();

const props = defineProps({
    address: {
        type: Object,
        default: null,
    },
    onDismiss: {
        type: Function,
        default: () => {},
    },
});

const isEdit = computed(() => !!props.address);

const form = useForm({
    name: props.address?.name || "",
    province: props.address?.province || "",
    city: props.address?.city || "",
    subdistrict: props.address?.subdistrict || "",
    postal_code: props.address?.postal_code || "",
    detail: props.address?.detail || "",
    phone: props.address?.phone || "",
    user_id: props.address?.user_id || "",
});

const submit = async () => {
    const title = isEdit.value ? "Ubah Alamat" : "Tambah Alamat";
    const msg = isEdit.value
        ? "Apakah anda yakin ingin mengubah alamat ini?"
        : "Apakah anda yakin ingin menambah alamat?";

    const confirmed = await confirm(title, msg);
    if (!confirmed) return;

    showLoading("Menyimpan perubahan...");

    if (isEdit.value) {
        form.put(route("address.update", props.address.id), {
            preserveScroll: true,
            onError: () => {
                showError("Gagal", "Gagal memperbarui alamat!");
            },
            onSuccess: () => {
                showSuccess("Berhasil!", "Alamat berhasil diperbarui!");
                props.onDismiss();
            },
            onFinish: () => {
                hideLoading();
            },
        });
    } else {
        form.post(route("address.store"), {
            preserveScroll: true,
            onError: () => {
                showError("Gagal", "Gagal menyimpan alamat!");
            },
            onSuccess: () => {
                showSuccess("Berhasil!", "Alamat berhasil ditambah!");
                props.onDismiss();
            },
            onFinish: () => {
                hideLoading();
            },
        });
    }
};
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-xs bg-black/50"
    >
        <Card class="w-[700px] p-5">
            <div class="flex flex-col justify-between items-center gap-4">
                <div
                    class="flex flex-row w-full justify-start items-center gap-2"
                >
                    <IconButton icons="back" @click="onDismiss" />
                    <p class="text-xl font-bold">
                        {{ isEdit ? "Ubah Alamat" : "Tambah Alamat" }}
                    </p>
                </div>

                <form
                    @submit.prevent="submit"
                    class="w-full flex flex-col gap-4"
                >
                    <!-- Row 1: Nama Penerima & Nomor Telepon -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Nama Penerima" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="off"
                                placeholder="Nama Penerima"
                                icon="user"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>
                        <div>
                            <InputLabel for="phone" value="Nomor Telepon" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Nomor Telepon"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.phone"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Provinsi & Kota / Kabupaten -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="province" value="Provinsi" />
                            <TextInput
                                id="province"
                                v-model="form.province"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Provinsi"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.province"
                            />
                        </div>
                        <div>
                            <InputLabel for="city" value="Kota / Kabupaten" />
                            <TextInput
                                id="city"
                                v-model="form.city"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Kota / Kabupaten"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.city"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Kecamatan & Kode Pos -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="subdistrict" value="Kecamatan" />
                            <TextInput
                                id="subdistrict"
                                v-model="form.subdistrict"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Kecamatan"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.subdistrict"
                            />
                        </div>
                        <div>
                            <InputLabel for="postal_code" value="Kode Pos" />
                            <TextInput
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autocomplete="off"
                                placeholder="Kode Pos"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.postal_code"
                            />
                        </div>
                    </div>

                    <!-- Row 4: Alamat Lengkap -->
                    <div>
                        <InputLabel
                            for="detail"
                            value="Alamat Lengkap (RT/RW, No. Rumah, Blok, dll)"
                        />
                        <TextInput
                            id="detail"
                            v-model="form.detail"
                            type="textarea"
                            class="mt-1 block w-full"
                            required
                            rows="3"
                            autocomplete="off"
                            placeholder="Alamat Lengkap"
                            icon="deskripsi"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.detail"
                        />
                    </div>

                    <div class="flex flex-row justify-end w-full gap-2 mt-4">
                        <PrimaryButton
                            type="button"
                            variant="secondary"
                            @click="onDismiss"
                        >
                            Batal
                        </PrimaryButton>
                        <PrimaryButton type="submit" :loading="form.processing">
                            {{ isEdit ? "Simpan Perubahan" : "Tambah Alamat" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Card>
    </div>
</template>
