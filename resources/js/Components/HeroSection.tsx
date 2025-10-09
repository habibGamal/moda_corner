import AnnouncementBanner from "@/Components/AnnouncementBanner";
import AnimatedBackground from "@/Components/AnimatedBackground";
import HeroCarousel from "@/Components/HeroCarousel";
import { Button } from "@/Components/ui/button";
import { useI18n } from "@/hooks/use-i18n";
import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "@inertiajs/react";
import { Star, Tag } from "lucide-react";

interface HeroSlide {
    id: number;
    title_en: string;
    title_ar: string;
    description_en: string;
    description_ar: string;
    image: string;
    cta_link: string;
}

interface HeroSectionProps {
    announcements: { id: number; title_en: string; title_ar: string }[];
    heroSlides: HeroSlide[];
}

export default function HeroSection({ announcements, heroSlides }: HeroSectionProps) {
    const { t, getLocalizedField, direction } = useI18n();
    const [currentSlideIndex, setCurrentSlideIndex] = useState(0);

    // Get current slide data
    const currentSlide = heroSlides[currentSlideIndex] || heroSlides[0];

    const handleSlideChange = (slideIndex: number) => {
        setCurrentSlideIndex(slideIndex);
    };

    return (
        <div className="relative bg-[#fdfbf7] dark:bg-background overflow-hidden min-h-[calc(100vh-65px)]">
            {/* Animated Background Shapes */}
            <AnimatedBackground />

            {/* Content */}
            <div className="container pt-8">
                <AnnouncementBanner announcements={announcements} />
                <div className="flex flex-col lg:flex-row items-center justify-between min-h-[calc(100vh-120px)] py-12 lg:py-16">
                    {/* Left Side - Hero Text */}
                    <div className="flex-1 ltr:lg:pr-12 rtl:lg:pl-12 text-center ltr:lg:text-left rtl:lg:text-right mb-12 lg:mb-0 ">
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={currentSlideIndex}
                                initial={{ opacity: 0, y: 30, scale: 0.9 }}
                                animate={{ opacity: 1, y: 0, scale: 1 }}
                                exit={{ opacity: 0, y: -30, scale: 0.9 }}
                                transition={{ duration: 0.6 }}
                                className="space-y-6"
                            >
                                {/* Dynamic Title from heroSlides */}
                                <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                                    {currentSlide ? getLocalizedField(currentSlide, "title") : (
                                        <>
                                            {t("hero_title_part1")}{" "}
                                            <span
                                                className="text-transparent bg-clip-text "
                                                style={{
                                                    WebkitTextStroke: "2px rgb(16 185 129)",
                                                    color: "transparent",
                                                }}
                                            >
                                                {t("hero_title_electric")}
                                            </span>{" "}
                                            {t("hero_title_part2")}
                                        </>
                                    )}
                                </h1>

                                {/* Dynamic Description from heroSlides */}
                                <p className="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-lg mx-auto ltr:lg:mx-0 ltr:lg:mr-auto rtl:lg:mx-0 rtl:lg:ml-auto">
                                    {currentSlide ? getLocalizedField(currentSlide, "description") : t("hero_description")}
                                </p>
                            </motion.div>
                        </AnimatePresence>

                        {/* Action Buttons */}
                        <div className="flex flex-col sm:flex-row gap-4 justify-center ltr:lg:justify-start rtl:lg:justify-end mt-8">
                            <Button
                                asChild
                                size="lg"
                                className="bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 text-lg font-semibold"
                            >
                                <Link href="/sections/4" className="flex items-center gap-2">
                                    <Star className="h-5 w-5" />
                                    {t("best_sellers")}
                                </Link>
                            </Button>

                            <Button
                                asChild
                                variant="secondary"
                                size="lg"
                                className="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 px-8 py-4 text-lg font-semibold"
                            >
                                <Link href="/sections/2" className="flex items-center gap-2">
                                    <Tag className="h-5 w-5" />
                                    {t("special_offers")}
                                </Link>
                            </Button>
                        </div>

                        {/* Question Text */}
                        <div className="mt-8">
                            <p className="text-gray-500 dark:text-gray-400 flex items-center justify-center ltr:lg:justify-start rtl:lg:justify-end gap-2">
                                <span className="w-2 h-2 bg-primary-400 dark:bg-primary-500 rounded-full"></span>
                                {t("hero_question")}
                            </p>
                        </div>
                    </div>

                    {/* Right Side - Image */}
                    <div className="flex-1 relative ltr:lg:pl-12 rtl:lg:pr-12">
                        <div className="relative z-10 text-center">
                            <HeroCarousel heroSlides={heroSlides} onSlideChange={handleSlideChange} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
