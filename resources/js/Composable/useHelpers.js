export function useHelpers() {
    const formatPrice = (price) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(price);
    };

    const getProductImage = (product) => {
        if (product.images && product.images.length > 0) {
            return `/storage/${product.images[0].image_path}`;
        }
        return "/images/placeholder-product.png";
    };

    return { formatPrice, getProductImage };
}
