<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
}

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Autentikasi Dua Faktor
        </template>

        <template #description>
            Tambahkan lapisan keamanan ekstra pada akun Anda menggunakan autentikasi dua faktor (2FA).
        </template>

        <template #content>
            <h3 v-if="twoFactorEnabled && ! confirming" class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="inline-block size-2 rounded-full bg-green-500"></span>
                Autentikasi dua faktor telah aktif.
            </h3>

            <h3 v-else-if="twoFactorEnabled && confirming" class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="inline-block size-2 rounded-full bg-yellow-500"></span>
                Selesaikan pengaktifan autentikasi dua faktor.
            </h3>

            <h3 v-else class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="inline-block size-2 rounded-full bg-gray-400"></span>
                Autentikasi dua faktor belum aktif.
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                <p>
                    Saat autentikasi dua faktor diaktifkan, Anda akan diminta memasukkan token acak yang aman saat proses login. Token ini dapat diperoleh melalui aplikasi Google Authenticator di perangkat seluler Anda.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        <p v-if="confirming" class="font-medium text-gray-800 dark:text-gray-200">
                            Untuk menyelesaikan pengaktifan, pindai kode QR berikut menggunakan aplikasi autentikator Anda atau masukkan kunci pengaturan lalu masukkan kode OTP yang dihasilkan.
                        </p>

                        <p v-else class="font-medium text-gray-800 dark:text-gray-200">
                            Autentikasi dua faktor kini aktif. Pindai kode QR berikut menggunakan aplikasi autentikator Anda atau masukkan kunci pengaturan.
                        </p>
                    </div>

                    <div class="mt-4 p-3 inline-block bg-white rounded-lg border border-gray-200 shadow-xs" v-html="qrCode" />

                    <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-medium">
                            Kunci Pengaturan (Setup Key): <span class="font-mono font-bold text-gray-900 dark:text-gray-100 select-all" v-html="setupKey"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4">
                        <InputLabel for="code" value="Kode OTP" />

                        <TextInput
                            id="code"
                            v-model="confirmationForm.code"
                            type="text"
                            name="code"
                            class="block mt-1 w-full sm:w-1/2"
                            inputmode="numeric"
                            autofocus
                            placeholder="6 digit angka"
                            autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication"
                        />

                        <InputError :message="confirmationForm.errors.code" class="mt-2" />
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && ! confirming">
                    <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            Simpan kode pemulihan berikut di tempat yang aman (misalnya pengelola kata sandi). Kode ini dapat digunakan untuk mengakses kembali akun Anda jika perangkat autentikasi Anda hilang.
                        </p>
                    </div>

                    <div class="grid gap-1.5 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-50 dark:bg-gray-900/60 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <div v-if="! twoFactorEnabled">
                    <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                        <PrimaryButton type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                            Aktifkan 2FA
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>

                <div v-else class="flex flex-wrap items-center gap-3">
                    <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                        <PrimaryButton
                            v-if="confirming"
                            type="button"
                            :class="{ 'opacity-25': enabling || confirmationForm.processing }"
                            :disabled="enabling || confirmationForm.processing"
                        >
                            Konfirmasi Kode
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <SecondaryButton
                            v-if="recoveryCodes.length > 0 && ! confirming"
                        >
                            Buat Ulang Kode Pemulihan
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <SecondaryButton
                            v-if="recoveryCodes.length === 0 && ! confirming"
                        >
                            Lihat Kode Pemulihan
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <SecondaryButton
                            v-if="confirming"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            Batal
                        </SecondaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <DangerButton
                            v-if="! confirming"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            Nonaktifkan 2FA
                        </DangerButton>
                    </ConfirmsPassword>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
