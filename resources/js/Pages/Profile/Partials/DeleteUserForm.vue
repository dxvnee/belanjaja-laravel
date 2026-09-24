<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            Hapus Akun
        </template>

        <template #description>
            Hapus akun Anda secara permanen beserta semua data terkait.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                Setelah akun Anda dihapus, seluruh sumber daya dan data yang terkait akan dihapus secara permanen. Sebelum menghapus akun, pastikan Anda telah mengunduh informasi penting yang ingin Anda simpan.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Hapus Akun Saya
                </DangerButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    Konfirmasi Hapus Akun
                </template>

                <template #content>
                    Apakah Anda yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan. Seluruh data transaksi, pesanan, dan profil Anda akan terhapus secara permanen.
                    
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Silakan masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun:
                    </p>

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full sm:w-3/4"
                            placeholder="Kata sandi akun Anda"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Hapus Akun Sekarang
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
