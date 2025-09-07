import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardFooter } from "@/Components/ui/card";
import { useI18n } from "@/hooks/use-i18n";
import useCart from "@/hooks/use-cart";
import { ShoppingBag } from "lucide-react";
import { Image } from "@/Components/ui/Image";
import { Link } from "@inertiajs/react";
import { App } from "@/types";

interface ProductCardProps {
    product: App.Models.Product;
}

export default function ProductCard({ product }: ProductCardProps) {
    const { getLocalizedField, t } = useI18n();
    const { addToCart, addingToCart } = useCart();
    const hasDiscount = product.sale_price && product.sale_price !== product.price;
    const discountPercentage = hasDiscount
        ? Math.round(
              ((product.price - product.sale_price!) / product.price) * 100
          )
        : 0;
    return (
        <Card className="group overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 h-full flex flex-col border-0 shadow-sm bg-card/50 backdrop-blur-sm">
            <Link
                href={`/products/${product.id}`}
                className="flex-1 flex flex-col"
            >
                <div className="aspect-[1/1] relative bg-gradient-to-br from-muted/30 to-muted/60 overflow-hidden">
                    <Image
                        src={product.featured_image || "/placeholder.jpg"}
                        alt={getLocalizedField(product, "name")}
                        className="object-contain w-full h-full aspect-square transition-transform duration-500 group-hover:scale-110"
                    />
                    {/* Overlay gradient for better text contrast */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                    {/* Discount Badge */}
                    {hasDiscount && discountPercentage > 0 && (
                        <div className="absolute top-3 right-3 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse">
                            -{discountPercentage}%
                        </div>
                    )}

                    {/* Stock Status Badge */}
                    {product.quantity <= 0 && (
                        <div className="absolute top-3 left-3 bg-gray-900/90 text-white text-xs font-medium px-3 py-1.5 rounded-full">
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

                    {/* Price Section */}
                    <div className="flex items-center justify-between mt-auto pt-3">
                        <div className="flex items-center gap-2">
                            {hasDiscount ? (
                                <div className="flex flex-col">
                                    <span className="text-xl font-bold text-primary">
                                        {Number(product.sale_price).toFixed(2)} EGP
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
                            className="rounded-full h-10 w-10 bg-primary/10 hover:bg-primary hover:text-primary-foreground transition-all duration-200 opacity-0 group-hover:opacity-100"
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                addToCart(product.id, 1);
                            }}
                            disabled={addingToCart[product.id] || product.quantity <= 0}
                        >
                            <ShoppingBag className="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Link>

            {/* Main Action Button */}
            <CardFooter className="p-5 pt-0">
                <Button
                    variant={product.quantity <= 0 ? "secondary" : "default"}
                    className="w-full font-medium transition-all duration-200 hover:shadow-md"
                    size="lg"
                    onClick={() => addToCart(product.id, 1)}
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
