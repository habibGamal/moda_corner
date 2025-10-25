import ProductCard from "@/Components/ProductCard";
import { useI18n } from "@/hooks/use-i18n";
import { PackageOpen, ArrowRight, ArrowLeft } from "lucide-react";
import { Link, WhenVisible, usePage } from "@inertiajs/react";
import { Skeleton } from "@/Components/ui/skeleton";
import { cn } from "@/lib/utils";
import EmptyState from "./ui/empty-state";
import { App } from "@/types";
import { useMemo } from "react";

interface ProductGridProps {
    title?: string | null;
    viewAllLink?: string | null;
    emptyMessage?: string;
    className?: string;
    sectionId: string | number;
    dataKey?: string;
    paginationKey?: string;
    viewType?: "scroll" | "grid"; // Added viewType prop
}

interface ProductWithVariant extends App.Models.Product {
    selectedVariant?: App.Models.ProductVariant;
}

/**
 * Get unique color variants from a product
 * Groups variants by color and returns one representative variant per unique color
 */
function getUniqueColorVariants(
    product: App.Models.Product
): App.Models.ProductVariant[] {
    if (!product.variants || product.variants.length === 0) {
        return [];
    }

    // Group variants by color
    const colorMap = new Map<string, App.Models.ProductVariant>();

    product.variants.forEach((variant) => {
        const color = variant.color?.toLowerCase() || "default";
        // Only keep the first variant of each color
        if (!colorMap.has(color)) {
            colorMap.set(color, variant);
        }
    });

    return Array.from(colorMap.values());
}

/**
 * Flatten products to show each unique color variant as a separate card
 */
function flattenProductsWithVariants(
    products: App.Models.Product[]
): ProductWithVariant[] {
    const result: ProductWithVariant[] = [];

    products.forEach((product) => {
        const uniqueColorVariants = getUniqueColorVariants(product);

        // If product has variants with different colors, create a card for each color
        if (uniqueColorVariants.length > 1) {
            uniqueColorVariants.forEach((variant) => {
                result.push({
                    ...product,
                    selectedVariant: variant,
                });
            });
        } else {
            // If no variants or only one color, show product as is
            result.push(product);
        }
    });

    return result;
}

export default function ProductGrid({
    title,
    viewAllLink,
    emptyMessage,
    className = "",
    sectionId,
    viewType = "scroll", // Default to horizontal scrolling view
}: ProductGridProps) {
    const { t, direction } = useI18n();
    const page = usePage();

    const sectionKey = `section_${sectionId}_page`;
    const actualDataKey = `${sectionKey}_data`;
    const actualPaginationKey = `${sectionKey}_pagination`;

    // Get data from page props
    const products = page.props[actualDataKey] as
        | App.Models.Product[]
        | undefined;

    console.log(page.props[actualPaginationKey]);
    const pagination = page.props[actualPaginationKey] as any;

    const ProductCardSkeleton = () => (
        <div
            className={cn(
                "space-y-3",
                viewType === "scroll"
                    ? "snap-start flex-shrink-0 w-[250px] sm:w-[300px]"
                    : "w-full"
            )}
        >
            <Skeleton className="w-full h-[180px] rounded-lg" />
            <Skeleton className="w-3/4 h-5 rounded" />
            <Skeleton className="w-1/2 h-4 rounded" />
            <Skeleton className="w-1/4 h-6 rounded" />
        </div>
    );

    // Flatten products to show each unique color variant as a separate card
    const displayProducts = useMemo(() => {
        if (!products) return [];
        return flattenProductsWithVariants(products);
    }, [products]);
    return (
        <section className={cn(`py-12 md:py-16`, className)}>
            {(title || viewAllLink) && (
                <div className="flex items-center justify-between pb-8">
                    {title && (
                        <div className="flex items-center gap-2 md:gap-4">
                            <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-primary flex items-center justify-center">
                                <PackageOpen className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                            </div>
                            <h2 className="text-2xl md:text-3xl font-bold">
                                {t(title)}
                            </h2>
                        </div>
                    )}
                    {viewAllLink && (
                        <Link
                            href={viewAllLink}
                            className="inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors group"
                        >
                            {t("view_all", "View All")}
                            {direction === "rtl" ? (
                                <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                            ) : (
                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            )}
                        </Link>
                    )}
                </div>
            )}

            {displayProducts && displayProducts.length > 0 ? (
                <div
                    className={cn(
                        viewType === "scroll"
                            ? "flex overflow-x-auto pb-4 gap-4 snap-x scrollbar-hide"
                            : "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6"
                    )}
                >
                    {displayProducts.map((product, index) => {
                        // Create unique key: use variant id if selected, otherwise product id
                        const uniqueKey = product.selectedVariant
                            ? `${product.id}-variant-${product.selectedVariant.id}`
                            : `product-${product.id}`;

                        return (
                            <div
                                key={uniqueKey}
                                className={cn(
                                    viewType === "scroll"
                                        ? "snap-start flex-shrink-0 w-[250px] sm:w-[300px]"
                                        : ""
                                )}
                            >
                                <ProductCard product={product} />
                            </div>
                        );
                    })}

                    {pagination && pagination.next_page_url && (
                        <WhenVisible
                            params={{
                                only: [actualDataKey, actualPaginationKey],
                                data: {
                                    [sectionKey]: pagination.current_page + 1,
                                },
                                preserveUrl: true,
                                onSuccess: (page) => {
                                    window.history.state.page.props =
                                        page.props;
                                },
                            }}
                            always={!!pagination.next_page_url}
                            fallback={<ProductCardSkeleton />}
                        >
                            <ProductCardSkeleton />
                        </WhenVisible>
                    )}
                </div>
            ) : (
                <EmptyState
                    title={
                        emptyMessage ||
                        t("no_products_available", "No products available")
                    }
                    icon={
                        <PackageOpen className="h-8 w-8 text-muted-foreground" />
                    }
                />
            )}
        </section>
    );
}
