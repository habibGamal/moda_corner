import { useI18n } from "@/hooks/use-i18n";
import { Head, router } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { App } from "@/types";
import { PageTitle } from "@/Components/ui/page-title";
import { Badge } from "@/Components/ui/badge";
import { CreditCard, RotateCcw } from "lucide-react";
import {
    OrderProgress,
    OrderCancelledAlert,
    OrderDetailsCard,
    ShippingAddressCard,
    OrderItemsCard,
    CancellationPolicy,
    OrderThankYou
} from "@/Components/Orders";
import ReturnOrderModal from "@/Components/Orders/ReturnOrderModal";

interface OrderShowProps extends App.Interfaces.AppPageProps {
    order: App.Models.Order;
    canRequestReturn?: boolean;
}

export default function Show({ order, canRequestReturn = false }: OrderShowProps) {
    const { t } = useI18n();

    // Helper function to get appropriate payment status badge color
    const getPaymentStatusBadgeColor = (status: App.Models.PaymentStatus) => {
        switch (status) {
            case "paid":
                return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
            case "pending":
                return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
            default:
                return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
        }
    };

    // Helper function to get appropriate return status badge color
    const getReturnStatusBadgeColor = (status: string) => {
        switch (status) {
            case "return_requested":
                return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500";
            case "return_approved":
                return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500";
            case "return_rejected":
                return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500";
            case "item_returned":
            case "refund_processed":
                return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500";
            default:
                return "bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500";
        }
    };

    // Check if payment needs to be completed (for non-COD orders that are pending payment)
    const needsPayment = () => {
        return (
            order.payment_method !== "cash_on_delivery" &&
            order.payment_status === "pending" &&
            order.order_status !== "cancelled"
        );
    };

    // Handle order cancellation
    const handleCancelOrder = () => {
        if (
            confirm(
                t(
                    "confirm_cancel_order",
                    "Are you sure you want to cancel this order?"
                )
            )
        ) {
            router.patch(
                route("orders.cancel", order.id),
                {},
                {
                    onSuccess: () => {
                        // No need to do anything here, this will be handled by Inertia
                    },
                }
            );
        }
    };
    console.log(canRequestReturn);

    return (
        <>
            <Head title={t("order_details", "Order Details")} />

            <div className="container mt-4">
                <div className="space-y-8">
                {/* Order Header with Back Button and Order Number */}
                <div className="flex flex-col gap-4">
                    <PageTitle
                        title={`${t("order_number", "Order")} #${order.id}`}
                        backUrl={route("orders.index")}
                        backText={t("back_to_orders", "Back to Orders")}
                        className="pb-4 border-b"
                    />

                    {/* Payment Status Banner */}
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap gap-4 items-center">
                            <div className="flex gap-2 items-center">
                                <span className="text-sm font-medium">{t("payment_status", "Payment Status")}:</span>
                                <Badge
                                    variant="outline"
                                    className={getPaymentStatusBadgeColor(order.payment_status)}
                                >
                                    {t(`payment_status_${order.payment_status}`, order.payment_status)}
                                </Badge>
                            </div>

                            {/* Return Status Badge */}
                            {order.return_status && (
                                <div className="flex gap-2 items-center">
                                    <span className="text-sm font-medium">{t("return_status", "Return Status")}:</span>
                                    <Badge
                                        variant="outline"
                                        className={getReturnStatusBadgeColor(order.return_status)}
                                    >
                                        {t(`return_status_${order.return_status}`, order.return_status)}
                                    </Badge>
                                </div>
                            )}
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {needsPayment() && (
                                <Button
                                    asChild
                                    className="flex items-center gap-1"
                                >
                                    <a href={route("kashier.payment.show", order.id)}>
                                        <CreditCard className="h-4 w-4" />
                                        {t("complete_your_payment", "Complete Your Payment")}
                                    </a>
                                </Button>
                            )}

                            {/* Return Order Button */}
                            {canRequestReturn && !order.return_status && (
                                <ReturnOrderModal
                                    orderId={order.id}
                                    canRequestReturn={canRequestReturn}
                                />
                            )}

                            {/* View Return History Button */}
                            <Button asChild variant="outline">
                                <a href={route("orders.returns.history")}>
                                    <RotateCcw className="h-4 w-4" />
                                    {t("return_history", "Return History")}
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Order Status Timeline */}
                {order.order_status !== "cancelled" && (
                    <OrderProgress order={order} />
                )}

                {/* Order Cancelled Alert */}
                {order.order_status === "cancelled" && (
                    <OrderCancelledAlert />
                )}

                {/* Return Information Alert */}
                {order.return_status && order.return_reason && (
                    <div className="bg-orange-50 border border-orange-200 rounded-lg p-4 dark:bg-orange-900/20 dark:border-orange-800">
                        <div className="flex items-start gap-3">
                            <RotateCcw className="h-5 w-5 text-orange-600 dark:text-orange-400 mt-0.5" />
                            <div className="flex-1">
                                <h3 className="font-medium text-orange-800 dark:text-orange-200 mb-2">
                                    {t("return_request_information", "Return Request Information")}
                                </h3>
                                <div className="space-y-2 text-sm">
                                    <div>
                                        <span className="font-medium text-orange-700 dark:text-orange-300">
                                            {t("return_status", "Status")}:
                                        </span>
                                        <span className="ml-2 text-orange-600 dark:text-orange-400">
                                            {t(`return_status_${order.return_status}`, order.return_status)}
                                        </span>
                                    </div>
                                    {order.return_requested_at && (
                                        <div>
                                            <span className="font-medium text-orange-700 dark:text-orange-300">
                                                {t("requested_on", "Requested on")}:
                                            </span>
                                            <span className="ml-2 text-orange-600 dark:text-orange-400">
                                                {new Date(order.return_requested_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                    )}
                                    <div>
                                        <span className="font-medium text-orange-700 dark:text-orange-300">
                                            {t("return_reason", "Reason")}:
                                        </span>
                                        <p className="mt-1 text-orange-600 dark:text-orange-400">
                                            {order.return_reason}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Order Information Cards */}
                <div className="grid gap-6 md:grid-cols-2">
                    <OrderDetailsCard order={order} />
                    <ShippingAddressCard address={order.shipping_address} />
                </div>

                {/* Order Items with Order Summary */}
                <OrderItemsCard order={order} />

                {/* Cancellation policy and action for processing orders */}
                {order.order_status === "processing" && (
                    <>
                        <CancellationPolicy />
                        <div className="flex justify-center">
                            <Button
                                variant="destructive"
                                onClick={handleCancelOrder}
                                className="mt-2"
                            >
                                {t("cancel_order", "Cancel Order")}
                            </Button>
                        </div>
                    </>
                )}

                {/* "Thank you" message at the bottom */}
                <OrderThankYou />
                </div>
            </div>
        </>
    );
}
