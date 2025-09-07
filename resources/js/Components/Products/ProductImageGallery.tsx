import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/Components/ui/carousel';
import { Image } from '@/Components/ui/Image';

interface ProductImageGalleryProps {
  images: string[];
  productName: string;
}

export default function ProductImageGallery({ images, productName }: ProductImageGalleryProps) {

  return (
    <div>
    <Carousel className="w-full">
      <CarouselContent>
        {images.map((image, index) => (
        <CarouselItem key={index}>
          <div className="aspect-square bg-muted rounded-lg overflow-hidden">
            <Image
            src={image}
            alt={`${productName} - ${index + 1}`}
            className="w-full h-full object-cover"
            />
          </div>
        </CarouselItem>
        ))}
      </CarouselContent>
      <CarouselPrevious className="left-6" />
      <CarouselNext className="right-6" />
    </Carousel>

      <div className="flex justify-center mt-4 gap-3">
        {images.map((image, index) => (
          <div
            key={index}
            className="w-16 h-16 rounded-md overflow-hidden border cursor-pointer hover:border-primary transition-all"
          >
            <Image
              src={image}
              alt={`Thumbnail ${index + 1}`}
              className="w-full h-full object-cover"
            />
          </div>
        ))}
      </div>
    </div>
  );
}
