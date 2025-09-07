import AnnouncementBanner from "@/Components/AnnouncementBanner";
import AnimatedBackground from "@/Components/AnimatedBackground";
import BrandGrid from "@/Components/BrandGrid";
import CategoryGrid from "@/Components/CategoryGrid";
import HeroCarousel from "@/Components/HeroCarousel";
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
    const { t, getLocalizedField } = useI18n();
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
                <div className="container">
                    <div className="flex flex-col lg:flex-row items-center justify-between min-h-[calc(100vh-120px)] py-12 lg:py-20">
                        {/* Left Side - Hero Text */}
                        <div className="flex-1 lg:pr-12 text-center lg:text-left mb-12 lg:mb-0 font-mono">
                            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                                Play with new{" "}
                                <span
                                    className="text-transparent bg-clip-text "
                                    style={{
                                        WebkitTextStroke: '2px #10b981',
                                        color: 'transparent'
                                    }}
                                >
                                    electric
                                </span>{" "}
                                Nike products...
                            </h1>

                            <p className="text-lg md:text-xl text-gray-600 mb-8 max-w-lg mx-auto lg:mx-0">
                                Find, explore and buy in an awesome place find, explore and buy in great and awesome place an awesome, explore more.
                            </p>

                            {/* Action Buttons */}
                            <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <button className="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2">
                                    Products
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                </button>

                                <button className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-4 rounded-lg font-semibold text-lg transition-colors duration-200 flex items-center justify-center gap-2">
                                    Shoe blog
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                </button>
                            </div>

                            {/* Question Text */}
                            <div className="mt-8">
                                <p className="text-gray-500 flex items-center justify-center lg:justify-start gap-2">
                                    <span className="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                    Have any question?
                                </p>
                            </div>
                        </div>

                        {/* Right Side - Image */}
                        <div className="flex-1 relative">
                            <div className="relative z-10 text-center">
                                    <img
                                        src={"storage/"+heroSlides[0]?.image || ""}
                                        alt="Nike Electric Shoe"
                                        className="w-full rounded-3xl h-auto  mx-auto transform hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                            <div className="relative hidden bg-emerald-400 rounded-3xl p-8 lg:p-12 mx-auto max-w-lg">
                                {/* Decorative Elements */}
                                <div className="absolute top-4 right-4 w-12 h-12 border-2 border-white/30 rounded-full flex items-center justify-center">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 17L17 7M17 7H8M17 7V16" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                </div>

                                <div className="absolute bottom-8 left-8">
                                    <div className="bg-gray-900 text-white px-4 py-2 rounded-full text-sm font-medium">
                                        Learn more about our service
                                    </div>
                                </div>

                                {/* Shoe Image Placeholder */}
                                <div className="relative z-10 text-center">
                                    <img
                                        src={"storage/"+heroSlides[0]?.image || ""}
                                        alt="Nike Electric Shoe"
                                        className="w-[120%] h-auto max-w-[200%] mx-auto transform hover:scale-105 transition-transform duration-300"
                                    />
                                </div>

                                {/* Floating Elements */}
                                <div className="absolute top-12 left-4 w-8 h-8 bg-white/20 rounded-full animate-pulse"></div>
                                <div className="absolute bottom-20 right-8 w-6 h-6 bg-white/30 rounded-full animate-bounce"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className="container">
                {/* Category Grid */}
                <CategoryGrid
                    categories={activeCategories}
                    title={t("shop_by_category", "Shop by Category")}
                />

                {/* Brand Grid */}
                <BrandGrid
                    brands={activeBrands.slice(0, 12)}
                    title={t("our_brands", "Our Brands")}
                />

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
