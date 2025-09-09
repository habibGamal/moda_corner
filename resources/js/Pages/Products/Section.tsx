import ProductGrid from "@/Components/ProductGrid";
import { PageTitle } from "@/Components/ui/page-title";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Head } from "@inertiajs/react";
import { Layers } from "lucide-react";

export default function SectionPage({
    section,
}: {
    section: App.Models.Section;
}) {
    const { getLocalizedField, t, direction } = useI18n();

    return (
        <>
            <Head title={getLocalizedField(section, "title")} />
            <div className="container mt-4">
                <PageTitle
                    title={getLocalizedField(section, "title")!}
                    icon={<Layers className="h-6 w-6" />}
                />
                <ProductGrid
                    key={section.id}
                    sectionId={section.id}
                    className="!pt-0"
                    viewType="grid"
                    emptyMessage={t(
                        "no_products_available",
                        "No products available in this section"
                    )}
                />
            </div>
        </>
    );
}
