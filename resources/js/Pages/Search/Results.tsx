import { Head } from "@inertiajs/react";
import { useI18n } from "@/hooks/use-i18n";
import ProductGrid from "@/Components/ProductGrid";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Grid, Search } from "lucide-react";
import { useState, useRef, useEffect } from "react";
import { router } from "@inertiajs/react";
import { FilterModal } from "@/Components/FilterModal";
import { SortSelector } from "@/Components/SortSelector";
import { SearchSuggestions } from "@/Components/SearchSuggestions";
import { App } from "@/types";

interface SearchResultsProps {
    query: string;
    filters?: App.Types.SearchFilters;
}

// Define default filters to avoid undefined checks throughout the component
const defaultFilters: App.Types.SearchFilters = {
    brands: [],
    categories: [],
    priceRange: { min: 0, max: 1000 },
    selectedBrands: [],
    selectedCategories: [],
    minPrice: null,
    maxPrice: null,
    sortBy: "newest",
};

export default function Results({
    query,
    filters = defaultFilters,
}: SearchResultsProps) {
    const { t } = useI18n();
    const [searchQuery, setSearchQuery] = useState(query || "");
    const [showSuggestions, setShowSuggestions] = useState(false);
    const searchRef = useRef<HTMLDivElement>(null);

    // Close suggestions when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
                setShowSuggestions(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    // Ensure filters is never undefined by merging with defaults
    const safeFilters: App.Types.SearchFilters = { ...defaultFilters, ...filters };
    // Helper functions to handle type conversions for components
    const getBrandsForFilter = safeFilters.brands ?? [];

    const getCategoriesForFilter = safeFilters.categories ?? [];

    const getSelectedBrands = (): string[] => {
        return safeFilters.selectedBrands || [];
    };

    const getSelectedCategories = (): string[] => {
        return safeFilters.selectedCategories || [];
    };

    const getMinPrice = (): number | null => {
        return safeFilters.minPrice !== undefined ? safeFilters.minPrice : null;
    };

    const getMaxPrice = (): number | null => {
        return safeFilters.maxPrice !== undefined ? safeFilters.maxPrice : null;
    };

    const getSortBy = (): string => {
        return safeFilters.sortBy || "newest";
    };

    const getPriceRange = (): App.Types.PriceRange => {
        return safeFilters.priceRange || { min: 0, max: 1000 };
    };

    const hasFiltersApplied = (): boolean => {
        return Boolean(
            (safeFilters.selectedBrands && safeFilters.selectedBrands.length > 0) ||
            (safeFilters.selectedCategories && safeFilters.selectedCategories.length > 0) ||
            safeFilters.minPrice !== null ||
            safeFilters.maxPrice !== null
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.get("/search", {
                q: searchQuery.trim(),
                brands: safeFilters.selectedBrands,
                categories: safeFilters.selectedCategories,
                min_price: safeFilters.minPrice,
                max_price: safeFilters.maxPrice,
                sort_by: safeFilters.sortBy,
            });
        }
    };

    return (
        <>
            <Head title={t("search_results", "Search Results")} />

            <div className="container mt-4">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 className="text-2xl md:text-3xl font-bold mb-2">
                        {t("search_results", "Search Results")}
                    </h1>
                    {query && (
                        <p className="text-muted-foreground">
                            {t("results_for", "Results for '{{query}}'").replace('{{query}}', query)}
                        </p>
                    )}
                </div>
                <div className="w-full md:w-auto">
                    <form
                        onSubmit={handleSearch}
                        className="flex w-full md:w-80"
                    >
                        <div ref={searchRef} className="relative flex-1">
                            <Input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => {
                                    setSearchQuery(e.target.value);
                                    setShowSuggestions(true);
                                }}
                                onFocus={() => setShowSuggestions(true)}
                                placeholder={t(
                                    "search_placeholder",
                                    "Search products..."
                                )}
                                className="w-full pr-8"
                            />
                            <Button
                                type="submit"
                                size="icon"
                                variant="ghost"
                                className="absolute ltr:right-0 rtl:left-0 top-0 h-full"
                            >
                                <Search className="h-4 w-4" />
                            </Button>
                            <SearchSuggestions
                                query={searchQuery}
                                isOpen={showSuggestions}
                                onClose={() => setShowSuggestions(false)}
                                onSuggestionClick={() => setShowSuggestions(false)}
                            />
                        </div>
                    </form>
                </div>
            </div>

            <div className="flex flex-wrap justify-between items-center gap-4 mb-6">
                <div className="flex items-center gap-2">
                    <FilterModal
                        brands={getBrandsForFilter}
                        categories={getCategoriesForFilter}
                        priceRange={getPriceRange()}
                        selectedBrands={getSelectedBrands()}
                        selectedCategories={getSelectedCategories()}
                        minPrice={getMinPrice()}
                        maxPrice={getMaxPrice()}
                        query={query}
                        sortBy={getSortBy()}
                    />
                    <div className="text-sm text-muted-foreground">
                        {hasFiltersApplied() && (
                            <span>
                                {t("filters_applied", "Filters applied")}
                            </span>
                        )}
                    </div>
                </div>
                <SortSelector
                    sortBy={getSortBy()}
                    query={query}
                    selectedBrands={getSelectedBrands()}
                    selectedCategories={getSelectedCategories()}
                    minPrice={getMinPrice()}
                    maxPrice={getMaxPrice()}
                />
            </div>
                <ProductGrid
                    sectionId="search_results_products"
                    emptyMessage={t(
                        "try_different_keywords",
                        "Try different keywords or filters"
                    )}
                    className="pt-0"
                    viewType="grid"
                />
            </div>
        </>
    );
}
