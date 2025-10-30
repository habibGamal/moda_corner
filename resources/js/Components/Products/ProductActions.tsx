import { Button } from "@/Components/ui/button";
import { useI18n } from "@/hooks/use-i18n";
import useCart from "@/hooks/use-cart";
import { App } from "@/types";
import { router } from "@inertiajs/react";
import { Heart, ShoppingBag } from "lucide-react";
import ReactPixel from "react-facebook-pixel";

interface ProductActionsProps {
    product: App.Models.Product;
    quantity: number;
    selectedVariant?: App.Models.ProductVariant;
}

export default function ProductActions({
    product,
    quantity,
    selectedVariant,
}: ProductActionsProps) {
    const { t } = useI18n();
    const { addToCart, addingToCart } = useCart();

    const handleAddToCart = () => {
        if (selectedVariant) {
            if (selectedVariant.quantity > 0) {
                // Add with variant ID
                addToCart(product.id, quantity, selectedVariant.id, product);
            }
        } else if (product.total_quantity && product.total_quantity > 0) {
            // Legacy fallback for products without variants
            addToCart(product.id, quantity, undefined, product);
        }
    };

    const isOutOfStock = selectedVariant
        ? selectedVariant.quantity <= 0
        : Boolean(product.total_quantity && product.total_quantity <= 0);

    const isInWishlist = product.is_in_wishlist;

    const addToWishlist = () => {
        router.post(
            route("wishlist.add"),
            { product_id: product.id },
            {
                preserveScroll: true,
                onSuccess: () => {
                    const variantId = selectedVariant?.id || product.id;
                    const value = selectedVariant?.sale_price || product?.sale_price;
                    ReactPixel.track("AddToWishlist", {
                        content_ids: [variantId],
                        contents: [{ id: variantId, quantity: 1 }],
                        value: value,
                        currency: "EGP",
                    });
                },
            }
        );
    };

    const removeFromWishlist = () => {
        router.delete(route("wishlist.remove", product.id), {
            preserveScroll: true,
        });
    };

    return (
        <div className="flex gap-3 flex-row">
            <Button
                onClick={handleAddToCart}
                disabled={addingToCart[product.id] || isOutOfStock}
                className="flex-1"
                size="lg"
            >
                <ShoppingBag className="w-5 h-5 mr-2" />
                {addingToCart[product.id]
                    ? t("adding_to_cart", "Adding to Cart...")
                    : isOutOfStock
                    ? t("out_of_stock", "Out of Stock")
                    : t("add_to_cart", "Add to Cart")}
            </Button>

            <Button
                variant="outline"
                size="lg"
                className="flex-shrink-0"
                onClick={isInWishlist ? removeFromWishlist : addToWishlist}
            >
                <Heart
                    className="w-5 h-5"
                    fill={isInWishlist ? "red" : "none"}
                    stroke={isInWishlist ? "red" : "currentColor"}
                />
                <span className="sr-only">
                    {t("add_to_wishlist", "Add to Wishlist")}
                </span>
            </Button>
        </div>
    );
}
