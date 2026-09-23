"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.useHelpers = useHelpers;

function useHelpers() {
  var formatPrice = function formatPrice(price) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0
    }).format(price);
  };

  var getProductImage = function getProductImage(product) {
    if (product.images && product.images.length > 0) {
      return "/storage/".concat(product.images[0].image_path);
    }

    return "/images/placeholder-product.png";
  };

  return {
    formatPrice: formatPrice,
    getProductImage: getProductImage
  };
}