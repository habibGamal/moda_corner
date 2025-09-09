import { Head } from "@inertiajs/react";
import { useI18n } from "@/hooks/use-i18n";
import { resolveStoragePath } from "@/utils/storageUtils";
import { Card } from "@/Components/ui/card";
import { Image } from "@/Components/ui/Image";
import { Award, ShoppingBag } from "lucide-react";
import { Link } from "@inertiajs/react";
import EmptyState from "@/Components/ui/empty-state";
import { App } from "@/types";

interface BrandsPageProps {
    brands: App.Models.Brand[];
}

export default function Index({ brands }: BrandsPageProps) {
    const { t, getLocalizedField } = useI18n();

    return (
        <div className="container mt-4">
            <Head title={t("brands", "Brands")} />

            <div className="flex items-center gap-2 mb-8">
                <Award className="h-7 w-7 text-primary" />
                <h1 className="text-2xl md:text-3xl font-bold tracking-tight">
                    {t("brands", "Brands")}
                </h1>
            </div>

            {brands && brands.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    {brands.map((brand, index) => {
                        // Different background colors for variety
                        const bgColors = [
                            'from-teal-400 to-cyan-500',
                            'from-blue-500 to-blue-600',
                            'from-sky-400 to-blue-500',
                            'from-indigo-500 to-purple-600',
                            'from-cyan-400 to-teal-500',
                            'from-blue-400 to-indigo-500'
                        ];
                        const bgColor = bgColors[index % bgColors.length];

                        return (
                            <Card
                                key={brand.id}
                                className="group relative aspect-[4/3] transition-all duration-300 hover:shadow-xl overflow-hidden border-none rounded-xl"
                            >
                                <Link
                                    href={`/search?brands[]=${brand.id}`}
                                    className="block h-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-xl"
                                >
                                    <div className={`relative h-full bg-gradient-to-br ${bgColor}`}>
                                        {(brand.display_image || brand.image) ? (
                                            <div className="relative w-full h-full">
                                                <Image
                                                    src={resolveStoragePath(brand.display_image || brand.image) || ''}
                                                    alt={getLocalizedField(
                                                        brand,
                                                        "name"
                                                    )}
                                                    className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105 rounded-xl"
                                                    sizes="(max-width: 640px) 50vw, (max-width: 768px) 50vw, 25vw"
                                                    fallback={
                                                        <div className={`w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} rounded-xl`}>
                                                            <span className="text-sm font-medium text-white">
                                                                {getLocalizedField(
                                                                    brand,
                                                                    "name"
                                                                )}
                                                            </span>
                                                        </div>
                                                    }
                                                />
                                                {/* Gradient overlay from transparent to dark at bottom */}
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl"></div>
                                            </div>
                                        ) : (
                                            <div className={`w-full h-full flex items-center justify-center bg-gradient-to-br ${bgColor} text-white rounded-xl`}>
                                                <span className="text-sm font-medium">
                                                    {getLocalizedField(
                                                        brand,
                                                        "name"
                                                    )}
                                                </span>
                                            </div>
                                        )}

                                        {/* Text overlay at bottom left */}
                                        <div className="absolute bottom-0 left-0 right-0 p-4 text-white">
                                            <div className="space-y-1">
                                                <h3 className="font-bold text-sm leading-tight uppercase tracking-wide">
                                                    {getLocalizedField(
                                                        brand,
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
                    icon={
                        <ShoppingBag className="h-8 w-8 text-muted-foreground" />
                    }
                    title={t("no_brands_available", "No brands available")}
                    description={t(
                        "no_brands_available_description",
                        "There are no brands available at the moment."
                    )}
                />
            )}
        </div>
    );
}
