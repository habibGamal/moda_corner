import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Star, Trash2, Edit } from "lucide-react";
import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { router } from "@inertiajs/react";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import ReviewForm from "./ReviewForm";

interface ReviewItemProps {
    review: App.Models.ProductReview;
    currentUserId?: number;
    onUpdated: () => void;
}

export default function ReviewItem({
    review,
    currentUserId,
    onUpdated,
}: ReviewItemProps) {
    const { t } = useI18n();
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const [showEditForm, setShowEditForm] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const isOwner = currentUserId === review.user_id;

    const handleDelete = () => {
        setDeleting(true);
        router.delete(`/reviews/${review.id}`, {
            onSuccess: () => {
                setShowDeleteDialog(false);
                onUpdated();
            },
            onFinish: () => setDeleting(false),
        });
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    };

    if (showEditForm) {
        return (
            <div className="p-6 border rounded-lg bg-card">
                <h3 className="text-lg font-semibold mb-4">
                    {t("edit_review", "Edit Review")}
                </h3>
                <ReviewForm
                    product={{ id: review.product_id } as App.Models.Product}
                    onSubmitted={() => {
                        setShowEditForm(false);
                        onUpdated();
                    }}
                    onCancel={() => setShowEditForm(false)}
                    existingReview={review}
                />
            </div>
        );
    }

    return (
        <>
            <div className="p-6 border rounded-lg bg-card hover:shadow-md transition-shadow">
                <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center gap-3">
                        {review.user.avatar ? (
                            <img
                                src={review.user.avatar}
                                alt={review.user.name}
                                className="h-10 w-10 rounded-full object-cover"
                            />
                        ) : (
                            <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span className="text-sm font-semibold text-primary">
                                    {review.user.name.charAt(0).toUpperCase()}
                                </span>
                            </div>
                        )}
                        <div>
                            <p className="font-semibold">{review.user.name}</p>
                            <p className="text-sm text-muted-foreground">
                                {formatDate(review.created_at)}
                            </p>
                        </div>
                    </div>

                    {isOwner && (
                        <div className="flex gap-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setShowEditForm(true)}
                            >
                                <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setShowDeleteDialog(true)}
                            >
                                <Trash2 className="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-2 mb-3">
                    <div className="flex items-center">
                        {Array.from({ length: 5 }).map((_, index) => (
                            <Star
                                key={index}
                                className={`h-4 w-4 ${
                                    index < review.rating
                                        ? "fill-yellow-400 text-yellow-400"
                                        : "text-gray-300 dark:text-gray-600"
                                }`}
                            />
                        ))}
                    </div>
                    {review.is_verified_purchase && (
                        <span className="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">
                            {t("verified_purchase", "Verified Purchase")}
                        </span>
                    )}
                </div>

                {review.comment && (
                    <p className="text-muted-foreground">{review.comment}</p>
                )}
            </div>

            <AlertDialog
                open={showDeleteDialog}
                onOpenChange={setShowDeleteDialog}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {t("delete_review", "Delete Review")}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {t(
                                "confirm_delete_review",
                                "Are you sure you want to delete this review?"
                            )}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={deleting}>
                            {t("cancel", "Cancel")}
                        </AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleDelete}
                            disabled={deleting}
                            className="bg-destructive hover:bg-destructive/90"
                        >
                            {deleting
                                ? t("removing", "Removing...")
                                : t("delete_review", "Delete Review")}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
