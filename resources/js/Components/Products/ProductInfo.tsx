import { Badge } from "@/Components/ui/badge";
import { useI18n } from "@/hooks/use-i18n";
import { Link } from "@inertiajs/react";
import { AlertCircle, Check } from "lucide-react";
import { App } from "@/types";

interface ProductInfoProps {
    product: App.Models.Product;
    selectedVariant?: App.Models.ProductVariant;
}

export default function ProductInfo({ product, selectedVariant }: ProductInfoProps) {
    const { getLocalizedField, t } = useI18n();

    // Use variant pricing if available, otherwise fall back to product pricing
    const currentPrice = selectedVariant?.price ?? product.price;
    const currentSalePrice = selectedVariant?.sale_price ?? product.sale_price;

    // Check stock availability - use variant stock if variant is selected
    const isInStock = selectedVariant
        ? selectedVariant.quantity > 0
        : product.isInStock;

    const hasDiscount = currentSalePrice && currentSalePrice !== currentPrice;
    const discountPercentage = hasDiscount
        ? Math.round(((currentPrice - currentSalePrice!) / currentPrice) * 100)
        : 0;
    return (
        <div className="flex flex-col">
            {/* Brand */}
            {product.brand && (
                <Link
                    href={`/search?brands[]=${product.brand.id}`}
                    className="w-fit mb-3"
                >
                    <Badge
                        variant="outline"
                        className="hover:bg-primary hover:text-primary-foreground transition-colors cursor-pointer"
                    >
                        {getLocalizedField(product.brand, "name")}
                    </Badge>
                </Link>
            )}

            {/* Product Name */}
            <h1 className="text-3xl md:text-4xl font-bold mb-4">
                {getLocalizedField(product, "name")}
            </h1>

            {/* Price */}
            <div className="flex items-center gap-3 mb-6">
                {hasDiscount ? (
                    <>
                        <span className="text-2xl md:text-3xl font-bold">
                            {currentSalePrice} EGP
                        </span>
                        <span className="text-xl text-muted-foreground line-through">
                            {currentPrice} EGP
                        </span>
                        {discountPercentage > 0 && (
                            <Badge variant="destructive" className="ml-2">
                                {discountPercentage}% {t("off", "OFF")}
                            </Badge>
                        )}
                    </>
                ) : (
                    <span className="text-2xl md:text-3xl font-bold">
                        {currentPrice} EGP
                    </span>
                )}
            </div>
            <div className="flex justify-between">
                {product.category && (
                    <div className="flex items-center gap-2 mb-6">
                        <span className="text-muted-foreground">
                            {t("category", "Category")}:
                        </span>
                        <Link
                            href={`/search?categories[]=${product.category.id}`}
                            className="text-primary hover:underline"
                        >
                            {getLocalizedField(product.category, "name")}
                        </Link>
                    </div>
                )}

                {/* Availability */}
                <div className="mb-6">
                    <Badge
                        variant={isInStock ? "outline" : "destructive"}
                        className="inline-flex items-center gap-1.5 py-1 px-3"
                    >
                        {isInStock ? (
                            <Check className="h-3.5 w-3.5 text-green-600" />
                        ) : (
                            <AlertCircle className="h-3.5 w-3.5" />
                        )}
                        <span className="font-medium">
                            {isInStock
                                ? `${t("in_stock", "In Stock")} · ${
                                      selectedVariant
                                        ? selectedVariant.quantity
                                        : product.totalQuantity
                                  } ${t("available", "available")}`
                                : t("out_of_stock", "Out of Stock")}
                        </span>
                    </Badge>
                </div>
            </div>
        </div>
    );
}
