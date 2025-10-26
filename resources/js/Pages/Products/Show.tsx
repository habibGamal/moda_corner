import ProductGrid from "@/Components/ProductGrid";
import ProductActions from "@/Components/Products/ProductActions";
import ProductDescription from "@/Components/Products/ProductDescription";
import ProductGallery from "@/Components/Products/ProductGallery";
import ProductInfo from "@/Components/Products/ProductInfo";
import ProductQuantitySelector from "@/Components/Products/ProductQuantitySelector";
import ProductReviews from "@/Components/Products/ProductReviews";
import ProductVariantSelector from "@/Components/Products/ProductVariantSelector";
import { PageTitle } from "@/Components/ui/page-title";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, ShoppingBag } from "lucide-react";
import { useState, useEffect, useMemo } from "react";
import ReactPixel from "react-facebook-pixel";

interface ShowProps {
    product: App.Models.Product;
    relatedProducts: App.Models.Product[];
    auth: {
        user: App.Models.User | null;
    };
}

export default function Show({ product, auth }: ShowProps) {
    const { t, getLocalizedField } = useI18n();
    const [quantity, setQuantity] = useState(1);

    // Get variant ID from URL query parameter (calculated once)
    const initialVariantId = useMemo(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const variantParam = urlParams.get('variant');
        return variantParam ? parseInt(variantParam) : undefined;
    }, []);

    // Initialize with first variant if variants exist
    const getDefaultVariant = () => {
        if (!product.variants || product.variants.length === 0) return null;

        // First priority: variant from URL
        if (initialVariantId) {
            const urlVariant = product.variants.find((v) => v.id === initialVariantId);
            if (urlVariant) return urlVariant;
        }

        // Second priority: default variant
        const defaultVariant = product.variants.find((v) => v.is_default);
        if (defaultVariant) return defaultVariant;

        // Last resort: first variant
        return product.variants[0];
    };

    const [selectedVariant, setSelectedVariant] =
        useState<App.Models.ProductVariant | null>(getDefaultVariant());

    const handleVariantChange = (variant: App.Models.ProductVariant) => {
        console.log("Selected variant:", variant);
        setSelectedVariant(variant);
        // Reset quantity to 1 when variant changes
        setQuantity(1);
    };

    useEffect(() => {
        if (selectedVariant)
            ReactPixel.track("ViewContent", {
                content_ids: [selectedVariant.id],
                contents: [{ id: selectedVariant.id, quantity: 1 }],
                content_type: "product",
                value: selectedVariant?.sale_price,
                currency: "EGP",
            });
        else
            ReactPixel.track("ViewContent", {
                content_ids: [product.id],
                contents: [{ id: product.id, quantity: 1 }],
                content_type: "product",
                value: product?.sale_price,
                currency: "EGP",
            });
    }, [selectedVariant]);

    return (
        <>
            <Head title={getLocalizedField(product, "name")} />
            <div className="container mt-4">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-4">
                    {/* Left column - Product images */}
                    <div>
                        <ProductGallery
                            product={product}
                            selectedVariant={selectedVariant || undefined}
                        />
                    </div>

                    {/* Right column - Product details */}
                    <div className="flex flex-col">
                        <ProductInfo
                            product={product}
                            selectedVariant={selectedVariant || undefined}
                        />

                        {/* Variant selector */}
                        {product.variants && product.variants.length > 0 && (
                            <div className="mb-6">
                                <ProductVariantSelector
                                    product={product}
                                    onVariantChange={handleVariantChange}
                                    selectedVariantId={selectedVariant?.id}
                                />
                            </div>
                        )}

                        {(product.isInStock ||
                            (selectedVariant &&
                                selectedVariant.quantity > 0)) && (
                            <div className="space-y-6 mb-6">
                                <ProductQuantitySelector
                                    maxQuantity={
                                        selectedVariant
                                            ? selectedVariant.quantity
                                            : product.totalQuantity || 1
                                    }
                                    onChange={setQuantity}
                                />
                                <ProductActions
                                    product={product}
                                    quantity={quantity}
                                    selectedVariant={
                                        selectedVariant || undefined
                                    }
                                />
                            </div>
                        )}
                        {/* Description - using the separate component */}
                        <div className="mb-8">
                            <ProductDescription product={product} />
                        </div>
                    </div>
                </div>

                {/* Product Reviews */}
                <ProductReviews product={product} auth={auth} />

                {/* Related products */}
                <ProductGrid
                    sectionId="related_products"
                    title="related_products"
                    emptyMessage={t(
                        "no_related_products",
                        "No related products found"
                    )}
                />
            </div>
        </>
    );
}
