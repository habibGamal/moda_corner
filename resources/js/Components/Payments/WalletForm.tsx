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
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import { Input } from "@/Components/ui/input";
import { useI18n } from "@/hooks/use-i18n";
import { Smartphone } from "lucide-react";
import { UseFormReturn } from "react-hook-form";

interface WalletFormProps {
    form: UseFormReturn<any>;
    isVisible?: boolean;
}

export default function WalletForm({ form, isVisible = true }: WalletFormProps) {
    const { t, direction } = useI18n();

    if (!isVisible) return null;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center">
                    <Smartphone className="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    {t("wallet_payment_details", "Wallet Payment Details")}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                <FormField
                    control={form.control}
                    name="mobile_phone"
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel>
                                {t("mobile_phone", "Mobile Phone")}
                            </FormLabel>
                            <FormControl>
                                <Input
                                    placeholder={t(
                                        "mobile_phone_placeholder",
                                        "01001234567"
                                    )}
                                    {...field}
                                    dir={direction}
                                    className="w-full"
                                    maxLength={11}
                                />
                            </FormControl>
                            <FormMessage />
                            <p className="text-sm text-muted-foreground">
                                {t(
                                    "wallet_phone_hint",
                                    "Enter your Egyptian mobile number registered with your mobile wallet"
                                )}
                            </p>
                        </FormItem>
                    )}
                />
            </CardContent>
        </Card>
    );
}
