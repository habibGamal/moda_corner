import AddressModal from "@/Components/AddressModal";
import { CheckoutForm, OrderSummary } from "@/Components/Checkout";
import { Button } from "@/Components/ui/button";
import { Form } from "@/Components/ui/form";
import { PageTitle } from "@/Components/ui/page-title";
import { useI18n } from "@/hooks/use-i18n";
import { useToast } from "@/hooks/use-toast";
import { App } from "@/types";
import { zodResolver } from "@hookform/resolvers/zod";
import { Head, router } from "@inertiajs/react";
import { Plus } from "lucide-react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import * as z from "zod";

// Order summary type definition
interface OrderSummary {
    subtotal: number;
    shippingCost: number;
    discount: number;
    total: number;
    shippingDiscount?: boolean;
    appliedPromotion?: App.Models.Promotion | null;
}

interface CheckoutProps extends App.Interfaces.AppPageProps {
    orderSummary?: OrderSummary;
    cartSummary: {
        totalItems: number;
        totalPrice: number;
    };
    addresses: App.Models.Address[];
    paymentMethods: string[];
}

// Define form schema with zod
const checkoutFormSchema = z.object({
    address_id: z.string({
        required_error: "Please select a shipping address",
    }),
    payment_method: z.string({
        required_error: "Please select a payment method",
    }),
    notes: z.string().optional(),
    coupon_code: z.string().optional(),
});

export default function Index({
    orderSummary: initialOrderSummary,
    cartSummary,
    addresses,
    paymentMethods,
}: CheckoutProps) {
    const { t, direction } = useI18n();
    const [orderSummary, setOrderSummary] =
        useState<typeof initialOrderSummary>(initialOrderSummary);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isApplyingCoupon, setIsApplyingCoupon] = useState(false);
    const [couponError, setCouponError] = useState<string | null>(null);
    const { toast } = useToast();

    // Get URL parameters for initial form values
    const urlParams = new URLSearchParams(window.location.search);
    const initialCouponCode =
        urlParams.get("coupon_code") ||
        orderSummary?.appliedPromotion?.code ||
        "";

    // Initialize form with react-hook-form and zod validation
    const form = useForm<z.infer<typeof checkoutFormSchema>>({
        resolver: zodResolver(checkoutFormSchema),
        defaultValues: {
            address_id:
                addresses && addresses.length > 0
                    ? addresses[0].id.toString()
                    : "",
            payment_method:
                paymentMethods.length > 0
                    ? paymentMethods[0]
                    : "cash_on_delivery",
            notes: "",
            coupon_code: initialCouponCode,
        },
    });

    // Reload order summary whenever address is changed
    useEffect(() => {
        const addressId = form.watch("address_id");
        if (addressId) {
            const appliedCouponCode =
                orderSummary?.appliedPromotion?.code || "";
            const data: { address_id: string; coupon_code?: string } = {
                address_id: addressId,
            };
            if (appliedCouponCode) {
                data.coupon_code = appliedCouponCode;
            }
            router.reload({
                only: ["orderSummary"],
                data,
                onSuccess: (page) => {
                    if (page.props.orderSummary) {
                        setOrderSummary(
                            page.props.orderSummary as OrderSummary
                        );
                    }
                },
            });
        }
    }, [form.watch("address_id")]);

    // Handle coupon application
    const handleApplyCoupon = () => {
        const addressId = form.watch("address_id");
        const couponCode = form.watch("coupon_code");

        if (!addressId) {
            toast({
                title: t("address_required", "Address Required"),
                description: t(
                    "select_address_first",
                    "Please select an address first"
                ),
                variant: "destructive",
            });
            return;
        }

        if (!couponCode?.trim()) {
            toast({
                title: t("coupon_required", "Coupon Code Required"),
                description: t(
                    "enter_coupon_code",
                    "Please enter a coupon code"
                ),
                variant: "destructive",
            });
            return;
        }

        setIsApplyingCoupon(true);
        setCouponError(null); // Clear any previous errors

        router.reload({
            only: ["orderSummary"],
            data: {
                address_id: addressId,
                coupon_code: couponCode.trim(),
            },
            onFinish: () => setIsApplyingCoupon(false),
            onSuccess: (page) => {
                if (page.props.orderSummary) {
                    setOrderSummary(page.props.orderSummary as OrderSummary);
                    // Check if the coupon was actually applied by checking if there's a promotion
                    const newOrderSummary = page.props.orderSummary as OrderSummary;
                    if (!newOrderSummary.appliedPromotion && newOrderSummary.discount === 0) {
                        // Coupon was not applied, show error
                        setCouponError(t(
                            "invalid_coupon_code",
                            "Invalid coupon code. Please check and try again."
                        ));
                    } else {
                        setCouponError(null);
                    }
                }
            },
            onError: (errors) => {
                // Handle server-side validation errors
                if (errors.coupon_code) {
                    setCouponError(errors.coupon_code);
                } else {
                    setCouponError(t(
                        "coupon_error",
                        "Failed to apply coupon code. Please try again."
                    ));
                }
            },
        });
    };

    // Handle coupon removal
    const handleRemoveCoupon = () => {
        const addressId = form.watch("address_id");

        if (!addressId) return;

        setIsApplyingCoupon(true);
        setCouponError(null); // Clear any errors when removing coupon
        form.setValue("coupon_code", "");

        router.reload({
            only: ["orderSummary"],
            data: { address_id: addressId, coupon_code: null },
            onFinish: () => setIsApplyingCoupon(false),
            onSuccess: (page) => {
                if (page.props.orderSummary) {
                    setOrderSummary(page.props.orderSummary as OrderSummary);
                }
            },
        });
    };

    // Handle clearing coupon error when user types
    const handleClearCouponError = () => {
        if (couponError) {
            setCouponError(null);
        }
    };

    // Calculate order totals
    const subtotal = orderSummary
        ? orderSummary.subtotal
        : cartSummary.totalPrice;
    const shippingCost = Number(orderSummary ? orderSummary.shippingCost : 0);
    const discount = orderSummary ? orderSummary.discount : 0;
    const total = subtotal + shippingCost - discount;
    const appliedPromotionFromBackend = orderSummary?.appliedPromotion;

    // Handle order submission
    const onSubmit = (values: z.infer<typeof checkoutFormSchema>) => {
        if (!values.address_id) {
            toast({
                title: t("address_required", "Address is required"),
                description: t(
                    "select_address_first",
                    "Please select or add a shipping address to continue."
                ),
                variant: "destructive",
            });
            return;
        }

        setIsSubmitting(true);

        // Submit the order
        router.post(
            route("orders.store"),
            {
                address_id: parseInt(values.address_id),
                payment_method: values.payment_method,
                notes: values.notes || null,
                coupon_code: values.coupon_code || null,
            },
            {
                onFinish: () => setIsSubmitting(false),
            }
        );
    };

    return (
        <div className="container mt-4">
            <Head title={t("checkout", "Checkout")} />

            <div className="space-y-6">
                <PageTitle
                    title={t("checkout", "Checkout")}
                    backUrl={route("cart.index")}
                    backText={t("back_to_cart", "Back to Cart")}
                />

                <AddressModal
                    trigger={
                        <Button variant="outline" className="gap-2">
                            <Plus className="h-4 w-4" />
                            {t("add_new_address", "Add New Address")}
                        </Button>
                    }
                    onAddressCreated={(newAddress) => {
                        // Update the form with the new address
                        router.reload({
                            only: ["addresses"],
                            onSuccess: () => {
                                form.setValue(
                                    "address_id",
                                    newAddress.id.toString()
                                );
                            },
                        });
                    }}
                />

                <Form {...form}>
                    <form
                        onSubmit={form.handleSubmit(onSubmit)}
                        className="grid gap-6 md:grid-cols-3 items-start"
                    >
                        <CheckoutForm
                            addresses={addresses}
                            paymentMethods={paymentMethods}
                            control={form.control}
                            direction={direction}
                        />

                        <OrderSummary
                            cartSummary={cartSummary}
                            subtotal={subtotal}
                            shippingCost={shippingCost}
                            discount={discount}
                            total={total}
                            appliedPromotion={appliedPromotionFromBackend}
                            selectedAddressId={form.watch("address_id")}
                            control={form.control}
                            watch={form.watch}
                            isSubmitting={isSubmitting}
                            isApplyingCoupon={isApplyingCoupon}
                            onApplyCoupon={handleApplyCoupon}
                            onRemoveCoupon={handleRemoveCoupon}
                            addressesLength={addresses.length}
                            couponError={couponError}
                            onCouponCodeInput={handleClearCouponError} // Clear error on input
                        />
                    </form>
                </Form>
            </div>
        </div>
    );
}
