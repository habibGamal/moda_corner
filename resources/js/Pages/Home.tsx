import BrandGrid from "@/Components/BrandGrid";
import CategoryGrid from "@/Components/CategoryGrid";
import HeroSection from "@/Components/HeroSection";
import ProductGrid from "@/Components/ProductGrid";
import { useI18n } from "@/hooks/use-i18n";
import { useSiteBranding } from "@/hooks/useSettings";
import { App } from "@/types";
import { Head } from "@inertiajs/react";

interface HomePageProps extends App.Interfaces.AppPageProps {
    announcements: { id: number; title_en: string; title_ar: string }[];
    heroSlides: {
        id: number;
        title_en: string;
        title_ar: string;
        description_en: string;
        description_ar: string;
        image: string;
        cta_link: string;
    }[];
    sections?: App.Models.Section[];
}

export default function Home({
    announcements,
    heroSlides,
    categories,
    brands,
    sections,
}: HomePageProps) {
    const { t, getLocalizedField, direction } = useI18n();
    const { title } = useSiteBranding();

    return (
        <div className={`${direction === 'rtl' ? 'rtl' : 'ltr'}`}>
            <Head title={title} />

            {/* Hero Section */}
            <HeroSection announcements={announcements} heroSlides={heroSlides} />

            {/* Sections Content */}
            <div className="container mt-8 space-y-8">
                {sections &&
                    sections.map((section) => (
                        <ProductGrid
                            key={section.id}
                            sectionId={section.id}
                            title={getLocalizedField(section, "title")}
                            viewAllLink={`/sections/${section.id}`}
                            emptyMessage={t(
                                "no_products_available",
                                "No products available in this section"
                            )}
                        />
                    ))}
            </div>
        </div>
    );
}
