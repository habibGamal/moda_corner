import AnnouncementBanner from "@/Components/AnnouncementBanner";
import AnimatedBackground from "@/Components/AnimatedBackground";
import BrandGrid from "@/Components/BrandGrid";
import CategoryGrid from "@/Components/CategoryGrid";
import HeroCarousel from "@/Components/HeroCarousel";
import ProductGrid from "@/Components/ProductGrid";
import { useI18n } from "@/hooks/use-i18n";
import { useSiteBranding } from "@/hooks/useSettings";
import { App } from "@/types";
import { Head, Link } from "@inertiajs/react";

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

    // Filter active categories and brands
    const activeCategories = categories?.filter((cat) => cat.is_active) || [];
    const activeBrands = brands?.filter((brand) => brand.is_active) || [];
    console.log(categories);

    return (
        <>
            <Head title={title} />

            {/* Hero Section with Background */}
            <div className="relative bg-[#fdfbf7] overflow-hidden min-h-[calc(100vh-65px)]">
                {/* Animated Background Shapes */}
                <AnimatedBackground />

                {/* Content */}
                <div className="container pt-8">
                    <AnnouncementBanner announcements={announcements} />
                    <div className="flex gap-8 flex-col lg:flex-row ltr:lg:flex-row rtl:lg:flex-row-reverse items-center justify-between min-h-[calc(100vh-120px)] py-12 lg:py-16">
                        {/* Hero Text */}
                        <div className="flex-1 ltr:lg:pr-12 rtl:lg:pl-12 text-center ltr:lg:text-left rtl:lg:text-right mb-12 lg:mb-0 font-mono">
                            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                                {t("hero_title_part_1", "Play with new")}{" "}
                                <span
                                    className="text-transparent bg-clip-text "
                                    style={{
                                        WebkitTextStroke: "2px #10b981",
                                        color: "transparent",
                                    }}
                                >
                                    {t("hero_title_electric", "electric")}
                                </span>{" "}
                                {t("hero_title_part_2", "Nike products...")}
                            </h1>

                            <p className="text-lg md:text-xl text-gray-600 mb-8 max-w-lg mx-auto ltr:lg:ml-0 ltr:lg:mr-auto rtl:lg:mr-0 rtl:lg:ml-auto">
                                {t("hero_description", "Find, explore and buy in an awesome place find, explore and buy in great and awesome place an awesome, explore more.")}
                            </p>

                            {/* Action Buttons */}
                            <div className="flex flex-col sm:flex-row gap-4 justify-center ltr:lg:justify-start rtl:lg:justify-end">
                                <Link
                                    href="/products"
                                    className="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2"
                                >
                                    {t("products", "Products")}
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="rtl:rotate-180"
                                    >
                                        <path
                                            d="M5 12H19M19 12L12 5M19 12L12 19"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                </Link>

                                <Link
                                    href="/contact"
                                    className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2"
                                >
                                    {t("contact", "Contact")}
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="rtl:rotate-180"
                                    >
                                        <path
                                            d="M5 12H19M19 12L12 5M19 12L12 19"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                </Link>
                            </div>

                            {/* Question Text */}
                            <div className="mt-8">
                                <p className="text-gray-500 flex items-center justify-center ltr:lg:justify-start rtl:lg:justify-end gap-2">
                                    <span className="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                    {t("have_any_question", "Have any question?")}
                                </p>
                            </div>
                        </div>

                        {/* Hero Image */}
                        <div className="flex-1 relative">
                            <HeroCarousel heroSlides={heroSlides} />
                        </div>
                    </div>
                </div>
            </div>
            <div className="container">
                {/* Category Grid */}
                {/* <CategoryGrid
                    categories={activeCategories}
                    title={t("shop_by_category", "Shop by Category")}
                /> */}

                {/* Brand Grid */}
                {/* <BrandGrid
                    brands={activeBrands.slice(0, 12)}
                    title={t("our_brands", "Our Brands")}
                /> */}

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
        </>
    );
}
