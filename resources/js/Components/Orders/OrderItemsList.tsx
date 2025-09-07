import { useI18n } from "@/hooks/use-i18n";
import { Package } from "lucide-react";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { App } from "@/types";
import { Badge } from "../ui/badge";

interface OrderItemsListProps {
    items: App.Models.OrderItem[];
}

export function OrderItemsList({ items }: OrderItemsListProps) {
    const { t, getLocalizedField } = useI18n();
    console.log("OrderItemsList items:", items);
    return (
        <Card className="overflow-hidden border-none rounded-b-none">
            <CardHeader className="pb-3 bg-muted/50 border-b">
                <CardTitle className="flex items-center gap-2">
                    <Package className="w-5 h-5 text-primary" />
                    {t("order_items", "Order Items")}
                </CardTitle>
            </CardHeader>
            <CardContent className="p-0">
                {items && items.length > 0 ? (
                    <ul className="divide-y divide-border">
                        {items.map((item) => (
                            <li
                                key={item.id}
                                className="flex gap-6 py-5 px-6 hover:bg-muted/30 transition-colors"
                            >
                                <div className="h-24 w-16 flex-shrink-0 overflow-hidden rounded-lg border relative group">
                                    {item.variant!.images![0] ? (
                                        <Avatar className="h-full w-full rounded-lg">
                                            <AvatarImage
                                                src={item.variant!.images![0] }
                                                alt={getLocalizedField(
                                                    item.product,
                                                    "name"
                                                )}
                                                className="h-full w-full object-cover object-center transition-transform group-hover:scale-110"
                                            />
                                            <AvatarFallback className="rounded-lg">
                                                <Package className="h-8 w-8 text-muted-foreground" />
                                            </AvatarFallback>
                                        </Avatar>
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center bg-muted rounded-lg">
                                            <Package className="h-8 w-8 text-muted-foreground" />
                                        </div>
                                    )}
                                    <div className="absolute -top-2 -right-2 w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-xs font-medium">
                                        {item.quantity}
                                    </div>
                                </div>

                                <div className="flex flex-1 flex-col justify-center">
                                    <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                        <div>
                                            <h3 className="font-medium text-lg">
                                                {item.product
                                                    ? getLocalizedField(
                                                          item.product,
                                                          "name"
                                                      )
                                                    : t(
                                                          "product_not_available",
                                                          "Product not available"
                                                      )}
                                            </h3>

                                            {item.variant && (
                                                <div className="flex flex-wrap gap-1.5 mt-1.5">
                                                    {item.variant.color && (
                                                        <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                            {t("color", "Color")}: {item.variant.color}
                                                        </Badge>
                                                    )}
                                                    {item.variant.size && (
                                                        <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                            {t("size", "Size")}: {item.variant.size}
                                                        </Badge>
                                                    )}
                                                    {item.variant.capacity && (
                                                        <Badge variant="outline" className="px-2 py-0 h-5 text-xs">
                                                            {t("capacity", "Capacity")}: {item.variant.capacity}
                                                        </Badge>
                                                    )}
                                                </div>
                                            )}
                                            <div className="mt-1.5 flex items-center text-sm text-muted-foreground">
                                                <span>
                                                    EGP
                                                    {Number(
                                                        item.unit_price
                                                    ).toFixed(2)}{" "}
                                                    {t(
                                                        "per_unit",
                                                        "per unit"
                                                    )}
                                                </span>
                                                <span className="mx-2">
                                                    •
                                                </span>
                                                <span>
                                                    {t(
                                                        "quantity",
                                                        "Quantity"
                                                    )}
                                                    : {item.quantity}
                                                </span>
                                            </div>
                                        </div>
                                        <p className="text-right font-medium text-lg">
                                            EGP
                                            {(
                                                item.unit_price *
                                                item.quantity
                                            ).toFixed(2)}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <div className="flex flex-col items-center justify-center py-12">
                        <Package className="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 className="text-lg font-medium">
                            {t("no_items", "No items in this order")}
                        </h3>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t(
                                "empty_order",
                                "This order doesn't contain any items"
                            )}
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
