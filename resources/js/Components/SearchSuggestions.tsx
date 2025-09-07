import { useI18n } from "@/hooks/use-i18n";
import { useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Image } from "@/Components/ui/Image";
import { Loader2, Search } from "lucide-react";
import { cn } from "@/lib/utils";

interface SearchSuggestion {
    id: number;
    name_en: string;
    name_ar: string;
    slug: string;
    price: number;
    featured_image?: string;
    brand?: {
        id: number;
        name_en: string;
        name_ar: string;
    };
    category?: {
        id: number;
        name_en: string;
        name_ar: string;
    };
}

interface SearchSuggestionsProps {
    query: string;
    onSuggestionClick?: (suggestion: SearchSuggestion) => void;
    isOpen: boolean;
    onClose: () => void;
    className?: string;
}

export function SearchSuggestions({
    query,
    onSuggestionClick,
    isOpen,
    onClose,
    className,
}: SearchSuggestionsProps) {
    const { t, direction, getLocalizedField } = useI18n();
    const [suggestions, setSuggestions] = useState<SearchSuggestion[]>([]);
    const [loading, setLoading] = useState(false);
    const isRTL = direction === "rtl";

    // Helper function to get localized value
    const getLocalizedValue = (obj: any, fieldName: string) => {
        return (
            getLocalizedField(obj, fieldName) ||
            obj[`${fieldName}_en`] ||
            obj[`${fieldName}_ar`] ||
            ""
        );
    };

    useEffect(() => {
        if (!query || query.length < 2) {
            setSuggestions([]);
            return;
        }

        const debounceTimer = setTimeout(async () => {
            setLoading(true);
            try {
                const response = await fetch(
                    `/search/suggestions?q=${encodeURIComponent(query)}`
                );
                const data = await response.json();
                setSuggestions(data.suggestions || []);
            } catch (error) {
                console.error("Error fetching suggestions:", error);
                setSuggestions([]);
            } finally {
                setLoading(false);
            }
        }, 300);

        return () => clearTimeout(debounceTimer);
    }, [query]);

    const handleSuggestionClick = (suggestion: SearchSuggestion) => {
        if (onSuggestionClick) onSuggestionClick(suggestion);
        onClose();
        // Navigate to product page
        router.visit(`/products/${suggestion.id}`);
    };

    const handleViewAllResults = () => {
        router.get("/search", { q: query });
        onClose();
    };

    if (
        !isOpen ||
        (!loading && suggestions.length === 0 && query.length >= 2)
    ) {
        return null;
    }
    return (
        <div
            className={cn(
                "absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-50 max-h-96 overflow-y-auto",
                className
            )}
        >
            {loading ? (
                <div className="flex items-center justify-center py-4">
                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                    <span className="text-sm text-muted-foreground">
                        {t("searching", "Searching...")}
                    </span>
                </div>
            ) : (
                <>
                    {suggestions.length > 0 && (
                        <div className="py-2">
                            {suggestions.map((suggestion) => (
                                <button
                                    key={suggestion.id}
                                    onClick={() =>
                                        handleSuggestionClick(suggestion)
                                    }
                                    className="w-full px-4 py-3 text-start hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-3"
                                >
                                    <Image
                                        src={suggestion.featured_image}
                                        alt={getLocalizedValue(
                                            suggestion,
                                            "name"
                                        )}
                                        className="w-10 h-10 object-cover rounded-md flex-shrink-0"
                                        fallback={
                                            <div className="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-md flex-shrink-0 flex items-center justify-center">
                                                <Search className="h-4 w-4 text-gray-400" />
                                            </div>
                                        }
                                        useDefaultFallback={false}
                                    />
                                    <div className="flex-1 min-w-0">
                                        <div className="font-medium text-sm truncate">
                                            {getLocalizedValue(
                                                suggestion,
                                                "name"
                                            )}
                                        </div>
                                        <div className="text-xs text-muted-foreground truncate">
                                            {suggestion.brand && (
                                                <span>
                                                    {getLocalizedValue(
                                                        suggestion.brand,
                                                        "name"
                                                    )}
                                                </span>
                                            )}
                                            {suggestion.brand &&
                                                suggestion.category &&
                                                " • "}
                                            {suggestion.category && (
                                                <span>
                                                    {getLocalizedValue(
                                                        suggestion.category,
                                                        "name"
                                                    )}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="text-sm font-medium text-primary">
                                        {new Intl.NumberFormat(
                                            isRTL ? "ar-EG" : "en-US",
                                            {
                                                style: "currency",
                                                currency: "EGP",
                                            }
                                        ).format(suggestion.price)}
                                    </div>
                                </button>
                            ))}

                            {query.length >= 2 && (
                                <div className="border-t border-gray-200 dark:border-gray-700 pt-2">
                                    {" "}
                                    <Button
                                        onClick={handleViewAllResults}
                                        variant="ghost"
                                        className="w-full justify-start px-4 py-3 text-sm"
                                    >
                                        <Search className="h-4 w-4 mr-2" />
                                        {t(
                                            "view_all_results_for",
                                            "View all results for '{{query}}'"
                                        ).replace("{{query}}", query)}
                                    </Button>
                                </div>
                            )}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}
