import { useI18n } from "@/hooks/use-i18n";
import { ArrowRight, ArrowLeft, LayoutGrid } from "lucide-react";
import { Link, WhenVisible, usePage } from "@inertiajs/react";
import { Skeleton } from "@/Components/ui/skeleton";
import { cn } from "@/lib/utils";
import EmptyState from "./ui/empty-state";
import { ReactNode } from "react";

interface ItemGridProps<T = any> {
    title?: string | null;
    viewAllLink?: string | null;
    emptyMessage?: string;
    className?: string;
    sectionId: string | number;
    dataKey?: string;
    paginationKey?: string;
    viewType?: "scroll" | "grid";
    scrollDirection?: "horizontal" | "vertical";
    renderItem: (item: T) => ReactNode;
    icon?: ReactNode;
    itemWidth?: string;
    itemHeight?: string;
    gridCols?: {
        default?: string;
        sm?: string;
        md?: string;
        lg?: string;
        xl?: string;
    };
}

export default function ItemGrid<T = any>({
    title,
    viewAllLink,
    emptyMessage,
    className = "",
    sectionId,
    paginationKey,
    dataKey,
    viewType = "scroll",
    scrollDirection = "horizontal",
    renderItem,
    icon = <LayoutGrid className="h-6 w-6 text-primary" />,
    itemWidth = "w-[250px] sm:w-[300px]",
    itemHeight,
    gridCols = {
        default: "grid-cols-2",
        sm: "sm:grid-cols-3",
        lg: "lg:grid-cols-4",
        xl: "xl:grid-cols-5",
    },
}: ItemGridProps<T>) {
    const { t, direction } = useI18n();
    const page = usePage();

    // Calculate section-based keys
    const sectionKey = `section_${sectionId}_items_page`;
    const actualDataKey = dataKey || `${sectionKey}_data`;
    const actualPaginationKey = paginationKey || `${sectionKey}_pagination`;

    // Get data from page props
    const items = page.props[actualDataKey] as T[] | undefined;
    const pagination = page.props[actualPaginationKey] as any;

    const ItemCardSkeleton = () => (
        <div
            className={cn(
                "space-y-3",
                viewType === "scroll"
                    ? scrollDirection === "horizontal"
                        ? `snap-start flex-shrink-0 ${itemWidth}`
                        : `snap-start w-full ${itemHeight || "h-auto"}`
                    : "w-full"
            )}
        >
            <Skeleton className="w-full h-[180px] rounded-lg" />
            <Skeleton className="w-3/4 h-5 rounded" />
            <Skeleton className="w-1/2 h-4 rounded" />
            <Skeleton className="w-1/4 h-6 rounded" />
        </div>
    );

    return (
        <section className={cn(`py-12 md:py-16`, className)}>
            {(title || viewAllLink) && (
                <div className="flex items-center justify-between pb-8">
                    {title && (
                        <div className="flex items-center gap-2">
                            {icon}
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
                            {direction === 'rtl' ? (
                                <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                            ) : (
                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            )}
                        </Link>
                    )}
                </div>
            )}

            {items && items.length > 0 ? (
                <div
                    className={cn(
                        viewType === "scroll"
                            ? scrollDirection === "horizontal"
                                ? "flex overflow-x-auto pb-4 gap-4 snap-x scrollbar-hide"
                                : "flex flex-col pb-4 gap-4 snap-y scrollbar-hide max-h-[70vh]"
                            : `grid ${gridCols.default} ${gridCols.sm} ${gridCols.lg} ${gridCols.xl} gap-4 md:gap-6`
                    )}
                >
                    {items.map((item, index) => (
                        <div
                            key={index}
                            className={cn(
                                viewType === "scroll"
                                    ? scrollDirection === "horizontal"
                                        ? `snap-start flex-shrink-0 ${itemWidth}`
                                        : `snap-start ${itemHeight || "h-auto"}`
                                    : ""
                            )}
                        >
                            {renderItem(item)}
                        </div>
                    ))}

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
                            fallback={<ItemCardSkeleton />}
                        >
                            <ItemCardSkeleton />
                        </WhenVisible>
                    )}
                </div>
            ) : (
                <EmptyState
                    title={
                        emptyMessage ||
                        t("no_items_available", "No items available")
                    }
                    icon={icon}
                />
            )}
        </section>
    );
}
