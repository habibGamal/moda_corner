import { App } from "@/types";
import { Control, UseFormReturn } from "react-hook-form";
import { OrderNotesSection } from "./OrderNotesSection";
import { PaymentMethodSection } from "./PaymentMethodSection";
import { ShippingAddressSection } from "./ShippingAddressSection";
import CustomCardForm from "../Payments/CustomCardForm";
import WalletForm from "../Payments/WalletForm";

interface CheckoutFormProps {
    addresses: App.Models.Address[];
    paymentMethods: string[];
    direction: "ltr" | "rtl";
    form: UseFormReturn<any>;
}

export function CheckoutForm({
    addresses,
    paymentMethods,
    direction,
    form,
}: CheckoutFormProps) {
    const selectedPaymentMethod = form.watch("payment_method");

    return (
        <div className="md:col-span-2 space-y-6">
            <ShippingAddressSection
                addresses={addresses}
                control={form.control}
                direction={direction}
            />
            <PaymentMethodSection
                paymentMethods={paymentMethods}
                control={form.control}
                direction={direction}
            />

            {/* Conditionally show payment forms based on selected method */}
            {selectedPaymentMethod === "card" && (
                <CustomCardForm form={form} isVisible={true} />
            )}

            {selectedPaymentMethod === "wallet" && (
                <WalletForm form={form} isVisible={true} />
            )}

            <OrderNotesSection control={form.control} />
        </div>
    );
}
