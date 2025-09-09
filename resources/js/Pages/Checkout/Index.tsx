import AddressModal from "@/Components/AddressModal";
import { CheckoutForm, OrderSummary } from "@/Components/Checkout";
import CustomCardForm from "@/Components/Payments/CustomCardForm";
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
    defaultPaymentMethod?: string;
}

// Define form schema with zod - conditional validation based on payment method
const checkoutFormSchema = z.object({
    address_id: z.string({
        required_error: "Please select a shipping address",
    }),
    payment_method: z.string({
        required_error: "Please select a payment method",
    }),
    notes: z.string().optional(),
    coupon_code: z.string().optional(),
    // Card payment fields - conditionally required
    card_number: z
        .string()
        .optional(),
    expiry_month: z
        .string()
        .optional(),
    expiry_year: z
        .string()
        .optional(),
    security_code: z
        .string()
        .optional(),
    name_on_card: z
        .string()
        .optional(),
    // Wallet payment fields - conditionally required
    mobile_phone: z
        .string()
        .optional(),
}).superRefine((data, ctx) => {
    // Validate card fields if payment method is card
    if (data.payment_method === 'card') {
        if (!data.card_number || data.card_number.length < 13 || data.card_number.length > 19 || !/^\d{13,19}$/.test(data.card_number)) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Card number must be 13-19 digits",
                path: ["card_number"],
            });
        }
        if (!data.expiry_month || !/^(0[1-9]|1[0-2])$/.test(data.expiry_month)) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Enter a valid month (01-12)",
                path: ["expiry_month"],
            });
        }
        if (!data.expiry_year || !/^\d{2}$/.test(data.expiry_year)) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Enter a valid year (YY)",
                path: ["expiry_year"],
            });
        } else {
            const currentYear = new Date().getFullYear() % 100;
            const inputYear = parseInt(data.expiry_year);
            if (inputYear < currentYear) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message: "Card has expired",
                    path: ["expiry_year"],
                });
            }
        }
        if (!data.security_code || !/^\d{3,4}$/.test(data.security_code) || data.security_code.length < 3 || data.security_code.length > 4) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "CVV must be 3 or 4 digits",
                path: ["security_code"],
            });
        }
        if (!data.name_on_card || data.name_on_card.length < 2 || data.name_on_card.length > 50 || !/^[a-zA-Z\s]+$/.test(data.name_on_card)) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Name must be 2-50 characters and contain only letters and spaces",
                path: ["name_on_card"],
            });
        }
    }

    // Validate wallet fields if payment method is wallet
    if (data.payment_method === 'wallet') {
        if (!data.mobile_phone || !/^01[0-9]{9}$/.test(data.mobile_phone)) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Mobile phone must be a valid Egyptian number (01xxxxxxxxx)",
                path: ["mobile_phone"],
            });
        }
    }
});

export default function Index({
    orderSummary: initialOrderSummary,
    cartSummary,
    addresses,
    paymentMethods,
    defaultPaymentMethod,
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
                defaultPaymentMethod ||
                (paymentMethods.length > 0
                    ? paymentMethods[0]
                    : "cash_on_delivery"),
            notes: "",
            coupon_code: initialCouponCode,
        },
    });

    console.log("Form errors:", form.formState.errors);

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
                    const newOrderSummary = page.props
                        .orderSummary as OrderSummary;
                    if (
                        !newOrderSummary.appliedPromotion &&
                        newOrderSummary.discount === 0
                    ) {
                        // Coupon was not applied, show error
                        setCouponError(
                            t(
                                "invalid_coupon_code",
                                "Invalid coupon code. Please check and try again."
                            )
                        );
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
                    setCouponError(
                        t(
                            "coupon_error",
                            "Failed to apply coupon code. Please try again."
                        )
                    );
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
        console.log("Submitting order with values:", values);
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

        // Prepare base submission data
        const submissionData: any = {
            address_id: parseInt(values.address_id),
            payment_method: values.payment_method,
            notes: values.notes || null,
            coupon_code: values.coupon_code || null,
        };

        // Add payment-specific fields based on payment method
        if (values.payment_method === 'card') {
            submissionData.card_number = values.card_number;
            submissionData.expiry_month = values.expiry_month;
            submissionData.expiry_year = values.expiry_year;
            submissionData.security_code = values.security_code;
            submissionData.name_on_card = values.name_on_card;
        } else if (values.payment_method === 'wallet') {
            submissionData.mobile_phone = values.mobile_phone;
        }

        // Submit the order
        router.post(route("orders.store"), submissionData, {
            onFinish: () => setIsSubmitting(false),
        });
    };

    return (
        <>
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
                            form={form}
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
        </>
    );
}
