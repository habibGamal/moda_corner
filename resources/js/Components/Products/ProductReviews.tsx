import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Star } from "lucide-react";
import { useState, useEffect } from "react";
import { Button } from "@/Components/ui/button";
import ReviewItem from "./ReviewItem";
import ReviewStats from "./ReviewStats";
import EmptyState from "@/Components/EmptyState";
import ReviewForm from "./ReviewForm";

interface ProductReviewsProps {
    product: App.Models.Product;
    auth: {
        user: App.Models.User | null;
    };
}

interface ReviewsData {
    reviews: {
        data: App.Models.ProductReview[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        average_rating: number;
        total_reviews: number;
        rating_breakdown: {
            5: number;
            4: number;
            3: number;
            2: number;
            1: number;
        };
    };
}

interface CanReviewData {
    can_review: boolean;
    has_reviewed: boolean;
    is_verified_purchase: boolean;
}

export default function ProductReviews({ product, auth }: ProductReviewsProps) {
    const { t } = useI18n();
    const [reviewsData, setReviewsData] = useState<ReviewsData | null>(null);
    const [canReviewData, setCanReviewData] = useState<CanReviewData | null>(
        null
    );
    const [loading, setLoading] = useState(true);
    const [showReviewForm, setShowReviewForm] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);

    useEffect(() => {
        fetchReviews();
        if (auth.user) {
            checkCanReview();
        }
    }, [product.id, auth.user]);

    const fetchReviews = async (page: number = 1) => {
        try {
            setLoading(page === 1);
            setLoadingMore(page > 1);
            const response = await fetch(
                `/products/${product.id}/reviews?page=${page}`
            );
            const data = await response.json();

            if (page === 1) {
                setReviewsData(data);
            } else {
                setReviewsData((prev) => {
                    if (!prev) return data;
                    return {
                        ...data,
                        reviews: {
                            ...data.reviews,
                            data: [...prev.reviews.data, ...data.reviews.data],
                        },
                    };
                });
            }
        } catch (error) {
            console.error("Error fetching reviews:", error);
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    };

    const checkCanReview = async () => {
        try {
            const response = await fetch(
                `/products/${product.id}/reviews/can-review`
            );
            const data = await response.json();
            setCanReviewData(data);
        } catch (error) {
            console.error("Error checking review status:", error);
        }
    };

    const handleReviewSubmitted = () => {
        setShowReviewForm(false);
        fetchReviews();
        if (auth.user) {
            checkCanReview();
        }
    };

    const loadMore = () => {
        if (
            reviewsData &&
            reviewsData.reviews.current_page < reviewsData.reviews.last_page
        ) {
            fetchReviews(reviewsData.reviews.current_page + 1);
        }
    };

    if (loading) {
        return (
            <div className="py-8">
                <div className="animate-pulse space-y-4">
                    <div className="h-8 bg-muted rounded w-1/4"></div>
                    <div className="h-20 bg-muted rounded"></div>
                    <div className="h-20 bg-muted rounded"></div>
                </div>
            </div>
        );
    }

    const hasReviews = reviewsData && reviewsData.reviews.data.length > 0;

    return (
        <div className="py-8 border-t">
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold flex items-center gap-2">
                    <Star className="h-6 w-6 text-yellow-500" />
                    {t("customer_reviews", "Customer Reviews")}
                </h2>
                {auth.user && canReviewData?.can_review && (
                    <Button onClick={() => setShowReviewForm(!showReviewForm)}>
                        {showReviewForm
                            ? t("cancel", "Cancel")
                            : t("write_a_review", "Write a Review")}
                    </Button>
                )}
            </div>

            {/* Review Stats */}
            {hasReviews && reviewsData.stats && (
                <ReviewStats stats={reviewsData.stats} />
            )}

            {/* Review Form */}
            {showReviewForm && auth.user && (
                <div className="mb-8 p-6 border rounded-lg bg-card">
                    <ReviewForm
                        product={product}
                        onSubmitted={handleReviewSubmitted}
                        onCancel={() => setShowReviewForm(false)}
                    />
                </div>
            )}

            {/* Login prompt for guests */}
            {!auth.user && (
                <div className="mb-8 p-6 border rounded-lg bg-muted/50">
                    <p className="text-center text-muted-foreground">
                        {t(
                            "login_to_review",
                            "Please log in to write a review"
                        )}
                    </p>
                </div>
            )}

            {/* Already reviewed message */}
            {auth.user && canReviewData?.has_reviewed && !showReviewForm && (
                <div className="mb-8 p-4 border rounded-lg bg-blue-50 dark:bg-blue-950/30">
                    <p className="text-sm text-blue-600 dark:text-blue-400">
                        {t(
                            "already_reviewed",
                            "You have already reviewed this product."
                        )}
                    </p>
                </div>
            )}

            {/* Reviews List */}
            {hasReviews ? (
                <div className="space-y-6">
                    {reviewsData.reviews.data.map((review) => (
                        <ReviewItem
                            key={review.id}
                            review={review}
                            currentUserId={auth.user?.id}
                            onUpdated={handleReviewSubmitted}
                        />
                    ))}

                    {/* Load More Button */}
                    {reviewsData.reviews.current_page <
                        reviewsData.reviews.last_page && (
                        <div className="flex justify-center pt-6">
                            <Button
                                variant="outline"
                                onClick={loadMore}
                                disabled={loadingMore}
                            >
                                {loadingMore
                                    ? t("loading", "Loading...")
                                    : t(
                                          "show_more_reviews",
                                          "Show More Reviews"
                                      )}
                            </Button>
                        </div>
                    )}
                </div>
            ) : (
                <EmptyState
                    icon={<Star className="h-10 w-10 text-muted-foreground" />}
                    title={t("no_reviews", "No reviews yet.")}
                    description={t(
                        "be_first_to_review",
                        "Be the first to review this product!"
                    )}
                    action={
                        auth.user && canReviewData?.can_review
                            ? {
                                  label: t("write_a_review", "Write a Review"),
                                  onClick: () => setShowReviewForm(true),
                              }
                            : undefined
                    }
                />
            )}
        </div>
    );
}
