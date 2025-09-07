import { Button } from "@/Components/ui/button";
import { CartItemList } from "@/Components/Cart/CartItemList";
import { OrderSummary } from "@/Components/Cart/OrderSummary";
import { useI18n } from "@/hooks/use-i18n";
import useCart from "@/hooks/use-cart";
import { App } from "@/types";
import { Head, router } from "@inertiajs/react";
import { ShoppingBag } from "lucide-react";
import EmptyState from "@/Components/ui/empty-state";
import { PageTitle } from "@/Components/ui/page-title";

interface CartIndexProps extends App.Interfaces.AppPageProps {
    cart: App.Models.Cart;
    cartSummary: App.Models.CartSummary;
}

export default function Index({ auth,  cart, cartSummary }: CartIndexProps) {
    const { t } = useI18n();
    const {
        isLoading,
        updateCartItemQuantity,
        removeCartItem,
        clearCart,
        calculateItemTotal,
    } = useCart();

    return (
        <>
            <Head title={t("cart", "Shopping Cart")} />

            <div className="flex flex-col gap-8">
                <PageTitle
                    title={t("cart", "Shopping Cart")}
                    icon={<ShoppingBag className="h-6 w-6 text-primary" />}
                />

                {/* Empty cart state */}
                {(!cart.items || cart.items.length === 0) && (
                    <EmptyState
                        icon={
                            <ShoppingBag
                                size={36}
                                className="text-muted-foreground"
                            />
                        }
                        title={t("your_cart_is_empty", "Your cart is empty")}
                        description={t(
                            "add_items_to_cart",
                            "Add items to your cart to see them here"
                        )}
                        action={
                            <Button onClick={() => router.visit(route("home"))}>
                                {t("continue_shopping", "Continue Shopping")}
                            </Button>
                        }
                    />
                )}

                {/* Cart with items */}
                {cart.items && cart.items.length > 0 && (
                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        <CartItemList
                            items={cart.items}
                            isLoading={isLoading}
                            updateCartItemQuantity={updateCartItemQuantity}
                            removeCartItem={removeCartItem}
                            calculateItemTotal={calculateItemTotal}
                            clearCart={clearCart}
                        />
                        <OrderSummary cartSummary={cartSummary} />
                    </div>
                )}
            </div>
        </>
    );
}
