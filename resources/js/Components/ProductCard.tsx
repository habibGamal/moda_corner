import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardFooter } from "@/Components/ui/card";
import { useI18n } from "@/hooks/use-i18n";
import useCart from "@/hooks/use-cart";
import { ShoppingBag, Heart } from "lucide-react";
import { Image } from "@/Components/ui/Image";
import { Link } from "@inertiajs/react";
import { router } from "@inertiajs/react";
import { App } from "@/types";
import ReactPixel from "react-facebook-pixel";

interface ProductCardProps {
    product: App.Models.Product;
}

export default function ProductCard({ product }: ProductCardProps) {
    const { getLocalizedField, t } = useI18n();
    const { addToCart, addingToCart } = useCart();
    const hasDiscount =
        product.sale_price && product.sale_price !== product.price;
    const discountPercentage = hasDiscount
        ? Math.round(
              ((product.price - product.sale_price!) / product.price) * 100
          )
        : 0;

    const addToWishlist = () => {
        router.post(
            route("wishlist.add"),
            { product_id: product.id },
            {
                preserveScroll: true,
                onSuccess: () => {
                    ReactPixel.track("AddToWishlist", {
                        content_ids: [product.id],
                        contents: [{ id: product.id, quantity: 1 }],
                        value: product?.sale_price,
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
        <Card className="group overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 h-full flex flex-col border-0 shadow-sm bg-card/50 backdrop-blur-sm dark:shadow-lg dark:hover:shadow-2xl">
            <Link
                href={`/products/${product.id}`}
                className="flex-1 flex flex-col"
            >
                <div className="aspect-[1/1] relative bg-gradient-to-br from-muted/30 to-muted/60 dark:from-muted/50 dark:to-muted/80 overflow-hidden">
                    <Image
                        src={product.featured_image || "/placeholder.jpg"}
                        alt={getLocalizedField(product, "name")}
                        className="object-contain w-full h-full aspect-square transition-transform duration-500 group-hover:scale-110"
                    />
                    {/* Overlay gradient for better text contrast */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 dark:from-black/40" />

                    {/* Discount Badge */}
                    {hasDiscount && discountPercentage > 0 && (
                        <div className="absolute top-3 right-3 bg-gradient-to-r from-red-500 to-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse">
                            -{discountPercentage}%
                        </div>
                    )}

                    {/* Stock Status Badge */}
                    {product.quantity <= 0 && (
                        <div className="absolute top-3 left-3 bg-gray-900/90 dark:bg-gray-100/90 text-white dark:text-gray-900 text-xs font-medium px-3 py-1.5 rounded-full">
                            {t("out_of_stock", "Out of Stock")}
                        </div>
                    )}
                </div>

                <CardContent className="p-5 flex-1 space-y-3">
                    {/* Brand */}
                    {product.brand && (
                        <div className="flex items-center">
                            <span className="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded-full">
                                {getLocalizedField(product.brand, "name")}
                            </span>
                        </div>
                    )}

                    {/* Product Name */}
                    <h3 className="font-semibold text-lg leading-tight line-clamp-2 group-hover:text-primary transition-colors duration-200">
                        {getLocalizedField(product, "name")}
                    </h3>

                    {/* Rating */}
                    {product.average_rating > 0 && (
                        <div className="flex items-center gap-1.5">
                            <div className="flex items-center">
                                {Array.from({ length: 5 }).map((_, index) => (
                                    <svg
                                        key={index}
                                        className={`h-4 w-4 ${
                                            index <
                                            Math.round(product.average_rating)
                                                ? "text-yellow-400 fill-yellow-400"
                                                : "text-gray-300 dark:text-gray-600"
                                        }`}
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                ))}
                            </div>
                            <span className="text-sm text-muted-foreground">
                                {product.average_rating.toFixed(1)} (
                                {product.reviews_count})
                            </span>
                        </div>
                    )}

                    {/* Price Section */}
                    <div className="flex items-center justify-between mt-auto pt-3">
                        <div className="flex items-center gap-2">
                            {hasDiscount ? (
                                <div className="flex flex-col">
                                    <span className="text-xl font-bold text-primary">
                                        {Number(product.sale_price).toFixed(2)}{" "}
                                        EGP
                                    </span>
                                    <span className="text-muted-foreground text-sm line-through">
                                        {Number(product.price).toFixed(2)} EGP
                                    </span>
                                </div>
                            ) : (
                                <span className="text-xl font-bold text-foreground">
                                    {Number(product.price).toFixed(2)} EGP
                                </span>
                            )}
                        </div>

                        {/* Quick Add Button */}
                        <Button
                            variant="ghost"
                            size="icon"
                            className="rounded-full h-10 w-10 bg-primary/10 hover:bg-primary hover:text-primary-foreground"
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                product.is_in_wishlist
                                    ? removeFromWishlist()
                                    : addToWishlist();
                            }}
                        >
                            <Heart
                                className="h-4 w-4"
                                fill={product.is_in_wishlist ? "red" : "none"}
                                stroke={
                                    product.is_in_wishlist
                                        ? "red"
                                        : "currentColor"
                                }
                            />
                        </Button>
                    </div>
                </CardContent>
            </Link>

            {/* Main Action Buttons */}
            <CardFooter className="p-5 pt-0">
                <Button
                    variant={product.quantity <= 0 ? "secondary" : "default"}
                    className="flex-1 font-medium transition-all duration-200 hover:shadow-md"
                    size="lg"
                    onClick={() => addToCart(product.id, 1, undefined, product)}
                    disabled={addingToCart[product.id] || product.quantity <= 0}
                >
                    <ShoppingBag className="h-4 w-4 mr-2" />
                    {addingToCart[product.id]
                        ? t("adding", "Adding...")
                        : product.quantity <= 0
                        ? t("out_of_stock", "Out of Stock")
                        : t("add_to_cart", "Add to Cart")}
                </Button>
            </CardFooter>
        </Card>
    );
}
