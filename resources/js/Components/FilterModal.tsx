import { useState, useEffect } from "react";
import { useI18n } from "@/hooks/use-i18n";
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetTrigger,
    SheetFooter,
} from "@/Components/ui/sheet";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Slider } from "@/Components/ui/slider";
import {
    FilterIcon,
    X,
    CheckIcon,
    ChevronDown,
    ChevronRight,
    ChevronLeft,
} from "lucide-react";
import { Label } from "@/Components/ui/label";
import { Badge } from "@/Components/ui/badge";
import { Separator } from "@/Components/ui/separator";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { router } from "@inertiajs/react";
import { Input } from "./ui/input";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "./ui/collapsible";
import { App } from "@/types";

// New component for nested categories
interface CategoryItemProps {
    category: App.Models.Category;
    localSelectedCategories: string[];
    toggleCategory: (id: string) => void;
    level: number;
    getName: (item: { name_en: string; name_ar: string }) => string;
}

const CategoryItem = ({
    category,
    localSelectedCategories,
    toggleCategory,
    level,
    getName,
}: CategoryItemProps) => {
    const [isOpen, setIsOpen] = useState(true);
    const hasChildren = category.children && category.children.length > 0;
    const { direction } = useI18n();
    return (
        <div className="space-y-1">
            {hasChildren ? (
                <Collapsible open={isOpen} onOpenChange={setIsOpen} dir={direction}>
                    <div className="flex items-center space-x-2 rtl:space-x-reverse">
                        <div style={{ width: `${level * 12}px` }}></div>
                        <CollapsibleTrigger className="focus:outline-none">
                            {isOpen ? (
                                <ChevronDown className="h-4 w-4" />
                            ) : (
                                direction === "rtl" ? (
                                    <ChevronLeft className="h-4 w-4" />
                                ) : (
                                    <ChevronRight className="h-4 w-4" />
                                )
                            )}
                        </CollapsibleTrigger>
                        <Checkbox
                            id={`category-${category.id}`}
                            checked={localSelectedCategories.includes(
                                category.id.toString()
                            )}
                            onCheckedChange={() =>
                                toggleCategory(category.id.toString())
                            }
                        />
                        <Label
                            htmlFor={`category-${category.id}`}
                            className="text-sm cursor-pointer"
                        >
                            {getName(category)}
                        </Label>
                    </div>
                    <CollapsibleContent>
                        <div className="space-y-1 ml-4 rtl:ml-0 rtl:mr-4">
                            {category.children!.map((child) => (
                                <CategoryItem
                                    key={child.id}
                                    category={child}
                                    localSelectedCategories={
                                        localSelectedCategories
                                    }
                                    toggleCategory={toggleCategory}
                                    level={level + 1}
                                    getName={getName}
                                />
                            ))}
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            ) : (
                <div className="flex items-center space-x-2 rtl:space-x-reverse">
                    <div style={{ width: `${level * 12}px` }}></div>
                    <div className="w-4"></div>
                    <Checkbox
                        id={`category-${category.id}`}
                        checked={localSelectedCategories.includes(
                            category.id.toString()
                        )}
                        onCheckedChange={() =>
                            toggleCategory(category.id.toString())
                        }
                    />
                    <Label
                        htmlFor={`category-${category.id}`}
                        className="text-sm cursor-pointer"
                    >
                        {getName(category)}
                    </Label>
                </div>
            )}
        </div>
    );
};

interface FilterModalProps {
    brands: App.Models.Brand[];
    categories: App.Models.Category[];
    priceRange: App.Types.PriceRange;
    selectedBrands: string[];
    selectedCategories: string[];
    minPrice: number | null;
    maxPrice: number | null;
    query: string;
    sortBy: string;
}

export function FilterModal({
    brands = [],
    categories = [],
    priceRange = { min: 0, max: 1000 },
    selectedBrands = [],
    selectedCategories = [],
    minPrice = null,
    maxPrice = null,
    query = "",
    sortBy = "newest",
}: FilterModalProps) {
    const { t, currentLocale, direction } = useI18n();
    const isRTL = currentLocale === "ar";

    console.log(brands, categories, priceRange, selectedBrands, selectedCategories, minPrice, maxPrice, query, sortBy);
    // Local state for filters
    const [localSelectedBrands, setLocalSelectedBrands] = useState<string[]>(
        selectedBrands || []
    );
    const [localSelectedCategories, setLocalSelectedCategories] = useState<
        string[]
    >(selectedCategories || []);
    const [localPriceRange, setLocalPriceRange] = useState<number[]>([
        minPrice !== null ? minPrice : priceRange.min,
        maxPrice !== null ? maxPrice : priceRange.max,
    ]);
    const [isOpen, setIsOpen] = useState(false);
    const [activeFiltersCount, setActiveFiltersCount] = useState(0);

    // Update local state when props change
    useEffect(() => {
        setLocalSelectedBrands(selectedBrands || []);
        setLocalSelectedCategories(selectedCategories || []);
        setLocalPriceRange([
            minPrice !== null ? minPrice : priceRange.min,
            maxPrice !== null ? maxPrice : priceRange.max,
        ]);

        // Calculate active filters count
        let count = 0;
        if (selectedBrands?.length) count += selectedBrands.length;
        if (selectedCategories?.length) count += selectedCategories.length;
        if (minPrice !== null || maxPrice !== null) count += 1;
        setActiveFiltersCount(count);
    }, [selectedBrands, selectedCategories, minPrice, maxPrice, priceRange]);

    const toggleBrand = (brandId: string) => {
        setLocalSelectedBrands((current) => {
            if (current.includes(brandId)) {
                return current.filter((id) => id !== brandId);
            } else {
                return [...current, brandId];
            }
        });
    };

    const toggleCategory = (categoryId: string) => {
        setLocalSelectedCategories((current) => {
            if (current.includes(categoryId)) {
                return current.filter((id) => id !== categoryId);
            } else {
                return [...current, categoryId];
            }
        });
    };

    const applyFilters = () => {
        router.get("/search", {
            q: query,
            brands: localSelectedBrands,
            categories: localSelectedCategories,
            min_price:
                localPriceRange[0] !== priceRange.min
                    ? localPriceRange[0]
                    : null,
            max_price:
                localPriceRange[1] !== priceRange.max
                    ? localPriceRange[1]
                    : null,
            sort_by: sortBy,
        });

        setIsOpen(false);
    };

    const resetFilters = () => {
        setLocalSelectedBrands([]);
        setLocalSelectedCategories([]);
        setLocalPriceRange([priceRange.min, priceRange.max]);
    };

    // Function to get the name based on current language
    const getName = (item: { name_en: string; name_ar: string }) => {
        return isRTL ? item.name_ar : item.name_en;
    };

    const hasActiveFilters =
        localSelectedBrands.length > 0 ||
        localSelectedCategories.length > 0 ||
        localPriceRange[0] !== priceRange.min ||
        localPriceRange[1] !== priceRange.max;
    return (
        <Sheet open={isOpen} onOpenChange={setIsOpen}>
            <SheetTrigger asChild>
                <Button
                    variant="outline"
                    className="flex items-center gap-2"
                    aria-label={t("filters", "Filters")}
                >
                    <FilterIcon className="h-4 w-4" />
                    <span>{t("filters", "Filters")}</span>
                    {activeFiltersCount > 0 && (
                        <Badge variant="secondary" className="text-xs">
                            {activeFiltersCount}
                        </Badge>
                    )}
                </Button>
            </SheetTrigger>
            <SheetContent
                side={isRTL ? "left" : "right"}
                className="w-full sm:max-w-md flex flex-col"
                dir={direction}
            >
                <SheetHeader className="space-y-1 mt-4">
                    <div className="flex items-center justify-between">
                        <SheetTitle>{t("filters", "Filters")}</SheetTitle>
                        {hasActiveFilters && (
                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={resetFilters}
                            >
                                {t("reset_all", "Reset all")}
                            </Button>
                        )}
                    </div>
                </SheetHeader>

                <ScrollArea className="flex-1 mt-6 pr-4" dir={direction}>
                    <div className="space-y-6">
                        {/* Brands filter */}
                        {brands.length > 0 && (
                            <div className="space-y-4">
                                <h3 className="text-lg font-medium">
                                    {t("brands", "Brands")}
                                </h3>
                                <div className="space-y-2">
                                    {brands.map((brand) => (
                                        <div
                                            key={brand.id}
                                            className="flex items-center space-x-2 rtl:space-x-reverse"
                                        >
                                            <Checkbox
                                                id={`brand-${brand.id}`}
                                                checked={localSelectedBrands.includes(
                                                    brand.id.toString()
                                                )}
                                                onCheckedChange={() =>
                                                    toggleBrand(
                                                        brand.id.toString()
                                                    )
                                                }
                                            />
                                            <Label
                                                htmlFor={`brand-${brand.id}`}
                                                className="text-sm cursor-pointer"
                                            >
                                                {getName(brand)}
                                            </Label>
                                        </div>
                                    ))}
                                </div>
                                {brands.length === 0 && (
                                    <div className="py-2 text-sm text-muted-foreground">
                                        {t(
                                            "no_brands_available",
                                            "No brands available"
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        <Separator className="my-6" />

                        {/* Categories filter */}
                        {categories.length > 0 && (
                            <div className="space-y-4">
                                <h3 className="text-lg font-medium">
                                    {t("categories", "Categories")}
                                </h3>
                                <div className="space-y-2">
                                    {categories.map((category) => (
                                        <CategoryItem
                                            key={category.id}
                                            category={category}
                                            localSelectedCategories={
                                                localSelectedCategories
                                            }
                                            toggleCategory={toggleCategory}
                                            level={0}
                                            getName={getName}
                                        />
                                    ))}
                                </div>
                                {categories.length === 0 && (
                                    <div className="py-2 text-sm text-muted-foreground">
                                        {t(
                                            "no_categories_available",
                                            "No categories available"
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        <Separator className="my-6" />

                        {/* Price range filter */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-medium">
                                {t("price_range", "Price Range")}
                            </h3>
                            <div className="px-4">
                                <div className="flex items-center justify-between gap-4">
                                    <div className="space-y-2 w-full">
                                        <Label
                                            htmlFor="min-price"
                                            className="text-sm"
                                        >
                                            {t("min_price", "Min Price")}
                                        </Label>
                                        <Input
                                            id="min-price"
                                            type="number"
                                            value={localPriceRange[0]}
                                            onChange={(e) => {
                                                const value = Number(
                                                    e.target.value
                                                );
                                                setLocalPriceRange([
                                                    value,
                                                    localPriceRange[1],
                                                ]);
                                            }}
                                            className="w-full"
                                        />
                                    </div>
                                    <span className="text-muted-foreground mx-1 mt-8">
                                        —
                                    </span>
                                    <div className="space-y-2 w-full">
                                        <Label
                                            htmlFor="max-price"
                                            className="text-sm"
                                        >
                                            {t("max_price", "Max Price")}
                                        </Label>
                                        <Input
                                            id="max-price"
                                            type="number"
                                            value={localPriceRange[1]}
                                            onChange={(e) => {
                                                const value = Number(
                                                    e.target.value
                                                );
                                                setLocalPriceRange([
                                                    localPriceRange[0],
                                                    value,
                                                ]);
                                            }}
                                            className="w-full"
                                        />
                                    </div>
                                </div>
                                <div className="flex items-center justify-between mt-4">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            setLocalPriceRange([
                                                priceRange.min,
                                                priceRange.max,
                                            ])
                                        }
                                        className="text-xs h-8"
                                    >
                                        {t("reset_price", "Reset Price")}
                                    </Button>
                                    <span className="text-xs text-muted-foreground">
                                        {t(
                                            "price_range_from_to",
                                            "Range: {{min}} - {{max}}",
                                            {
                                                min: priceRange.min,
                                                max: priceRange.max,
                                            }
                                        )}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </ScrollArea>

                <SheetFooter className="pt-4 sm:pt-0">
                    <Button onClick={applyFilters} className="w-full">
                        {t("apply_filters", "Apply Filters")}
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    );
}
