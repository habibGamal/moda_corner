import { useI18n } from "@/hooks/use-i18n";
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Clock } from "lucide-react";
import { Control } from "react-hook-form";

interface DeliveryTimeSectionProps {
    deliveryTimeOptions: string[];
    control: Control<any>;
    direction: "ltr" | "rtl";
}

export function DeliveryTimeSection({
    deliveryTimeOptions,
    control,
    direction,
}: DeliveryTimeSectionProps) {
    const { t } = useI18n();

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Clock className="h-5 w-5" />
                    {t("preferred_delivery_time", "Preferred Delivery Time")}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <FormField
                    control={control}
                    name="preferred_delivery_time"
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel>
                                {t(
                                    "select_delivery_time",
                                    "Select a preferred delivery time (optional)"
                                )}
                            </FormLabel>
                            <Select
                                onValueChange={field.onChange}
                                value={field.value}
                                dir={direction}
                            >
                                <FormControl>
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder={t(
                                                "choose_delivery_time",
                                                "Choose delivery time..."
                                            )}
                                        />
                                    </SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    {deliveryTimeOptions.map((option) => (
                                        <SelectItem key={option} value={option}>
                                            {option}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    )}
                />
            </CardContent>
        </Card>
    );
}
