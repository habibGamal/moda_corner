import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { useI18n } from "@/hooks/use-i18n";
import { Star } from "lucide-react";
import EmptyState from "../ui/empty-state";
import { App } from "@/types";

interface ProductTabsProps {
    product: App.Models.Product;
}

export default function ProductTabs({ product }: ProductTabsProps) {
    const { t, getLocalizedField, direction } = useI18n();

    return (
        <Tabs defaultValue="specifications" dir={direction}>
            <TabsList className="grid w-full grid-cols-2">
                <TabsTrigger value="specifications">
                    {t("specifications", "Specifications")}
                </TabsTrigger>
                <TabsTrigger value="reviews">
                    {t("reviews", "Reviews")}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="specifications" className="py-4">
                Specs
            </TabsContent>

            <TabsContent value="reviews" className="py-4">
                {product.reviews && product.reviews.length > 0 ? (
                    <div className="space-y-6">
                        {product.reviews.map((review: App.Models.ProductReview, index: number) => (
                            <div
                                key={index}
                                className="border-b pb-4 last:border-0"
                            >
                                <div className="flex justify-between mb-2">
                                    <div>
                                        <p className="font-medium">
                                            {review.user?.name}
                                        </p>
                                        <div className="flex items-center text-yellow-400">
                                            {[...Array(5)].map((_, i) => (
                                                <Star
                                                    key={i}
                                                    className={`h-4 w-4 ${
                                                        i < review.rating
                                                            ? "fill-current"
                                                            : ""
                                                    }`}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {new Date(
                                            review.created_at
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                                <p>{review.comment}</p>
                            </div>
                        ))}
                    </div>
                ) : (
                    <EmptyState
                        icon={<Star className="text-yellow-400" />}
                        title={t("no_reviews", "No reviews available")}
                        description={t(
                            "be_first_to_review",
                            "Be the first to review this product!"
                        )}
                    />
                )}
            </TabsContent>
        </Tabs>
    );
}
