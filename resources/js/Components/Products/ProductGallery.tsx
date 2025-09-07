import { useState } from "react";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { cn } from "@/lib/utils";
import { Image } from "@/Components/ui/Image";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/Components/ui/button";

interface ProductGalleryProps {
    product: App.Models.Product;
    selectedVariant?: App.Models.ProductVariant;
}

export default function ProductGallery({
    product,
    selectedVariant,
}: ProductGalleryProps) {
    const { getLocalizedField } = useI18n();

    // Get images from product or selected variant
    const images = selectedVariant?.images || product.all_images || [];
    const [currentImageIndex, setCurrentImageIndex] = useState(0);

    // If there are no images, show a placeholder
    if (images.length === 0) {
        return (
            <div className="aspect-square relative bg-muted rounded-md flex items-center justify-center">
                <Image
                    src="/placeholder.jpg"
                    alt={getLocalizedField(product, "name")}
                    className="object-contain w-full h-full"
                />
            </div>
        );
    }

    const prevImage = () => {
        setCurrentImageIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1));
    };

    const nextImage = () => {
        setCurrentImageIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1));
    };

    const goToImage = (index: number) => {
        setCurrentImageIndex(index);
    };

    return (
        <div className="space-y-4">
            <div className="aspect-square relative bg-muted rounded-md overflow-hidden">
                <Image
                    src={images[currentImageIndex]}
                    alt={getLocalizedField(product, "name")}
                    className="object-contain w-full h-full"
                />

                {/* Navigation arrows for multiple images */}
                {images.length > 1 && (
                    <>
                        <Button
                            variant="outline"
                            size="icon"
                            className="absolute left-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full opacity-70 hover:opacity-100"
                            onClick={prevImage}
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            className="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full opacity-70 hover:opacity-100"
                            onClick={nextImage}
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </>
                )}
            </div>                {/* Thumbnail navigation for multiple images */}
            {images.length > 1 && (
                <div className="flex flex-wrap gap-2 justify-center">
                    {images.map((image: string, index: number) => (
                        <button
                            key={index}
                            className={cn(
                                "w-16 h-16 rounded border overflow-hidden",
                                index === currentImageIndex
                                    ? "border-primary ring-2 ring-primary ring-offset-2"
                                    : "border-muted-foreground/20"
                            )}
                            onClick={() => goToImage(index)}
                        >
                            <Image
                                src={image}
                                alt={`${getLocalizedField(product, "name")} - ${index + 1}`}
                                className="object-cover w-full h-full"
                            />
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
