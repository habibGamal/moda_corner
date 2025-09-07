import { useI18n } from "@/hooks/use-i18n";
import { AlertCircle } from "lucide-react";

interface OrderCancelledAlertProps {}

export function OrderCancelledAlert({}: OrderCancelledAlertProps) {
    const { t } = useI18n();

    return (
        <div className="rounded-lg border border-red-200 bg-red-50 dark:bg-red-950/20 dark:border-red-900/50 p-6 flex gap-4 items-center">
            <div className="rounded-full bg-red-100 dark:bg-red-900/30 p-3">
                <AlertCircle className="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <h3 className="text-lg font-semibold text-red-800 dark:text-red-400">
                    {t("order_cancelled", "Order Cancelled")}
                </h3>
                <p className="text-red-700 dark:text-red-300">
                    {t(
                        "order_cancelled_message",
                        "This order has been cancelled and will not be processed further."
                    )}
                </p>
            </div>
        </div>
    );
}
