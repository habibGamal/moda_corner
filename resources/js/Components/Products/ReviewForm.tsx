import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { useForm } from "@inertiajs/react";
import { Star } from "lucide-react";
import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Textarea } from "@/Components/ui/textarea";
import { Label } from "@/Components/ui/label";
import InputError from "@/Components/InputError";

interface ReviewFormProps {
    product: App.Models.Product;
    onSubmitted: () => void;
    onCancel: () => void;
    existingReview?: App.Models.ProductReview;
}

export default function ReviewForm({
    product,
    onSubmitted,
    onCancel,
    existingReview,
}: ReviewFormProps) {
    const { t } = useI18n();
    const [hoveredRating, setHoveredRating] = useState<number | null>(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        product_id: product.id,
        rating: existingReview?.rating || 0,
        comment: existingReview?.comment || "",
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (data.rating === 0) {
            return;
        }

        if (existingReview) {
            put(`/reviews/${existingReview.id}`, {
                onSuccess: () => {
                    onSubmitted();
                    reset();
                },
            });
        } else {
            post(`/products/${product.id}/reviews`, {
                onSuccess: () => {
                    onSubmitted();
                    reset();
                },
            });
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div>
                <Label>
                    {t("rating", "Rating")}{" "}
                    <span className="text-destructive">*</span>
                </Label>
                <div className="flex items-center gap-2 mt-2">
                    {[1, 2, 3, 4, 5].map((star) => (
                        <button
                            key={star}
                            type="button"
                            onClick={() => setData("rating", star)}
                            onMouseEnter={() => setHoveredRating(star)}
                            onMouseLeave={() => setHoveredRating(null)}
                            className="transition-transform hover:scale-110"
                        >
                            <Star
                                className={`h-8 w-8 ${
                                    star <=
                                    (hoveredRating || data.rating)
                                        ? "fill-yellow-400 text-yellow-400"
                                        : "text-gray-300 dark:text-gray-600"
                                }`}
                            />
                        </button>
                    ))}
                    <span className="ml-2 text-sm text-muted-foreground">
                        {data.rating > 0 &&
                            `${data.rating} ${
                                data.rating === 1
                                    ? t("star", "star")
                                    : t("stars", "stars")
                            }`}
                    </span>
                </div>
                {errors.rating && (
                    <InputError message={errors.rating} className="mt-2" />
                )}
            </div>

            <div>
                <Label htmlFor="comment">
                    {t("review_comment_optional", "Comment (optional)")}
                </Label>
                <Textarea
                    id="comment"
                    value={data.comment}
                    onChange={(e) => setData("comment", e.target.value)}
                    placeholder={t(
                        "review_comment_placeholder",
                        "Share your thoughts about this product..."
                    )}
                    rows={4}
                    className="mt-2"
                    maxLength={1000}
                />
                <div className="text-xs text-muted-foreground text-right mt-1">
                    {data.comment.length}/1000
                </div>
                {errors.comment && (
                    <InputError message={errors.comment} className="mt-2" />
                )}
            </div>

            <div className="flex gap-3">
                <Button type="submit" disabled={processing || data.rating === 0}>
                    {processing
                        ? existingReview
                            ? t("updating_review", "Updating...")
                            : t("submitting_review", "Submitting...")
                        : existingReview
                        ? t("update_review", "Update Review")
                        : t("submit_review", "Submit Review")}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onCancel}
                    disabled={processing}
                >
                    {t("cancel", "Cancel")}
                </Button>
            </div>
        </form>
    );
}
