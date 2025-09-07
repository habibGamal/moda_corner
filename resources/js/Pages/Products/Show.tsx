import ProductGrid from "@/Components/ProductGrid";
import ProductActions from "@/Components/Products/ProductActions";
import ProductDescription from "@/Components/Products/ProductDescription";
import ProductGallery from "@/Components/Products/ProductGallery";
import ProductInfo from "@/Components/Products/ProductInfo";
import ProductQuantitySelector from "@/Components/Products/ProductQuantitySelector";

import ProductVariantSelector from "@/Components/Products/ProductVariantSelector";
import { PageTitle } from "@/Components/ui/page-title";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, ShoppingBag } from "lucide-react";
import { useState, useEffect } from "react";

interface ShowProps {
    product: App.Models.Product;
    relatedProducts: App.Models.Product[];
}

export default function Show({ product }: ShowProps) {
    const { t, getLocalizedField } = useI18n();
    const [quantity, setQuantity] = useState(1);

    // Initialize with first variant if variants exist
    const getDefaultVariant = () => {
        if (!product.variants || product.variants.length === 0) return null;
        // Try to find default variant first, otherwise use first variant
        return product.variants.find(v => v.is_default) || product.variants[0];
    };

    const [selectedVariant, setSelectedVariant] = useState<App.Models.ProductVariant | null>(getDefaultVariant());

    // Ensure the ProductVariantSelector knows about the initial selection
    useEffect(() => {
        const defaultVariant = getDefaultVariant();
        if (defaultVariant && !selectedVariant) {
            setSelectedVariant(defaultVariant);
        }
    }, [product.variants]);

    const handleVariantChange = (variant: App.Models.ProductVariant) => {
        console.log("Selected variant:", variant);
        setSelectedVariant(variant);
        // Reset quantity to 1 when variant changes
        setQuantity(1);
    };

    return (
        <>
            <Head title={getLocalizedField(product, "name")} />

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
                    <ProductInfo product={product} selectedVariant={selectedVariant || undefined} />

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

                    {(product.isInStock || (selectedVariant && selectedVariant.quantity > 0)) && (
                        <div className="space-y-6 mb-6">
                            <ProductQuantitySelector
                                maxQuantity={selectedVariant ? selectedVariant.quantity : (product.totalQuantity || 1)}
                                onChange={setQuantity}
                            />
                            <ProductActions
                                product={product}
                                quantity={quantity}
                                selectedVariant={selectedVariant || undefined}
                            />
                        </div>
                    )}
                    {/* Description - using the separate component */}
                    <div className="mb-8">
                        <ProductDescription product={product} />
                    </div>
                </div>
            </div>

            {/* Related products */}
            <ProductGrid
                sectionId="related_products"
                title="related_products"
                emptyMessage={t(
                    "no_related_products",
                    "No related products found"
                )}
            />
        </>
    );
}
