import { Button } from "@/Components/ui/button";
import Autoplay from "embla-carousel-autoplay";
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import { Image } from "@/Components/ui/Image";
import { useI18n } from "@/hooks/use-i18n";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Link } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";
import { useState, useEffect, useCallback } from "react";
import type { CarouselApi } from "@/Components/ui/carousel";

interface HeroSlide {
    id: number;
    title_en: string;
    title_ar: string;
    description_en: string;
    description_ar: string;
    image: string;
    cta_link: string;
}

interface HeroCarouselProps {
    heroSlides: HeroSlide[];
    onSlideChange?: (slideIndex: number) => void;
}

export default function HeroCarousel({ heroSlides, onSlideChange }: HeroCarouselProps) {
    const { getLocalizedField, direction } = useI18n();
    const [api, setApi] = useState<CarouselApi>();
    const [current, setCurrent] = useState(0);
    const [animationKey, setAnimationKey] = useState(0);

    const directionIcon =
        direction === "rtl" ? (
            <ChevronLeft className="h-4 w-4" />
        ) : (
            <ChevronRight className="h-4 w-4" />
        );
    const isRtl = direction === "rtl";

    // Handle slide changes
    const handleSlideChange = useCallback(() => {
        if (!api) return;
        const newCurrent = api.selectedScrollSnap();
        setCurrent(newCurrent);
        setAnimationKey((prev) => prev + 1); // Force re-animation

        // Call parent callback if provided
        if (onSlideChange) {
            onSlideChange(newCurrent);
        }
    }, [api, onSlideChange]);

    useEffect(() => {
        if (!api) return;

        // Listen for slide changes
        api.on("select", handleSlideChange);

        return () => {
            api.off("select", handleSlideChange);
        };
    }, [api, handleSlideChange]);

    // Animation variants - Cinematic Reveal Style
    const containerVariants = {
        hidden: { opacity: 0 },
        visible: {
            opacity: 1,
            transition: {
                duration: 0.8,
                staggerChildren: 0.2,
                delayChildren: 0.1,
            },
        },
        exit: {
            opacity: 0,
            x: -100,
            transition: {
                duration: 0.4,
            },
        },
    };

    const slideInVariants = {
        hidden: {
            opacity: 0,
            x: 100,
            rotateY: 15,
            scale: 0.9,
        },
        visible: {
            opacity: 1,
            x: 0,
            rotateY: 0,
            scale: 1,
            transition: {
                duration: 0.7,
                stiffness: 100,
                damping: 15,
            },
        },
        exit: {
            opacity: 0,
            x: -50,
            scale: 0.95,
            transition: {
                duration: 0.3,
            },
        },
    };

    const textVariants = {
        hidden: {
            opacity: 0,
            y: 50,
            scale: 0.8,
        },
        visible: {
            opacity: 1,
            y: 0,
            scale: 1,
            transition: {
                duration: 0.6,
                stiffness: 120,
                damping: 12,
            },
        },
        exit: {
            opacity: 0,
            y: -30,
            scale: 1.1,
            transition: {
                duration: 0.25,
            },
        },
    };

    const buttonVariants = {
        hidden: {
            opacity: 0,
            scale: 0.6,
            rotateX: 90,
        },
        visible: {
            opacity: 1,
            scale: 1,
            rotateX: 0,
            transition: {
                duration: 0.5,
                delay: 0.4,
                stiffness: 200,
                damping: 20,
            },
        },
        exit: {
            opacity: 0,
            scale: 0.8,
            rotateX: -45,
            transition: {
                duration: 0.2,
            },
        },
        hover: {
            scale: 1.1,
            rotateX: -5,
            y: -5,
            transition: {
                duration: 0.2,
                stiffness: 300,
            },
        },
        tap: {
            scale: 0.9,
            rotateX: 5,
        },
    };

    if (!heroSlides || heroSlides.length === 0) {
        return null;
    }

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
        >
            <Carousel
                setApi={setApi}
                className="w-full my-4 force-ltr"
                plugins={[
                    Autoplay({
                        delay: 3000,
                    }),
                ]}
                opts={{ loop: true }}
            >
                <CarouselContent>
                    {heroSlides.map((slide, index) => (
                        <CarouselItem key={slide.id} className="cursor-pointer">
                            <img
                                src={"storage/"+slide.image}
                                alt="Nike Electric Shoe"
                                className="w-full rounded-3xl h-auto "
                            />
                        </CarouselItem>
                    ))}
                </CarouselContent>

                {/* Slide Indicators */}
                <div className="flex justify-center space-x-2 mt-4">
                    {heroSlides.map((_, index) => (
                        <motion.button
                            key={index}
                            className={`w-2 h-2 rounded-full transition-all duration-300 ${
                                index === current
                                    ? "bg-primary w-8 shadow-lg shadow-primary/40"
                                    : "bg-primary/30 hover:bg-primary/60"
                            }`}
                            onClick={() => api?.scrollTo(index)}
                            whileHover={{ scale: 1.3, y: -2 }}
                            whileTap={{ scale: 0.8 }}
                            initial={{ opacity: 0, y: 20, rotateX: 90 }}
                            animate={{
                                opacity: 1,
                                y: 0,
                                rotateX: 0,
                                scale: index === current ? 1.2 : 1,
                            }}
                            transition={{
                                duration: 0.4,
                                delay: index * 0.1,
                            }}
                        />
                    ))}
                </div>
            </Carousel>
        </motion.div>
    );
}
