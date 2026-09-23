import { ref } from "vue";

export function useMidtrans() {
    const isSnapLoaded = ref(typeof window !== "undefined" && !!window.snap);
    const isProcessing = ref(false);

    const loadSnapScript = (clientKey, isProduction = false) => {
        return new Promise((resolve, reject) => {
            if (typeof window === "undefined") {
                return reject(new Error("Window is undefined"));
            }

            if (window.snap) {
                isSnapLoaded.value = true;
                return resolve(window.snap);
            }

            const existingScript = document.querySelector(
                'script[src*="midtrans.com/snap/snap.js"]'
            );
            if (existingScript) {
                existingScript.addEventListener("load", () => {
                    isSnapLoaded.value = true;
                    resolve(window.snap);
                });
                return;
            }

            const script = document.createElement("script");
            const snapUrl = isProduction
                ? "https://app.midtrans.com/snap/snap.js"
                : "https://app.sandbox.midtrans.com/snap/snap.js";

            script.src = snapUrl;
            if (clientKey) {
                script.setAttribute("data-client-key", clientKey);
            }

            script.onload = () => {
                isSnapLoaded.value = true;
                resolve(window.snap);
            };

            script.onerror = () => {
                reject(new Error("Gagal memuat Midtrans Snap SDK"));
            };

            document.head.appendChild(script);
        });
    };

    /**
     * Memunculkan Snap Popup Pembayaran
     * @param {string} snapToken Token transaksi dari Midtrans backend
     * @param {Object} options Callback onSuccess, onPending, onError, onClose
     * @returns {Promise<any>}
     */
    const pay = (snapToken, options = {}) => {
        return new Promise((resolve, reject) => {
            if (!window.snap) {
                return reject(new Error("Midtrans Snap belum siap."));
            }

            isProcessing.value = true;

            window.snap.pay(snapToken, {
                onSuccess: (result) => {
                    isProcessing.value = false;
                    options.onSuccess?.(result);
                    resolve({ status: "success", result });
                },
                onPending: (result) => {
                    isProcessing.value = false;
                    options.onPending?.(result);
                    resolve({ status: "pending", result });
                },
                onError: (error) => {
                    isProcessing.value = false;
                    options.onError?.(error);
                    reject(error);
                },
                onClose: () => {
                    isProcessing.value = false;
                    options.onClose?.();
                    resolve({ status: "closed" });
                },
            });
        });
    };

    const embed = (snapToken, embedId, options = {}) => {
        if (!window.snap) {
            console.error("Midtrans Snap belum siap.");
            return;
        }

        window.snap.embed(snapToken, {
            embedId: embedId,
            onSuccess: (result) => {
                options.onSuccess?.(result);
            },
            onPending: (result) => {
                options.onPending?.(result);
            },
            onError: (error) => {
                options.onError?.(error);
            },
            onClose: () => {
                options.onClose?.();
            },
        });
    };

    return {
        isSnapLoaded,
        isProcessing,
        loadSnapScript,
        pay,
        embed,
    };
}
