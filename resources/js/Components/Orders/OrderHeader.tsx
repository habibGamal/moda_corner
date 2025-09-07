import { useI18n } from "@/hooks/use-i18n";
import { Link } from "@inertiajs/react";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { Button } from "@/Components/ui/button";
import { formatDate } from "@/lib/utils";
import { App } from "@/types";

interface OrderHeaderProps {
    order: App.Models.Order;
}

export function OrderHeader({ order }: OrderHeaderProps) {
    const { t ,direction } = useI18n();
    const ArrowIcon = direction === "rtl" ? ArrowRight : ArrowLeft;
    return (
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b">
            <div>
                <Button
                    variant="ghost"
                    size="sm"
                    asChild
                    className="mb-2 hover:bg-transparent group px-0"
                >
                    <Link href={route("orders.index")}>
                        <ArrowIcon className="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                        {t("back_to_orders", "Back to Orders")}
                    </Link>
                </Button>
                <h1 className="text-3xl font-bold tracking-tight">
                    {t("order_number", "Order")}{" "}
                    <span className="text-primary">#{order.id}</span>
                </h1>
                <p className="text-muted-foreground mt-1">
                    {formatDate(order.created_at)}
                </p>
            </div>
        </div>
    );
}
