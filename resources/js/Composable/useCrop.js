import Cropper from "cropperjs";
import "cropperjs/dist/cropper.css";
import { ref, nextTick, onUnmounted } from "vue";

export function useCrop(options = {}) {
    const {
        aspectRatio = 1,
        viewMode = 1,
        autoCropArea = 0.9,
        responsive = true,
        background = false,
        outputWidth = 800,
        outputHeight = 800,
        quality = 0.8,
        mimeType = "image/webp",
        fileName = "product.webp",
        ...extraCropperOptions
    } = options;

    const imageSrc = ref(null);
    const imgRef = ref(null);
    let cropper = null;

    const destroyCropper = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    };

    const cleanupImageUrl = () => {
        if (imageSrc.value && imageSrc.value.startsWith("blob:")) {
            URL.revokeObjectURL(imageSrc.value);
        }
        imageSrc.value = null;
    };

    const closeCrop = () => {
        destroyCropper();
        cleanupImageUrl();
    };

    const initCrop = (file) => {
        if (!file) return;

        closeCrop();

        imageSrc.value = URL.createObjectURL(file);

        nextTick(() => {
            destroyCropper();

            if (imgRef.value) {
                cropper = new Cropper(imgRef.value, {
                    aspectRatio,
                    viewMode,
                    autoCropArea,
                    responsive,
                    background,
                    ...extraCropperOptions,
                });
            }
        });
    };

    const applyCrop = (customOptions = {}) => {
        return new Promise((resolve, reject) => {
            if (!cropper) {
                reject(new Error("Cropper belum diinisialisasi"));
                return;
            }

            const width = customOptions.outputWidth ?? outputWidth;
            const height = customOptions.outputHeight ?? outputHeight;
            const targetMimeType = customOptions.mimeType ?? mimeType;
            const targetQuality = customOptions.quality ?? quality;
            const targetFileName = customOptions.fileName ?? fileName;

            const canvas = cropper.getCroppedCanvas({
                width,
                height,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: "high",
            });

            if (!canvas) {
                reject(new Error("Gagal mengambil canvas dari cropper"));
                return;
            }

            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        reject(new Error("Gagal mengoptimasi gambar menjadi blob"));
                        return;
                    }

                    const optimizedFile = new File([blob], targetFileName, {
                        type: targetMimeType,
                    });

                    closeCrop();
                    resolve(optimizedFile);
                },
                targetMimeType,
                targetQuality,
            );
        });
    };

    const rotate = (degree = 90) => {
        if (cropper) {
            cropper.rotate(degree);
        }
    };

    const zoom = (ratio = 0.1) => {
        if (cropper) {
            cropper.zoom(ratio);
        }
    };

    const reset = () => {
        if (cropper) {
            cropper.reset();
        }
    };

    onUnmounted(() => {
        closeCrop();
    });

    return {
        imageSrc,
        imgRef,
        initCrop,
        applyCrop,
        closeCrop,
        rotate,
        zoom,
        reset,
        getCropper: () => cropper,
    };
}
