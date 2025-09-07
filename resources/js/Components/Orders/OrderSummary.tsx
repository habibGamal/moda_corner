import { useI18n } from "@/hooks/use-i18n";
import { CardFooter } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { App } from "@/types";

interface OrderSummaryProps {
    order: App.Models.Order;
}

export function OrderSummary({ order }: OrderSummaryProps) {
    const { t } = useI18n();

    return (
        <CardFooter className="pt-6 bg-muted/20">
            <dl className="space-y-4 text-sm w-full">
                <div className="flex justify-between items-center">
                                        <span className="text-muted-foreground">
                        {t("subtotal", "Subtotal")}
                    </span>
                    <span>
                        EGP {Number(order.subtotal).toFixed(2)}
                    </span>
                </div>
                <div className="flex justify-between items-center">
                                        <span className="text-muted-foreground">
                        {t("shipping", "Shipping")}
                    </span>
                    <span>
                        EGP {Number(order.shipping_cost).toFixed(2)}
                    </span>
                </div>
                {Number(order.discount) > 0 && (
                    <div className="flex justify-between items-center text-green-600">
                        <dt>{t("discount", "Discount")}</dt>
                        <dd className="font-medium">
                                                    <span className="text-destructive">
                            -EGP {Number(order.discount).toFixed(2)}
                        </span>
                        </dd>
                    </div>
                )}
                <Separator className="my-2" />
                <div className="flex justify-between items-center font-medium text-xl">
                    <dt>{t("total", "Total")}</dt>
                    <dd className="text-primary">
                        EGP {Number(order.total).toFixed(2)}
                    </dd>
                </div>
            </dl>
        </CardFooter>
    );
}
