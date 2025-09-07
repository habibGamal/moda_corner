import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import {
    FormControl,
    FormField,
    FormItem,
    FormMessage,
} from "@/Components/ui/form";
import { Label } from "@/Components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { useI18n } from "@/hooks/use-i18n";
import { Banknote, CreditCard } from "lucide-react";
import { Control } from "react-hook-form";

interface PaymentMethodSectionProps {
    paymentMethods: string[];
    control: Control<any>;
    direction: "ltr" | "rtl";
}

export function PaymentMethodSection({
    paymentMethods,
    control,
    direction,
}: PaymentMethodSectionProps) {
    const { t } = useI18n();

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center">
                    <CreditCard className="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    {t("payment_method", "Payment Method")}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <FormField
                    control={control}
                    name="payment_method"
                    render={({ field }) => (
                        <FormItem>
                            <FormControl>
                                <RadioGroup
                                    dir={direction}
                                    className="space-y-4"
                                    value={field.value}
                                    onValueChange={field.onChange}
                                >
                                    {paymentMethods.map((method) => (
                                        <div
                                            key={method}
                                            className={`flex items-center gap-3 p-2 rounded-md border cursor-pointer ${
                                                field.value === method
                                                    ? "border-primary bg-primary/5"
                                                    : "border-border"
                                            }`}
                                        >
                                            <RadioGroupItem
                                                value={method}
                                                id={`payment-${method}`}
                                            />
                                            <Label
                                                htmlFor={`payment-${method}`}
                                                className="font-medium cursor-pointer flex py-2 items-center gap-2"
                                            >
                                                {method === "cash_on_delivery" && (
                                                    <Banknote className="w-4 h-4" />
                                                )}
                                                {method === "kashier" && (
                                                    <CreditCard className="w-4 h-4" />
                                                )}
                                                {t(
                                                    `payment_method_${method}`,
                                                    method === "cash_on_delivery"
                                                        ? "Cash on Delivery"
                                                        : method === "kashier"
                                                        ? "Credit Card (Kashier)"
                                                        : method
                                                )}
                                            </Label>
                                        </div>
                                    ))}
                                </RadioGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />
            </CardContent>
        </Card>
    );
}
