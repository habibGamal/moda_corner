import { useI18n } from "@/hooks/use-i18n";
import { ShoppingBag, Calendar, CreditCard, Tag } from "lucide-react";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { formatDate } from "@/lib/utils";
import { App } from "@/types";

interface OrderDetailsCardProps {
    order: App.Models.Order;
}

export function OrderDetailsCard({ order }: OrderDetailsCardProps) {
    const { t } = useI18n();

    return (
        <Card className="overflow-hidden border-none shadow-md">
            <CardHeader className="pb-3 bg-muted/50 border-b">
                <CardTitle className="flex items-center gap-2">
                    <ShoppingBag className="w-5 h-5 text-primary" />
                    {t("order_details", "Order Details")}
                </CardTitle>
            </CardHeader>
            <CardContent className="p-6">
                <dl className="grid gap-4 text-sm">
                    <div className="flex items-center">
                        <dt className="flex items-center gap-2 font-medium w-1/2">
                            <Calendar className="w-4 h-4 text-muted-foreground" />
                            {t("order_date", "Order Date")}
                        </dt>
                        <dd className="w-1/2">
                            {formatDate(order.created_at)}
                        </dd>
                    </div>
                    <div className="flex items-center">
                        <dt className="flex items-center gap-2 font-medium w-1/2">
                            <CreditCard className="w-4 h-4 text-muted-foreground" />
                            {t("payment_method", "Payment Method")}
                        </dt>
                        <dd className="w-1/2">
                            {t(
                                `payment_method_${order.payment_method}`,
                                order.payment_method === "cash_on_delivery"
                                    ? "Cash on Delivery"
                                    : order.payment_method
                            )}
                        </dd>
                    </div>
                    {order.coupon_code && (
                        <div className="flex items-center">
                            <dt className="flex items-center gap-2 font-medium w-1/2">
                                <Tag className="w-4 h-4 text-muted-foreground" />
                                {t("coupon", "Coupon")}
                            </dt>
                            <dd className="w-1/2">
                                <div className="inline-flex px-2 py-1 text-sm bg-secondary text-secondary-foreground rounded-md font-normal">
                                    {order.coupon_code}
                                </div>
                            </dd>
                        </div>
                    )}
                    {order.notes && (
                        <div className="col-span-2 pt-3 border-t">
                            <dt className="font-medium mb-2">
                                {t("notes", "Order Notes")}
                            </dt>
                            <dd className="bg-muted p-3 rounded-md italic text-muted-foreground">
                                "{order.notes}"
                            </dd>
                        </div>
                    )}
                </dl>
            </CardContent>
        </Card>
    );
}
