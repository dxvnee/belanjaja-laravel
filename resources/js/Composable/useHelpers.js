export function useHelpers() {
    const formatPrice = (price) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(price);
    };

    const getProductImage = (product) => {
        if (!product) return "/images/placeholder-product.svg";

        let path = null;
        if (typeof product === "string") {
            path = product;
        } else if (product.images && product.images.length > 0 && product.images[0]?.image_path) {
            path = product.images[0].image_path;
        }

        if (path) {
            if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("data:")) {
                return path;
            }
            if (path.startsWith("/storage/")) {
                return path;
            }
            if (path.startsWith("storage/")) {
                return `/${path}`;
            }
            const cleanPath = path.startsWith("/") ? path.substring(1) : path;
            return `/storage/${cleanPath}`;
        }

        return "/images/placeholder-product.svg";
    };

    return { formatPrice, getProductImage };
}
