import { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import { App } from "@/types";
import { useI18n } from "@/hooks/use-i18n";
import { toast } from "./use-toast";

type LoadingState = Record<number | string, boolean>;

export default function useCart() {
    const [isLoading, setIsLoading] = useState<LoadingState>({});
    const [addingToCart, setAddingToCart] = useState<LoadingState>({});
    const { t } = useI18n();

    /**
     * Add a product to the cart
     */
    const addToCart = async (
        productId: number,
        quantity: number = 1,
        variantId?: number
    ) => {
        setAddingToCart((prev) => ({ ...prev, [productId]: true }));

        try {
            await axios.post(route("cart.add"), {
                product_id: productId,
                product_variant_id: variantId,
                quantity,
            });

            // Show success toast or notification here if needed
            toast({
                title: t("added_to_cart", "Added to Cart"),
            });
            // Refresh to update cart state in the navbar/header
            // Alternative: use a global state manager to update the cart badge only
            router.reload({ only: ["cartInfo"] });
        } catch (error: any) {
            console.error("Error adding product to cart:", error);

            // Handle authentication error - redirect to login
            if (error.response?.status === 401) {
                toast({
                    title: t("login_required", "Login Required"),
                    description: t(
                        "login_to_add_to_cart",
                        "Please login to add items to cart"
                    ),
                    variant: "destructive",
                });
                router.visit(route("login"));
                return;
            }

            // Handle other errors
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                t(
                    "failed_to_add_to_cart",
                    "Failed to add item to cart. Please try again."
                );
            toast({
                title: t("error", "Error"),
                description: errorMessage,
                variant: "destructive",
            });
        } finally {
            setAddingToCart((prev) => ({ ...prev, [productId]: false }));
        }
    };

    /**
     * Update the quantity of a cart item
     */
    const updateCartItemQuantity = async (id: number, quantity: number) => {
        setIsLoading((prev) => ({ ...prev, [id]: true }));

        try {
            await axios.patch(route("cart.update", id), { quantity });
            router.reload();
        } catch (error: any) {
            console.error("Error updating cart item:", error);

            // Handle authentication error
            if (error.response?.status === 401) {
                toast({
                    title: t("login_required", "Login Required"),
                    description: t(
                        "login_to_continue",
                        "Please login to continue"
                    ),
                    variant: "destructive",
                });
                router.visit(route("login"));
                return;
            }

            // Handle other errors
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                t(
                    "failed_to_update_cart",
                    "Failed to update cart item. Please try again."
                );
            toast({
                title: t("error", "Error"),
                description: errorMessage,
                variant: "destructive",
            });
        } finally {
            setIsLoading((prev) => ({ ...prev, [id]: false }));
        }
    };

    /**
     * Remove an item from the cart
     */
    const removeCartItem = async (id: number) => {
        setIsLoading((prev) => ({ ...prev, [id]: true }));

        try {
            await axios.delete(route("cart.remove", id));
            router.reload();
        } catch (error: any) {
            console.error("Error removing cart item:", error);

            // Handle authentication error
            if (error.response?.status === 401) {
                toast({
                    title: t("login_required", "Login Required"),
                    description: t(
                        "login_to_continue",
                        "Please login to continue"
                    ),
                    variant: "destructive",
                });
                router.visit(route("login"));
                return;
            }

            // Handle other errors
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                t(
                    "failed_to_remove_from_cart",
                    "Failed to remove item from cart. Please try again."
                );
            toast({
                title: t("error", "Error"),
                description: errorMessage,
                variant: "destructive",
            });
        } finally {
            setIsLoading((prev) => ({ ...prev, [id]: false }));
        }
    };

    /**
     * Clear all items from the cart
     */
    const clearCart = async () => {
        try {
            await axios.delete(route("cart.clear"));
            router.reload();
        } catch (error: any) {
            console.error("Error clearing cart:", error);

            // Handle authentication error
            if (error.response?.status === 401) {
                toast({
                    title: t("login_required", "Login Required"),
                    description: t(
                        "login_to_continue",
                        "Please login to continue"
                    ),
                    variant: "destructive",
                });
                router.visit(route("login"));
                return;
            }

            // Handle other errors
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                t(
                    "failed_to_clear_cart",
                    "Failed to clear cart. Please try again."
                );
            toast({
                title: t("error", "Error"),
                description: errorMessage,
                variant: "destructive",
            });
        }
    };

    /**
     * Calculate the total price of a cart item
     */
    const calculateItemTotal = (item: App.Models.CartItem) => {
        const priceVariant = item.variant?.sale_price || item.variant?.price;
        const priceProduct = item.product.sale_price || item.product.price;
        const price = priceVariant || priceProduct;
        return (price * item.quantity).toFixed(2);
    };

    return {
        isLoading,
        addingToCart,
        addToCart,
        updateCartItemQuantity,
        removeCartItem,
        clearCart,
        calculateItemTotal,
    };
}
