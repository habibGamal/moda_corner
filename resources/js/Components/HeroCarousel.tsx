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
}

export default function HeroCarousel({ heroSlides }: HeroCarouselProps) {
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
    const onSlideChange = useCallback(() => {
        if (!api) return;
        const newCurrent = api.selectedScrollSnap();
        setCurrent(newCurrent);
        setAnimationKey(prev => prev + 1); // Force re-animation
    }, [api]);

    useEffect(() => {
        if (!api) return;

        // Listen for slide changes
        api.on("select", onSlideChange);

        return () => {
            api.off("select", onSlideChange);
        };
    }, [api, onSlideChange]);

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
            scale: 0.9
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
            scale: 0.8
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
            rotateX: 90
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
                        delay: 5000,
                    }),
                ]}
                opts={{ loop: true }}
            >
            <CarouselContent>
                {heroSlides.map((slide, index) => (
                    <CarouselItem key={slide.id}>
                        <motion.div
                            className="relative overflow-hidden"
                            key={`${slide.id}-${animationKey}`}
                            initial={{ opacity: 0, scale: 1.1 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9 }}
                            transition={{
                                duration: 0.6,
                                delay: index === current ? 0 : 0.1
                            }}
                        >
                            {/* Mobile Layout */}
                            <div className="block md:hidden">
                                <motion.div
                                    className="grid grid-cols-1 gap-6"
                                    key={`mobile-${slide.id}-${animationKey}`}
                                    variants={containerVariants}
                                    initial="hidden"
                                    animate={index === current ? "visible" : "hidden"}
                                >
                                    <motion.div
                                        className="aspect-[16/9] rounded-xl overflow-hidden bg-muted"
                                        variants={slideInVariants}
                                    >
                                        <Image
                                            src={slide.image}
                                            alt={getLocalizedField(
                                                slide,
                                                "title"
                                            )}
                                            className="w-full h-full object-cover transition-all duration-700 hover:scale-110 hover:brightness-110 hover:contrast-110"
                                            fallback={
                                                <div className="w-full h-full flex items-center justify-center text-muted-foreground bg-gradient-to-br from-gray-100 to-gray-200 animate-pulse">
                                                    {getLocalizedField(
                                                        {
                                                            no_image_en:
                                                                "No Image",
                                                            no_image_ar:
                                                                "لا توجد صورة",
                                                        },
                                                        "no_image"
                                                    )}
                                                </div>
                                            }
                                        />
                                    </motion.div>
                                    <motion.div
                                        className="space-y-4 ltr:force-ltr rtl:force-rtl"
                                        variants={slideInVariants}
                                    >
                                        <motion.h1
                                            className="text-3xl font-bold"
                                            variants={textVariants}
                                        >
                                            {getLocalizedField(slide, "title")}
                                        </motion.h1>
                                        <motion.p
                                            className="text-muted-foreground"
                                            variants={textVariants}
                                        >
                                            {getLocalizedField(
                                                slide,
                                                "description"
                                            )}
                                        </motion.p>
                                        <motion.div
                                            className="pt-4"
                                            variants={buttonVariants}
                                        >
                                            <motion.div
                                                variants={buttonVariants}
                                                whileHover="hover"
                                                whileTap="tap"
                                            >
                                                <Button
                                                    size="lg"
                                                    className="font-medium transition-all duration-300 hover:shadow-xl hover:shadow-primary/20"
                                                    asChild
                                                >
                                                    <Link href={slide.cta_link}>
                                                        {getLocalizedField(
                                                            {
                                                                shop_now_en:
                                                                    "Shop Now",
                                                                shop_now_ar:
                                                                    "تسوق الآن",
                                                            },
                                                            "shop_now"
                                                        )}{" "}
                                                        {directionIcon}
                                                    </Link>
                                                </Button>
                                            </motion.div>
                                        </motion.div>
                                    </motion.div>
                                </motion.div>
                            </div>

                            {/* Desktop and Tablet Layout */}
                            <div className="hidden md:block">
                                <motion.div
                                    className="relative aspect-[16/9] overflow-hidden rounded-xl"
                                    key={`desktop-${slide.id}-${animationKey}`}
                                    variants={slideInVariants}
                                    initial="hidden"
                                    animate={index === current ? "visible" : "hidden"}
                                >
                                    <Image
                                        src={slide.image}
                                        alt={getLocalizedField(slide, "title")}
                                        className="w-full h-full object-cover transition-all duration-700 hover:scale-110 hover:brightness-110 hover:contrast-110"
                                        fallback={
                                            <div className="w-full h-full flex items-center justify-center text-muted-foreground bg-gradient-to-br from-gray-100 to-gray-200 animate-pulse">
                                                {getLocalizedField(
                                                    {
                                                        no_image_en: "No Image",
                                                        no_image_ar:
                                                            "لا توجد صورة",
                                                    },
                                                    "no_image"
                                                )}
                                            </div>
                                        }
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent transition-all duration-500 hover:from-black/60 hover:via-black/20" />
                                    <div className="absolute inset-0 top-[30%] flex items-center justify-center">
                                        <motion.div
                                            className="text-center max-w-screen-xl mx-auto px-4 md:px-6 lg:px-8 space-y-3 md:space-y-4 lg:space-y-6 text-white ltr:force-ltr rtl:force-rtl"
                                            key={`content-${slide.id}-${animationKey}`}
                                            variants={containerVariants}
                                            initial="hidden"
                                            animate={index === current ? "visible" : "hidden"}
                                        >
                                            <motion.h1
                                                className="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold leading-tight"
                                                variants={textVariants}
                                            >
                                                {getLocalizedField(
                                                    slide,
                                                    "title"
                                                )}
                                            </motion.h1>
                                            <motion.p
                                                className="text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl mx-auto leading-relaxed"
                                                variants={textVariants}
                                            >
                                                {getLocalizedField(
                                                    slide,
                                                    "description"
                                                )}
                                            </motion.p>
                                            <motion.div
                                                className="pt-2 md:pt-4"
                                                variants={buttonVariants}
                                            >
                                                <motion.div
                                                    variants={buttonVariants}
                                                    whileHover="hover"
                                                    whileTap="tap"
                                                >
                                                    <Button
                                                        size="lg"
                                                        className="font-medium text-sm md:text-base transition-all duration-300 hover:shadow-xl hover:shadow-primary/20"
                                                        asChild
                                                    >
                                                        <Link href={slide.cta_link}>
                                                            {getLocalizedField(
                                                                {
                                                                    shop_now_en:
                                                                        "Shop Now",
                                                                    shop_now_ar:
                                                                        "تسوق الآن",
                                                                },
                                                                "shop_now"
                                                            )}{" "}
                                                            {directionIcon}
                                                        </Link>
                                                    </Button>
                                                </motion.div>
                                            </motion.div>
                                        </motion.div>
                                    </div>
                                </motion.div>
                            </div>
                        </motion.div>
                    </CarouselItem>
                ))}
            </CarouselContent>
            <div className="hidden md:block">
                <CarouselPrevious className="left-6 transition-all duration-300 hover:scale-125 hover:shadow-xl hover:shadow-primary/30 backdrop-blur-sm bg-white/10 border-white/20 hover:bg-white/20" />
                <CarouselNext className="right-6 transition-all duration-300 hover:scale-125 hover:shadow-xl hover:shadow-primary/30 backdrop-blur-sm bg-white/10 border-white/20 hover:bg-white/20" />
            </div>

            {/* Slide Indicators */}
            <div className="flex justify-center space-x-2 mt-4">
                {heroSlides.map((_, index) => (
                    <motion.button
                        key={index}
                        className={`w-2 h-2 rounded-full transition-all duration-300 ${
                            index === current
                                ? 'bg-primary w-8 shadow-lg shadow-primary/40'
                                : 'bg-primary/30 hover:bg-primary/60'
                        }`}
                        onClick={() => api?.scrollTo(index)}
                        whileHover={{ scale: 1.3, y: -2 }}
                        whileTap={{ scale: 0.8 }}
                        initial={{ opacity: 0, y: 20, rotateX: 90 }}
                        animate={{
                            opacity: 1,
                            y: 0,
                            rotateX: 0,
                            scale: index === current ? 1.2 : 1
                        }}
                        transition={{
                            duration: 0.4,
                            delay: index * 0.1
                        }}
                    />
                ))}
            </div>
            </Carousel>
        </motion.div>
    );
}
