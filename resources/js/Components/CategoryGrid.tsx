import { Card } from "@/Components/ui/card";
import { Image } from "@/Components/ui/Image";
import { useI18n } from "@/hooks/use-i18n";
import { resolveStoragePath } from "@/utils/storageUtils";
import { FolderX, Layers, ArrowRight, ArrowLeft } from "lucide-react";
import { Link } from "@inertiajs/react";
import EmptyState from "./ui/empty-state";
import { App } from "@/types";

interface CategoryGridProps {
    categories: App.Models.Category[];
    title?: string;
}

export default function CategoryGrid({ categories, title }: CategoryGridProps) {
    const { t, getLocalizedField, direction } = useI18n();

    return (
        <section className="py-12 md:py-16">
            <div className="flex items-center justify-between pb-8">
                <div className="flex items-center gap-2 md:gap-4">
                    <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-primary flex items-center justify-center">
                            <Layers className="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                        </div>
                    <h2 className="text-2xl md:text-3xl font-bold tracking-tight">
                        {title || t("Shop by Category")}
                    </h2>
                </div>
                <Link
                    href="/categories"
                    className="inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors group"
                >
                    {t("view_all", "View All")}
                    {direction === 'rtl' ? (
                        <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    ) : (
                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    )}
                </Link>
            </div>

            {categories && categories.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    {categories.map((category, index) => {
                        // Different background colors for variety (matching the image)
                        const bgColors = [
                            'from-teal-400 to-cyan-500',      // Teal like in the image
                            'from-blue-500 to-blue-600',     // Blue like feeding bottle
                            'from-sky-400 to-blue-500',      // Light blue like bibs
                            'from-indigo-500 to-purple-600', // Purple/indigo variant
                            'from-cyan-400 to-teal-500',     // Cyan variant
                            'from-blue-400 to-indigo-500'    // Blue-indigo variant
                        ];
                        const bgColor = bgColors[index % bgColors.length];

                        return (
                            <Card
                                key={category.id}
                                className="group relative aspect-[4/3] transition-all duration-300 hover:shadow-xl overflow-hidden border-none rounded-xl"
                            >
                                <Link
                                    href={`/search?categories[]=${category.id}`}
                                    className="block h-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-xl"
                                >
                                    <div className={`relative h-full bg-gradient-to-br ${bgColor}`}>
                                        {(category.display_image || category.image) ? (
                                            <div className="relative w-full h-full">
                                                <Image
                                                    src={resolveStoragePath(category.display_image || category.image) || ''}
                                                    alt={getLocalizedField(
                                                        category,
                                                        "name"
                                                    )}
                                                    className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105 rounded-xl"
                                                    sizes="(max-width: 640px) 50vw, (max-width: 768px) 50vw, 25vw"
                                                    fallback={
                                                        <div className={`w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} rounded-xl`}>
                                                            <span className="text-sm font-medium text-white">
                                                                {getLocalizedField(
                                                                    category,
                                                                    "name"
                                                                )}
                                                            </span>
                                                        </div>
                                                    }
                                                />
                                                {/* Gradient overlay from transparent to dark at bottom - matching the image */}
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl"></div>
                                            </div>
                                        ) : (
                                            <div className={`w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} text-white rounded-xl`}>
                                                <span className="text-sm font-medium">
                                                    {getLocalizedField(
                                                        category,
                                                        "name"
                                                    )}
                                                </span>
                                            </div>
                                        )}

                                        {/* Text overlay at bottom left - exactly like in the image */}
                                        <div className="absolute bottom-0 left-0 right-0 p-4 text-white">
                                            <div className="space-y-1">
                                                <h3 className="font-bold text-sm leading-tight uppercase tracking-wide">
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
                        );
                    })}
                </div>
            ) : (
                <EmptyState
                    title={t("No categories available")}
                    icon={<FolderX className="h-8 w-8 text-muted-foreground" />}
                />
            )}
        </section>
    );
}
