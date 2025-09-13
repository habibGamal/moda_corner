import React from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { PageTitle } from "@/Components/ui/page-title";
import {
    ReturnOrderInfo,
    ReturnReasonForm,
    ItemSelectionCard,
    ReturnSummaryCard,
} from "@/Components/Returns";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { RotateCcw, ArrowLeft } from "lucide-react";

interface ReturnCreateProps extends App.Interfaces.AppPageProps {
    order: App.Models.Order;
    returnableItems: (App.Models.OrderItem & {
        max_returnable_quantity: number;
    })[];
}

interface ReturnFormData {
    order_id: number;
    reason: string;
    return_items: Array<{
        order_item_id: number;
        quantity: number;
    }>;
    [key: string]: any;
}

export default function Create({ order, returnableItems }: ReturnCreateProps) {
    const { t } = useI18n();

    const { data, setData, post, processing, errors } = useForm<ReturnFormData>(
        {
            order_id: order.id,
            reason: "",
            return_items: [],
        }
    );

    // Helper function to handle item selection
    const handleItemToggle = (itemId: number, maxQuantity: number) => {
        const existingItem = data.return_items.find(
            (item) => item.order_item_id === itemId
        );

        if (existingItem) {
            // Remove item if already selected
            setData(
                "return_items",
                data.return_items.filter(
                    (item) => item.order_item_id !== itemId
                )
            );
        } else {
            // Add item with quantity 1
            setData("return_items", [
                ...data.return_items,
                { order_item_id: itemId, quantity: 1 },
            ]);
        }
    };

    // Helper function to update item quantity
    const handleQuantityChange = (itemId: number, quantity: number) => {
        const newQuantity = Math.max(0, quantity);

        if (newQuantity === 0) {
            // Remove item if quantity is 0
            setData(
                "return_items",
                data.return_items.filter(
                    (item) => item.order_item_id !== itemId
                )
            );
        } else {
            // Update quantity
            setData(
                "return_items",
                data.return_items.map((item) =>
                    item.order_item_id === itemId
                        ? { ...item, quantity: newQuantity }
                        : item
                )
            );
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (data.return_items.length === 0) {
            // Add error handling for no items selected
            return;
        }

        post(route("returns.store", { order: order.id }));
    };

    const selectedCount = data.return_items.length;
    const totalRefundAmount = data.return_items.reduce((total, returnItem) => {
        const item = returnableItems.find(
            (i) => i.id === returnItem.order_item_id
        );
        return total + (item ? item.unit_price * returnItem.quantity : 0);
    }, 0);

    return (
        <>
            <Head title={t("request_return", "Request Return")} />

            <div className="container mt-4">
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <PageTitle
                            title={t("request_return", "Request Return")}
                            icon={
                                <RotateCcw className="h-6 w-6 text-primary" />
                            }
                        />
                        <Link href={route("returns.index")}>
                            <Button
                                variant="outline"
                                className="flex items-center gap-2"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                {t("back_to_returns", "Back to Returns")}
                            </Button>
                        </Link>
                    </div>

                    {/* Order Information */}
                    <ReturnOrderInfo order={order} />

                    {/* Return Request Form */}
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Items Selection */}
                        <ItemSelectionCard
                            returnableItems={returnableItems}
                            selectedItems={data.return_items}
                            onItemToggle={handleItemToggle}
                            onQuantityChange={handleQuantityChange}
                            error={errors.items}
                        />

                        {/* Return Reason */}
                        <ReturnReasonForm
                            reason={data.reason}
                            onReasonChange={(reason) =>
                                setData("reason", reason)
                            }
                            error={errors.reason}
                        />
                    </form>
                </div>
            </div>
        </>
    );
}
