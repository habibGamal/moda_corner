import { useState, useEffect, useRef } from "react";
import { useI18n } from "@/hooks/use-i18n";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Label } from "@/Components/ui/label";
import { Check } from "lucide-react";
import { cn } from "@/lib/utils";
import { Badge } from "@/Components/ui/badge";
import { App } from "@/types";

interface ProductVariantSelectorProps {
    product: App.Models.Product;
    onVariantChange: (variant: App.Models.ProductVariant) => void;
    selectedVariantId?: number;
}

export default function ProductVariantSelector({
    product,
    onVariantChange,
    selectedVariantId,
}: ProductVariantSelectorProps) {
    const { t } = useI18n();
    const [selectedVariant, setSelectedVariant] = useState<App.Models.ProductVariant | null>(null);
    const isInitialized = useRef(false);

    // Get all variants with colors and without colors
    const variantsWithColors = product.variants?.filter((v: App.Models.ProductVariant) => v.color) || [];
    const variantsWithoutColors = product.variants?.filter((v: App.Models.ProductVariant) => !v.color) || [];

    // Get all unique attributes
    const uniqueColors = [...new Set(product.variants?.map((v: App.Models.ProductVariant) => v.color).filter(Boolean))] as string[];

    // Get all variants with sizes and without sizes
    const variantsWithSizes = product.variants?.filter((v: App.Models.ProductVariant) => v.size) || [];
    const variantsWithoutSizes = product.variants?.filter((v: App.Models.ProductVariant) => !v.size) || [];

    // Selected attribute values
    const [selectedColor, setSelectedColor] = useState<string | null>(null);
    const [selectedSize, setSelectedSize] = useState<string | null>(null);
    const [selectedCapacity, setSelectedCapacity] = useState<string | null>(null);

    // Get available sizes based on selected color
    const availableSizesForColor = product.variants
        ?.filter((v: App.Models.ProductVariant) => {
            // If a color is selected, only show sizes for that color
            if (selectedColor) return v.color === selectedColor;
            // If no color is selected, show:
            // - Sizes from variants without colors
            // - Or all sizes if there are no variants with colors
            return !v.color || uniqueColors.length === 0;
        })
        .map((v: App.Models.ProductVariant) => v.size)
        .filter(Boolean) as string[] || [];
    const uniqueSizes = [...new Set(availableSizesForColor)];

    // Get available capacities based on selected color and size
    const availableCapacitiesForColorAndSize = product.variants
        ?.filter((v: App.Models.ProductVariant) => {
            // Match color if selected
            const colorMatches = !selectedColor || v.color === selectedColor;
            // Match size if selected
            const sizeMatches = !selectedSize || v.size === selectedSize;

            // If we have variants with colors and sizes, enforce hierarchy
            if (uniqueColors.length > 0 && uniqueSizes.length > 0) {
                return colorMatches && sizeMatches;
            }
            // If we have no colors but have sizes, size is the leading property
            else if (uniqueColors.length === 0 && uniqueSizes.length > 0) {
                return sizeMatches;
            }
            // If we have no sizes but have colors, color is the only property to match
            else if (uniqueColors.length > 0 && uniqueSizes.length === 0) {
                return colorMatches;
            }
            // If we have neither colors nor sizes, show all capacities
            else {
                return true;
            }
        })
        .map((v: App.Models.ProductVariant) => v.capacity)
        .filter(Boolean) as string[] || [];
    const uniqueCapacities = [...new Set(availableCapacitiesForColorAndSize)];

    // Filter available variants based on selections
    const availableVariants = product.variants?.filter((variant: App.Models.ProductVariant) => {
        let matches = true;
        if (selectedColor && variant.color !== selectedColor) matches = false;
        if (selectedSize && variant.size !== selectedSize) matches = false;
        if (selectedCapacity && variant.capacity !== selectedCapacity) matches = false;
        return matches;
    });

    // Find default variant - only run once on mount
    useEffect(() => {
        if (!product.variants?.length || isInitialized.current) return;

        // If there's a selected variant ID, use that
        if (selectedVariantId) {
            const variant = product.variants.find((v: App.Models.ProductVariant) => v.id === selectedVariantId);
            if (variant) {
                setSelectedVariant(variant);
                setSelectedColor(variant.color || null);
                setSelectedSize(variant.size || null);
                setSelectedCapacity(variant.capacity || null);
                isInitialized.current = true;
                return;
            }
        }

        // Otherwise, use default variant or first variant
        const defaultVariant = product.variants.find((v: App.Models.ProductVariant) => v.is_default) || product.variants[0];
        setSelectedVariant(defaultVariant);
        setSelectedColor(defaultVariant.color || null);
        setSelectedSize(defaultVariant.size || null);
        setSelectedCapacity(defaultVariant.capacity || null);
        isInitialized.current = true;
    }, [product.variants, selectedVariantId]);

    // Update selected size and capacity when color changes
    useEffect(() => {
        // If selected color changes, we need to check if current size is valid
        if (selectedSize && uniqueSizes.length > 0 && !uniqueSizes.includes(selectedSize)) {
            setSelectedSize(null);
        }

        // If selected size changes, we need to check if current capacity is valid
        if (selectedCapacity && uniqueCapacities.length > 0 && !uniqueCapacities.includes(selectedCapacity)) {
            setSelectedCapacity(null);
        }
    }, [selectedColor, selectedSize, uniqueSizes, uniqueCapacities]);

    // Update selected variant when attributes change
    useEffect(() => {
        if (!availableVariants?.length) return;

        const matchingVariant = availableVariants[0];
        if (matchingVariant && (!selectedVariant || selectedVariant.id !== matchingVariant.id)) {
            setSelectedVariant(matchingVariant);
            onVariantChange(matchingVariant);
        }
    }, [selectedColor, selectedSize, selectedCapacity, availableVariants]);

    if (!product.variants?.length) return null;

    return (
        <div className="space-y-4">
            {/* Color selector - always show if available */}
            {uniqueColors.length > 0 && (
                <div>
                    <h3 className="text-sm font-medium mb-3">{t("color", "Color")}</h3>
                    <div className="flex flex-wrap gap-2">
                        {uniqueColors.map((color: string) => (
                            <Button
                                key={color as React.Key}
                                type="button"
                                variant={color === selectedColor ? "default" : "outline"}
                                className={cn(
                                    "h-9 px-3 rounded-md",
                                    color === selectedColor ? "text-primary-foreground" : "text-foreground"
                                )}
                                onClick={() => {
                                    setSelectedColor(color);
                                    // Reset subsequent selections when color changes
                                    setSelectedSize(null);
                                    setSelectedCapacity(null);
                                }}
                            >
                                {color === selectedColor && <Check className="mr-1 h-4 w-4" />}
                                {color}
                            </Button>
                        ))}
                    </div>
                </div>
            )}

            {/* Size selector - show if:
                - There are colors and a color is selected, or
                - There are no colors but there are sizes
            */}
            {((selectedColor && uniqueSizes.length > 0) ||
              (uniqueColors.length === 0 && uniqueSizes.length > 0)) && (
                <div>
                    <h3 className="text-sm font-medium mb-3">{t("size", "Size")}</h3>
                    <div className="flex flex-wrap gap-2">
                        {uniqueSizes.map((size: string) => (
                            <Button
                                key={size as React.Key}
                                type="button"
                                variant={size === selectedSize ? "default" : "outline"}
                                className={cn(
                                    "h-9 px-3 rounded-md",
                                    size === selectedSize ? "text-primary-foreground" : "text-foreground"
                                )}
                                onClick={() => {
                                    setSelectedSize(size);
                                    // Reset capacity selection when size changes
                                    setSelectedCapacity(null);
                                }}
                            >
                                {size === selectedSize && <Check className="mr-1 h-4 w-4" />}
                                {size}
                            </Button>
                        ))}
                    </div>
                </div>
            )}

            {/* Capacity selector - show if:
                - There are colors and sizes, and both are selected, or
                - There are colors but no sizes, and color is selected, or
                - There are sizes but no colors, and size is selected, or
                - There are no colors and no sizes but there are capacities
            */}
            {((selectedColor && selectedSize && uniqueCapacities.length > 0) ||
              (selectedColor && uniqueSizes.length === 0 && uniqueCapacities.length > 0) ||
              (selectedSize && uniqueColors.length === 0 && uniqueCapacities.length > 0) ||
              (uniqueColors.length === 0 && uniqueSizes.length === 0 && uniqueCapacities.length > 0)) && (
                <div>
                    <h3 className="text-sm font-medium mb-3">{t("capacity", "Capacity")}</h3>
                    <div className="flex flex-wrap gap-2">
                        {uniqueCapacities.map((capacity: string) => (
                            <Button
                                key={capacity as React.Key}
                                type="button"
                                variant={capacity === selectedCapacity ? "default" : "outline"}
                                className={cn(
                                    "h-9 px-3 rounded-md",
                                    capacity === selectedCapacity ? "text-primary-foreground" : "text-foreground"
                                )}
                                onClick={() => setSelectedCapacity(capacity)}
                            >
                                {capacity === selectedCapacity && <Check className="mr-1 h-4 w-4" />}
                                {capacity}
                            </Button>
                        ))}
                    </div>
                </div>
            )}

            {/* Selected variant status */}
            {selectedVariant && (
                <div className="mt-4">
                    <Badge variant={selectedVariant.quantity > 0 ? "outline" : "destructive"} className="text-sm">
                        {selectedVariant.quantity > 0
                            ? `${t("in_stock", "In Stock")} · ${t("available", "available")}`
                            : t("out_of_stock", "Out of Stock")}
                    </Badge>
                </div>
            )}
        </div>
    );
}
