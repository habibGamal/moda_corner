import { useI18n } from "@/hooks/use-i18n";
import { Star } from "lucide-react";
import { Progress } from "@/Components/ui/progress";

interface ReviewStatsProps {
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

export default function ReviewStats({ stats }: ReviewStatsProps) {
    const { t } = useI18n();

    const getRatingPercentage = (count: number) => {
        if (stats.total_reviews === 0) return 0;
        return (count / stats.total_reviews) * 100;
    };

    const averageRating = stats.average_rating || 0;

    return (
        <div className="mb-8 p-6 border rounded-lg bg-card">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Average Rating */}
                <div className="flex flex-col items-center justify-center">
                    <div className="text-5xl font-bold mb-2">
                        {Number(averageRating).toFixed(1)}
                    </div>
                    <div className="flex items-center mb-2">
                        {Array.from({ length: 5 }).map((_, index) => (
                            <Star
                                key={index}
                                className={`h-5 w-5 ${
                                    index < Math.round(averageRating)
                                        ? "fill-yellow-400 text-yellow-400"
                                        : "text-gray-300 dark:text-gray-600"
                                }`}
                            />
                        ))}
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {t("average_rating", "Average Rating")}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        {stats.total_reviews}{" "}
                        {stats.total_reviews === 1
                            ? t("reviews", "review")
                            : t("total_reviews", "reviews")}
                    </p>
                </div>

                {/* Rating Breakdown */}
                <div className="space-y-3">
                    {[5, 4, 3, 2, 1].map((rating) => (
                        <div
                            key={rating}
                            className="flex items-center gap-3"
                        >
                            <div className="flex items-center gap-1 w-16">
                                <span className="text-sm font-medium">
                                    {rating}
                                </span>
                                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                            </div>
                            <Progress
                                value={getRatingPercentage(
                                    stats.rating_breakdown[
                                        rating as keyof typeof stats.rating_breakdown
                                    ]
                                )}
                                className="flex-1 h-2"
                            />
                            <span className="text-sm text-muted-foreground w-12 text-right">
                                {
                                    stats.rating_breakdown[
                                        rating as keyof typeof stats.rating_breakdown
                                    ]
                                }
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
