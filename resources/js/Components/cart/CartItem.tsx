import { Button } from "@/Components/ui/button";
import { Card } from "@/Components/ui/card";
import { Image } from "@/Components/ui/Image";
import { useI18n } from "@/hooks/use-i18n";
import { Trash2, MinusCircle, PlusCircle } from "lucide-react";
import { Badge } from "@/Components/ui/badge";
import { App } from "@/types";

interface CartItemProps {
    item: App.Models.CartItem;
    isLoading: Record<string, boolean>;
    updateCartItemQuantity: (id: number, quantity: number) => void;
    removeCartItem: (id: number) => void;
    calculateItemTotal: (item: App.Models.CartItem) => string;
}

export function CartItem({
    item,
    isLoading,
    updateCartItemQuantity,
    removeCartItem,
    calculateItemTotal,
}: CartItemProps) {
    const { t ,getLocalizedField } = useI18n();

    return (
        <Card className="overflow-hidden">
            <div className="p-3 sm:p-4">
                <div className="flex flex-row gap-3 sm:gap-4 items-start">
                    {/* Product Image */}
                    <div className="h-14 w-14 sm:h-24 sm:w-24 rounded-md overflow-hidden bg-muted flex-shrink-0">
                        <Image
                            src={item.variant?.featured_image || item.product.featured_image || "/placeholder.jpg"}
                            alt={item.product.name_en}
                            className="h-full w-full object-cover"
                            fallbackSrc="/placeholder.jpg"
                        />
                    </div>

                    {/* Product Details */}
                    <div className="flex-grow space-y-3 sm:space-y-4 w-full">
                        <div className="flex justify-between items-start gap-2">
                            <div>
                                <h4 className="font-medium line-clamp-2 text-sm sm:text-base">
                                    {getLocalizedField(item.product, 'name')}
                                </h4>

                                {/* Variant Details */}
                                {item.variant && (
                                    <div className="flex flex-wrap gap-1.5 mt-1.5">
                                        {item.variant.color && (
                                            <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                {t("color", "Color")}: {item.variant.color}
                                            </Badge>
                                        )}
                                        {item.variant.size && (
                                            <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                {t("size", "Size")}: {item.variant.size}
                                            </Badge>
                                        )}
                                        {item.variant.capacity && (
                                            <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                {t("capacity", "Capacity")}: {item.variant.capacity}
                                            </Badge>
                                        )}
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center ml-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    disabled={isLoading[item.id]}
                                    onClick={() => removeCartItem(item.id)}
                                    className="text-destructive hover:text-destructive/80 h-7 w-7 sm:h-8 sm:w-8"
                                >
                                    <Trash2 className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                </Button>
                            </div>
                        </div>

                        {/* Mobile view - price and total in the same row */}
                        <div className="flex flex-col gap-3 sm:hidden">
                            <div className="flex justify-between items-center">
                                {/* Price */}
                                <div>
                                    <div className="text-xs font-medium text-muted-foreground mb-0.5">
                                        {t("price", "Price")}
                                    </div>
                                    <div className="flex flex-col">
                                        {/* Show variant price if available, otherwise product price */}
                                        {(item.variant?.sale_price || item.product.sale_price) && (
                                            <span className="text-primary font-medium text-sm">
                                                EGP
                                                {Number(
                                                    item.variant?.sale_price || item.product.sale_price
                                                ).toFixed(2)}
                                            </span>
                                        )}
                                        <span
                                            className={
                                                (item.variant?.sale_price || item.product.sale_price)
                                                    ? "text-xs line-through text-muted-foreground"
                                                    : "text-primary font-medium text-sm"
                                            }
                                        >
                                            EGP
                                            {Number(item.variant?.price || item.product.price).toFixed(
                                                2
                                            )}
                                        </span>
                                    </div>
                                </div>

                                {/* Quantity for mobile */}
                                <div>
                                    <div className="text-xs font-medium text-muted-foreground mb-0.5">
                                        {t("quantity", "Quantity")}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            size="icon"
                                            variant="outline"
                                            className="h-7 w-7"
                                            disabled={
                                                isLoading[item.id] ||
                                                item.quantity <= 1
                                            }
                                            onClick={() =>
                                                updateCartItemQuantity(
                                                    item.id,
                                                    item.quantity - 1
                                                )
                                            }
                                        >
                                            <MinusCircle className="h-3.5 w-3.5" />
                                        </Button>
                                        <span className="w-7 text-center text-sm">
                                            {item.quantity}
                                        </span>
                                        <Button
                                            size="icon"
                                            variant="outline"
                                            className="h-7 w-7"
                                            disabled={isLoading[item.id]}
                                            onClick={() =>
                                                updateCartItemQuantity(
                                                    item.id,
                                                    item.quantity + 1
                                                )
                                            }
                                        >
                                            <PlusCircle className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            {/* Total for mobile */}
                            <div className="flex gap-2">
                                <div className="text-xs font-medium text-muted-foreground mb-0.5">
                                    {t("total", "Total")}
                                </div>
                                <div className="font-semibold text-sm">
                                    EGP {calculateItemTotal(item)}
                                </div>
                            </div>
                        </div>

                        {/* Desktop view */}
                        <div className="hidden sm:flex sm:flex-row sm:items-center justify-between gap-4">
                            {/* Price */}
                            <div>
                                <div className="text-sm font-medium text-muted-foreground mb-1">
                                    {t("price", "Price")}
                                </div>
                                <div className="flex flex-col">
                                    {(item.variant?.sale_price || item.product.sale_price) && (
                                        <span className="text-primary font-medium">
                                            EGP
                                            {Number(
                                                item.variant?.sale_price || item.product.sale_price
                                            ).toFixed(2)}
                                        </span>
                                    )}
                                    <span
                                        className={
                                            (item.variant?.sale_price || item.product.sale_price)
                                                ? "text-xs line-through text-muted-foreground"
                                                : "text-primary font-medium"
                                        }
                                    >
                                        EGP {Number(item.variant?.price || item.product.price).toFixed(2)}
                                    </span>
                                </div>
                            </div>

                            {/* Quantity */}
                            <div>
                                <div className="text-sm font-medium text-muted-foreground mb-1">
                                    {t("quantity", "Quantity")}
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        className="h-8 w-8"
                                        disabled={
                                            isLoading[item.id] ||
                                            item.quantity <= 1
                                        }
                                        onClick={() =>
                                            updateCartItemQuantity(
                                                item.id,
                                                item.quantity - 1
                                            )
                                        }
                                    >
                                        <MinusCircle className="h-4 w-4" />
                                    </Button>
                                    <span className="w-8 text-center">
                                        {item.quantity}
                                    </span>
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        className="h-8 w-8"
                                        disabled={isLoading[item.id]}
                                        onClick={() =>
                                            updateCartItemQuantity(
                                                item.id,
                                                item.quantity + 1
                                            )
                                        }
                                    >
                                        <PlusCircle className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            {/* Total */}
                            <div>
                                <div className="text-sm font-medium text-muted-foreground mb-1">
                                    {t("total", "Total")}
                                </div>
                                <div className="font-semibold">
                                    EGP {calculateItemTotal(item)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
