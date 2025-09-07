import { useI18n } from "@/hooks/use-i18n";
import { Card, CardContent } from "@/Components/ui/card";
import { OrderItemsList } from "./OrderItemsList";
import { OrderSummary } from "./OrderSummary";
import { App } from "@/types";

interface OrderItemsCardProps {
    order: App.Models.Order;
}

export function OrderItemsCard({ order }: OrderItemsCardProps) {
    // Combine the OrderItemsList and OrderSummary components
    return (
        <Card className="overflow-hidden border-none shadow-md">
            {order.items && <OrderItemsList items={order.items} />}
            <OrderSummary order={order} />
        </Card>
    );
}
