import { Head } from "@inertiajs/react";
import { App } from "@/types";
import { Button } from "@/Components/ui/button";
import ProductCard from "@/Components/ProductCard";
import { useI18n } from "@/hooks/use-i18n";
import { useState } from "react";
import { Heart, Trash2 } from "lucide-react";
import { router } from "@inertiajs/react";

interface WishlistPageProps {
    items: {
        id: number;
        product_id: number;
        product: App.Models.Product;
    }[];
}

export default function Index({ items }: WishlistPageProps) {
    const { t } = useI18n();
    const [removing, setRemoving] = useState<Record<number, boolean>>({});
    const [clearingList, setClearingList] = useState(false);

    const handleRemoveItem = (productId: number) => {
        setRemoving((prev) => ({ ...prev, [productId]: true }));

        router.delete(
            route("wishlist.remove", productId),
            {
                preserveScroll: true,
                onFinish: () => {
                    setRemoving((prev) => ({ ...prev, [productId]: false }));
                },
            }
        );
    };

    const handleClearList = () => {
        if (items.length === 0) return;

        setClearingList(true);
        router.delete(
            route("wishlist.clear"),
            {
                preserveScroll: true,
                onFinish: () => {
                    setClearingList(false);
                },
            }
        );
    };

    return (
        <>
            <Head title={t("wishlist", "Wishlist")} />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl md:text-3xl font-bold flex items-center gap-2">
                    <Heart className="h-6 w-6" />
                    {t("wishlist", "Wishlist")}
                </h1>

                {items.length > 0 && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={handleClearList}
                        disabled={clearingList}
                        className="text-destructive hover:text-destructive-foreground hover:bg-destructive"
                    >
                        <Trash2 className="h-4 w-4 mr-2" />
                        {clearingList
                            ? t("clearing", "Clearing...")
                            : t("clear_wishlist", "Clear Wishlist")}
                    </Button>
                )}
            </div>

            {items.length > 0 ? (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    {items.map((item) => (
                        <div key={item.id} className="relative group">
                            <ProductCard product={item.product} />
                            <Button
                                variant="destructive"
                                size="icon"
                                className="absolute top-2 left-2"
                                onClick={() =>
                                    handleRemoveItem(item.product_id)
                                }
                                disabled={removing[item.product_id]}
                            >
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-12">
                    <div className="inline-flex h-20 w-20 items-center justify-center rounded-full bg-muted mb-4">
                        <Heart className="h-10 w-10 text-muted-foreground" />
                    </div>
                    <h2 className="text-xl font-semibold mb-2">
                        {t("wishlist_empty", "Your wishlist is empty")}
                    </h2>
                    <p className="text-muted-foreground mb-6">
                        {t(
                            "wishlist_empty_description",
                            "Items added to your wishlist will appear here"
                        )}
                    </p>
                    <Button onClick={() => router.visit(route("home"))}>
                        {t("continue_shopping", "Continue Shopping")}
                    </Button>
                </div>
            )}
        </>
    );
}
