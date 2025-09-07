import { useI18n } from "@/hooks/use-i18n";
import { Progress } from "@/Components/ui/progress";
import { Clock, Truck, CheckCircle } from "lucide-react";
import { cn } from "@/lib/utils";
import { App } from "@/types";

interface OrderProgressProps {
    order: App.Models.Order;
}

export function OrderProgress({ order }: OrderProgressProps) {
    const { t ,direction} = useI18n();

    // Define order status steps
    const orderStatuses = ["processing", "shipped", "delivered"];

    // Calculate the current step and progress percentage for timeline
    const getOrderProgressPercentage = () => {
        if (order.order_status === "cancelled") return 0;
        const currentIndex = orderStatuses.indexOf(
            order.order_status as string
        );
        if (currentIndex < 0) return 0;
        return ((currentIndex + 1) / orderStatuses.length) * 100;
    };

    if (order.order_status === "cancelled") {
        return null;
    }

    return (
        <div className="rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden" dir="ltr">
            <div className="p-6">
                <h2 className="text-xl font-semibold mb-4" dir="rtl">
                    {t("order_progress", "Order Progress")}
                </h2>
                <Progress
                    value={getOrderProgressPercentage()}
                    className="h-2 mb-6"
                />
                <div className="grid grid-cols-3 gap-2">
                    {orderStatuses.map((status, index) => (
                        <div
                            key={status}
                            className="flex flex-col items-center text-center"
                        >
                            <div
                                className={cn(
                                    "w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors",
                                    orderStatuses.indexOf(
                                        order.order_status as string
                                    ) >= index
                                        ? "bg-primary text-primary-foreground"
                                        : "bg-muted text-muted-foreground"
                                )}
                            >
                                {index === 0 ? (
                                    <Clock className="w-5 h-5" />
                                ) : index === 1 ? (
                                    <Truck className="w-5 h-5" />
                                ) : (
                                    <CheckCircle className="w-5 h-5" />
                                )}
                            </div>
                            <span
                                className={cn(
                                    "text-sm font-medium",
                                )}
                            >
                                {t(
                                    `order_status_${status}`,
                                    status
                                )}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
