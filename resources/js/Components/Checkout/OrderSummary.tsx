import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { CircleCheck, Percent, ShoppingBag, Tag, Truck } from "lucide-react";
import { Control, UseFormWatch } from "react-hook-form";
import { CouponSection } from "./CouponSection";
import { formatCurrencyLocalized } from "@/utils/currencyUtils";

interface OrderSummaryProps {
    cartSummary: {
        totalItems: number;
        totalPrice: number;
    };
    subtotal: number;
    shippingCost: number;
    discount: number;
    total: number;
    appliedPromotion?: App.Models.Promotion | null;
    selectedAddressId: string;
    control: Control<any>;
    watch: UseFormWatch<any>;
    isSubmitting: boolean;
    isApplyingCoupon: boolean;
    onApplyCoupon: () => void;
    onRemoveCoupon: () => void;
    addressesLength: number;
    couponError?: string | null;
    onCouponCodeInput?: () => void;
}

export function OrderSummary({
    cartSummary,
    subtotal,
    shippingCost,
    discount,
    total,
    appliedPromotion,
    selectedAddressId,
    control,
    watch,
    isSubmitting,
    isApplyingCoupon,
    onApplyCoupon,
    onRemoveCoupon,
    addressesLength,
    couponError,
    onCouponCodeInput,
}: OrderSummaryProps) {
    const { t, currentLocale } = useI18n();
    console.log("submit is disabled", isSubmitting , addressesLength === 0 , !selectedAddressId);
    return (
        <Card className="sticky top-4">
            <CardHeader>
                <CardTitle className="flex items-center">
                    <ShoppingBag className="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    {t("order_summary", "Order Summary")}
                </CardTitle>
                <CardDescription>
                    {t("items_in_cart", "Items in your cart")}: {cartSummary.totalItems}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {/* Order Summary Calculations */}
                <div className="space-y-3">
                    <div className="flex items-center justify-between">
                        <span className="text-muted-foreground">
                            {t("subtotal", "Subtotal")}
                        </span>
                        <span>{formatCurrencyLocalized(subtotal, currentLocale)}</span>
                    </div>

                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-1">
                            <Truck className="h-4 w-4 text-muted-foreground" />
                            <span className="text-muted-foreground">
                                {t("shipping", "Shipping")}
                            </span>
                        </div>
                        <span>
                            {selectedAddressId
                                ? formatCurrencyLocalized(shippingCost, currentLocale)
                                : t("select_address", "Select address")}
                        </span>
                    </div>

                    {discount > 0 && (
                        <div className="flex items-center justify-between p-3 bg-green-50 dark:bg-green-950/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div className="flex items-center gap-2">
                                <div className="flex items-center justify-center w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full">
                                    <Tag className="h-3 w-3 text-green-600 dark:text-green-400" />
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-sm font-medium text-green-700 dark:text-green-300">
                                        {t("discount_applied", "Discount Applied")}
                                    </span>
                                    {appliedPromotion && (
                                        <div className="flex items-center gap-1">
                                            <Badge
                                                variant="outline"
                                                className="text-xs font-mono bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800"
                                            >
                                                {appliedPromotion.code}
                                            </Badge>
                                            <Percent className="h-3 w-3 text-green-500" />
                                        </div>
                                    )}
                                </div>
                            </div>
                            <span className="text-lg font-semibold text-green-600 dark:text-green-400">
                                                            <span className="text-destructive">
                                -{formatCurrencyLocalized(discount, currentLocale)}
                            </span>
                            </span>
                        </div>
                    )}

                    <div className="border-t pt-3 mt-3">
                        <div className="flex items-center justify-between font-medium">
                            <span>{t("total", "Total")}</span>
                            <span className="text-lg">
                                {selectedAddressId
                                    ? formatCurrencyLocalized(total, currentLocale)
                                    : formatCurrencyLocalized(subtotal, currentLocale)}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Coupon/Promotion Code Section */}
                <CouponSection
                    control={control}
                    appliedPromotion={appliedPromotion}
                    discount={discount}
                    isApplyingCoupon={isApplyingCoupon}
                    onApplyCoupon={onApplyCoupon}
                    onRemoveCoupon={onRemoveCoupon}
                    couponError={couponError}
                    onCouponCodeInput={onCouponCodeInput}
                />
            </CardContent>
            <CardFooter>
                <Button
                    type="submit"
                    className="w-full"
                    size="lg"
                    disabled={
                        isSubmitting ||
                        addressesLength === 0 ||
                        !selectedAddressId
                    }
                >
                    {isSubmitting ? (
                        t("processing_order", "Processing...")
                    ) : (
                        <>
                            <CircleCheck className="w-4 h-4 ltr:mr-2 rtl:ml-2" />
                            {t("place_order", "Place Order")}
                        </>
                    )}
                </Button>
            </CardFooter>
        </Card>
    );
}
