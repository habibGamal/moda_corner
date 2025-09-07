import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    FormControl,
    FormField,
    FormItem,
    FormMessage,
} from "@/Components/ui/form";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useI18n } from "@/hooks/use-i18n";
import { formatCurrencyLocalized } from "@/utils/currencyUtils";
import { App } from "@/types";
import { AlertTriangle, CircleCheck, Gift, Percent, Tag, X } from "lucide-react";
import { Control } from "react-hook-form";

interface CouponSectionProps {
    control: Control<any>;
    appliedPromotion?: App.Models.Promotion | null;
    discount: number;
    isApplyingCoupon: boolean;
    onApplyCoupon: () => void;
    onRemoveCoupon: () => void;
    couponError?: string | null;
    onCouponCodeInput?: () => void;
}

export function CouponSection({
    control,
    appliedPromotion,
    discount,
    isApplyingCoupon,
    onApplyCoupon,
    onRemoveCoupon,
    couponError,
    onCouponCodeInput,
}: CouponSectionProps) {
    const { t, getLocalizedField, currentLocale } = useI18n();

    return (
        <div className="pt-4 border-t">
            <FormField
                control={control}
                name="coupon_code"
                render={({ field }) => (
                    <FormItem>
                        <Label>
                            {t("coupon_code", "Coupon Code")}
                        </Label>
                        <div className="flex gap-2">
                            <FormControl>
                                <Input
                                    placeholder={t(
                                        "enter_coupon_code",
                                        "Enter coupon code"
                                    )}
                                    {...field}
                                    disabled={isApplyingCoupon}
                                    onChange={(e) => {
                                        field.onChange(e);
                                        if (onCouponCodeInput) {
                                            onCouponCodeInput();
                                        }
                                    }}
                                    className={
                                        appliedPromotion
                                            ? "border-green-200 bg-green-50/50 text-green-700 dark:border-green-800 dark:bg-green-950/10 dark:text-green-300"
                                            : couponError
                                            ? "border-red-200 bg-red-50/50 text-red-700 dark:border-red-800 dark:bg-red-950/10 dark:text-red-300"
                                            : ""
                                    }
                                />
                            </FormControl>
                            {appliedPromotion ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={onRemoveCoupon}
                                    disabled={isApplyingCoupon}
                                    className="whitespace-nowrap border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/20"
                                >
                                    {isApplyingCoupon ? (
                                        t("removing", "Removing...")
                                    ) : (
                                        <>
                                            <X className="h-4 w-4 ltr:mr-1 rtl:ml-1" />
                                            {t("remove", "Remove")}
                                        </>
                                    )}
                                </Button>
                            ) : (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={onApplyCoupon}
                                    disabled={
                                        isApplyingCoupon || !field.value?.trim()
                                    }
                                    className="whitespace-nowrap border-green-200 text-green-600 hover:bg-green-50 hover:border-green-300 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-950/20"
                                >
                                    {isApplyingCoupon ? (
                                        t("applying", "Applying...")
                                    ) : (
                                        <>
                                            <Tag className="h-4 w-4 ltr:mr-1 rtl:ml-1" />
                                            {t("apply", "Apply")}
                                        </>
                                    )}
                                </Button>
                            )}
                        </div>
                        {appliedPromotion && discount > 0 && (
                            <div className="mt-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/20 dark:to-emerald-950/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <div className="flex items-start gap-3">
                                    <div className="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex-shrink-0">
                                        <Gift className="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 mb-1">
                                            <CircleCheck className="h-4 w-4 text-green-600 dark:text-green-400" />
                                            <span className="text-sm font-medium text-green-700 dark:text-green-300">
                                                {t(
                                                    "promotion_applied",
                                                    "Promotion Applied Successfully!"
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <span className="text-xs text-muted-foreground">
                                                {t("code", "Code")}:
                                            </span>
                                            <Badge
                                                variant="secondary"
                                                className="font-mono bg-green-100 text-green-700 border-green-200 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800"
                                            >
                                                {appliedPromotion.code}
                                            </Badge>
                                            <span className="text-xs text-muted-foreground">
                                                •
                                            </span>
                                            <Badge
                                                variant="default"
                                                className="bg-green-600 text-white hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-500"
                                            >
                                                <Percent className="h-3 w-3 ltr:mr-1 rtl:ml-1" />
                                                {t("saving", "Saving")}{" "}
                                                {formatCurrencyLocalized(discount, currentLocale)}
                                            </Badge>
                                        </div>
                                        {appliedPromotion.description_en && (
                                            <p className="text-xs text-muted-foreground mt-1 line-clamp-2">
                                                {getLocalizedField(
                                                    appliedPromotion,
                                                    "description"
                                                )}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
                        {couponError && (
                            <div className="mt-2 p-3 bg-red-50 dark:bg-red-950/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div className="flex items-center gap-2">
                                    <div className="flex items-center justify-center w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex-shrink-0">
                                        <AlertTriangle className="h-3 w-3 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span className="text-sm text-red-700 dark:text-red-300">
                                        {couponError}
                                    </span>
                                </div>
                            </div>
                        )}
                        <FormMessage />
                    </FormItem>
                )}
            />
        </div>
    );
}
