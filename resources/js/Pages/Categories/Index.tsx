import { Head, Link } from "@inertiajs/react";
import { useI18n } from "@/hooks/use-i18n";
import { resolveStoragePath } from "@/utils/storageUtils";
import { Card } from "@/Components/ui/card";
import { Layers, FolderX } from "lucide-react";
import { Image } from "@/Components/ui/Image";
import EmptyState from "@/Components/ui/empty-state";
import { cn } from "@/lib/utils";
import { PageTitle } from "@/Components/ui/page-title";
import { App } from "@/types";

interface CategoriesPageProps {
    categories: App.Models.Category[];
}

export default function Index({ categories }: CategoriesPageProps) {
    const { t, getLocalizedField } = useI18n();

    // Function to determine if a category should be featured (larger size)
    const isFeatureCategory = (index: number) => {
        return !(index % 3);
    };

    return (
        <>
            <Head title={t("categories", "Categories")} />

            <PageTitle
                title={t("categories", "Categories")}
                icon={<Layers className="h-7 w-7 text-primary" />}
            />

            {categories && categories.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 auto-rows-[180px]">
                    {categories.map((category, index) => (
                        <Card
                            key={category.id}
                            className={cn(
                                "group relative transition-all duration-300 hover:shadow-lg overflow-hidden border-muted/40 hover:border-primary/30",
                                isFeatureCategory(index) &&
                                    "sm:col-span-2 sm:row-span-2"
                            )}
                        >
                            <Link
                                href={`/search?categories[]=${category.id}`}
                                className="block h-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >
                                <div className="relative h-full bg-background/50 group-hover:bg-muted/5">
                                    {(category.display_image || category.image) ? (
                                        <div className="relative w-full h-full">
                                            <Image
                                                src={resolveStoragePath(category.display_image || category.image) || ''}
                                                alt={getLocalizedField(
                                                    category,
                                                    "name"
                                                )}
                                                className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105"
                                                sizes={
                                                    isFeatureCategory(index)
                                                        ? "(max-width: 640px) 100vw, 66vw"
                                                        : "(max-width: 640px) 50vw, 33vw"
                                                }
                                                fallback={
                                                    <div className="w-full h-full flex items-center justify-center bg-muted/5">
                                                        <span className="text-sm font-medium text-muted-foreground">
                                                            {getLocalizedField(
                                                                category,
                                                                "name"
                                                            )}
                                                        </span>
                                                    </div>
                                                }
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-b from-transparent via-background/10 to-gray-900/90"></div>
                                        </div>
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-muted/5 text-muted-foreground">
                                            <span className="text-sm font-medium">
                                                {getLocalizedField(
                                                    category,
                                                    "name"
                                                )}
                                            </span>
                                        </div>
                                    )}
                                    <div className="absolute bottom-0 left-0 right-0 p-4">
                                        <div className="flex items-center gap-2">
                                            <h3
                                                className={cn(
                                                    "font-medium text-foreground line-clamp-2 text-white",
                                                    isFeatureCategory(index)
                                                        ? "text-lg"
                                                        : "text-sm"
                                                )}
                                            >
                                                {getLocalizedField(
                                                    category,
                                                    "name"
                                                )}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </Card>
                    ))}
                </div>
            ) : (
                <EmptyState
                    icon={<FolderX className="h-8 w-8 text-muted-foreground" />}
                    title={t(
                        "no_categories_available",
                        "No categories available"
                    )}
                    description={t(
                        "no_categories_available_description",
                        "There are no categories available at the moment."
                    )}
                />
            )}
        </>
    );
}
