import { App } from "@/types";
import { Control } from "react-hook-form";
import { BirthdateSection } from "./BirthdateSection";
import { OrderNotesSection } from "./OrderNotesSection";
import { PaymentMethodSection } from "./PaymentMethodSection";
import { ShippingAddressSection } from "./ShippingAddressSection";

interface CheckoutFormProps {
    addresses: App.Models.Address[];
    paymentMethods: string[];
    control: Control<any>;
    direction: "ltr" | "rtl";
}

export function CheckoutForm({
    addresses,
    paymentMethods,
    control,
    direction,
}: CheckoutFormProps) {
    return (
        <div className="md:col-span-2 space-y-6">
            <ShippingAddressSection
                addresses={addresses}
                control={control}
                direction={direction}
            />
            <PaymentMethodSection
                paymentMethods={paymentMethods}
                control={control}
                direction={direction}
            />
            <BirthdateSection control={control} />
            <OrderNotesSection control={control} />
        </div>
    );
}
