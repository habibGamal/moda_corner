import React, { useState } from "react";
import { Control, UseFormReturn } from "react-hook-form";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { useI18n } from "@/hooks/use-i18n";
import { CreditCard, Lock, User, Calendar, Hash } from "lucide-react";
import { cn } from "@/lib/utils";

interface CustomCardFormProps {
    isSubmitting?: boolean;
    orderAmount: string;
    orderCurrency: string;
}

// Card type detection
const getCardType = (cardNumber: string): string => {
    const number = cardNumber.replace(/\s/g, "");

    if (/^4/.test(number)) return "visa";
    if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) return "mastercard";
    if (/^3[47]/.test(number)) return "amex";
    if (/^6(?:011|5)/.test(number)) return "discover";

    return "unknown";
};

// Format card number with spaces
const formatCardNumber = (value: string): string => {
    const number = value.replace(/\s/g, "");
    const cardType = getCardType(number);

    if (cardType === "amex") {
        // American Express: 4-6-5 format
        return number.replace(/(\d{4})(\d{6})(\d{5})/, "$1 $2 $3");
    } else {
        // Other cards: 4-4-4-4 format
        return number.replace(/(\d{4})(?=\d)/g, "$1 ");
    }
};

// Format expiry date
const formatExpiry = (value: string): string => {
    const number = value.replace(/\D/g, "");
    if (number.length >= 2) {
        return number.substring(0, 2) + "/" + number.substring(2, 4);
    }
    return number;
};

export function CustomCardForm({ form, isVisible = true }: { form: UseFormReturn; isVisible?: boolean }) {
    const { t } = useI18n();
    const [cardNumber, setCardNumber] = useState("");
    const [expiry, setExpiry] = useState("");

    if (!isVisible) return null;

    const handleCardNumberChange = (value: string) => {
        const cleanValue = value.replace(/\s/g, "");
        if (cleanValue.length <= 19) {
            setCardNumber(cleanValue);
            form.setValue("card_number", cleanValue);
        }
    };

    const handleExpiryChange = (value: string) => {
        const cleanValue = value.replace(/\D/g, "");
        if (cleanValue.length <= 4) {
            setExpiry(cleanValue);
            if (cleanValue.length >= 2) {
                form.setValue("expiry_month", cleanValue.substring(0, 2));
                if (cleanValue.length === 4) {
                    form.setValue("expiry_year", cleanValue.substring(2, 4));
                }
            }
        }
    };

    const cardType = getCardType(cardNumber);
    const formattedCardNumber = formatCardNumber(cardNumber);
    const formattedExpiry = formatExpiry(expiry);

    // Test fill handlers
    const fillKashier = () => {
        // Visa test card
        const card = "5123456789012346";
        const exp = "0526"; // MMYY
        setCardNumber(card);
        setExpiry(exp);
        form.setValue("card_number", card);
        form.setValue("expiry_month", exp.substring(0, 2));
        form.setValue("expiry_year", exp.substring(2, 4));
        // Some forms in this component use camelCase keys as well
        form.setValue("expiryMonth", "05/26");
        form.setValue("security_code", "100");
        form.setValue("name_on_card", "Success Card");
    };

    const fillPaymob = () => {
        // Mastercard test card
        const card = "5123456789012346";
        const exp = "1225"; // MMYY
        setCardNumber(card);
        setExpiry(exp);
        form.setValue("card_number", card);
        form.setValue("expiry_month", exp.substring(0, 2));
        form.setValue("expiry_year", exp.substring(2, 4));
        form.setValue("expiryMonth", "12/25");
        form.setValue("security_code", "123");
        form.setValue("name_on_card", "Test Account");
    };

    return (
        <Card className="w-full max-w-md mx-auto">
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CreditCard className="w-5 h-5" />
                    {t("card_details", "Card Details")}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                <FormField
                    control={form.control}
                    name="card_number"
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel className="flex items-center gap-2">
                                <CreditCard className="w-4 h-4" />
                                {t("card_number", "Card Number")}
                            </FormLabel>
                            <FormControl>
                                <div className="relative">
                                    <Input
                                        dir="ltr"
                                        type="text"
                                        placeholder="1234 5678 9012 3456"
                                        value={formattedCardNumber}
                                        onChange={(e) =>
                                            handleCardNumberChange(
                                                e.target.value
                                            )
                                        }
                                        className={cn(
                                            "pr-12",
                                            cardType !== "unknown" &&
                                                cardNumber.length > 4 &&
                                                "border-green-300"
                                        )}
                                        maxLength={23} // Including spaces
                                    />
                                    {cardType !== "unknown" &&
                                        cardNumber.length > 4 && (
                                            <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                                                <div
                                                    className={cn(
                                                        "w-8 h-5 rounded text-xs flex items-center justify-center text-white font-semibold",
                                                        cardType === "visa" &&
                                                            "bg-blue-600",
                                                        cardType ===
                                                            "mastercard" &&
                                                            "bg-red-600",
                                                        cardType === "amex" &&
                                                            "bg-green-600",
                                                        cardType ===
                                                            "discover" &&
                                                            "bg-orange-600"
                                                    )}
                                                >
                                                    {cardType
                                                        .toUpperCase()
                                                        .substring(0, 4)}
                                                </div>
                                            </div>
                                        )}
                                </div>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />

                {/* Expiry Date and CVV */}
                <div className="grid grid-cols-2 gap-4">
                    <FormField
                        control={form.control}
                        name="expiryMonth"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel className="flex items-center gap-2">
                                    <Calendar className="w-4 h-4" />
                                    {t("expiry_date", "Expiry Date")}
                                </FormLabel>
                                <FormControl>
                                    <Input
                                        type="text"
                                        placeholder="MM/YY"
                                        value={formattedExpiry}
                                        onChange={(e) =>
                                            handleExpiryChange(e.target.value)
                                        }
                                        maxLength={5}
                                    />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="security_code"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel className="flex items-center gap-2">
                                    <Hash className="w-4 h-4" />
                                    {t("cvv", "CVV")}
                                </FormLabel>
                                <FormControl>
                                    <Input
                                        type="text"
                                        placeholder="123"
                                        {...field}
                                        maxLength={4}
                                        onChange={(e) => {
                                            const value =
                                                e.target.value.replace(
                                                    /\D/g,
                                                    ""
                                                );
                                            field.onChange(value);
                                        }}
                                    />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />
                </div>

                {/* Cardholder Name */}
                <FormField
                    control={form.control}
                    name="name_on_card"
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel className="flex items-center gap-2">
                                <User className="w-4 h-4" />
                                {t("cardholder_name", "Cardholder Name")}
                            </FormLabel>
                            <FormControl>
                                <Input
                                    type="text"
                                    placeholder={t(
                                        "enter_name_as_on_card",
                                        "Enter name as on card"
                                    )}
                                    {...field}
                                    onChange={(e) => {
                                        const value =
                                            e.target.value.toUpperCase();
                                        field.onChange(value);
                                    }}
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />

                {/* Test fill buttons */}
                <div className="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={fillKashier}
                        type="button"
                    >
                        kashier
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={fillPaymob}
                        type="button"
                    >
                        paymob
                    </Button>
                </div>

                {/* Security Notice */}
                <div className="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-950 rounded-lg text-sm">
                    <Lock className="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" />
                    <p className="text-blue-700 dark:text-blue-300">
                        {t(
                            "secure_payment_notice",
                            "Your payment information is encrypted and secure. We do not store your card details."
                        )}
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

export default CustomCardForm;
