import { Alert, AlertDescription } from "@/Components/ui/alert";
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
import { App } from "@/types";
import { MapPin } from "lucide-react";
import { Control } from "react-hook-form";

interface ShippingAddressSectionProps {
    addresses: App.Models.Address[];
    control: Control<any>;
    direction: "ltr" | "rtl";
}

export function ShippingAddressSection({
    addresses,
    control,
    direction,
}: ShippingAddressSectionProps) {
    const { t, getLocalizedField } = useI18n();

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center">
                    <MapPin className="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    {t("shipping_address", "Shipping Address")}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {addresses.length === 0 ? (
                    <Alert>
                        <AlertDescription>
                            {t(
                                "no_addresses",
                                "You have no saved addresses. Please add an address to continue."
                            )}
                        </AlertDescription>
                    </Alert>
                ) : (
                    <FormField
                        control={control}
                        name="address_id"
                        render={({ field }) => (
                            <FormItem>
                                <FormControl>
                                    <RadioGroup
                                        dir={direction}
                                        className="space-y-4"
                                        value={field.value}
                                        onValueChange={field.onChange}
                                    >
                                        {addresses.map((address) => (
                                            <div
                                                key={address.id}
                                                className={`flex items-start gap-3 p-2 rounded-md border cursor-pointer ${
                                                    field.value ===
                                                    address.id.toString()
                                                        ? "border-primary bg-primary/5"
                                                        : "border-border"
                                                }`}
                                            >
                                                <RadioGroupItem
                                                    value={address.id.toString()}
                                                    id={`address-${address.id}`}
                                                    className="mt-1"
                                                />
                                                <Label
                                                    htmlFor={`address-${address.id}`}
                                                    className="font-medium cursor-pointer flex-1 py-2"
                                                >
                                                    <div className="grid gap-1">
                                                        <div className="flex items-center gap-2">
                                                            {getLocalizedField(
                                                                address.area
                                                                    ?.gov,
                                                                "name"
                                                            )}
                                                            ,{" "}
                                                            {getLocalizedField(
                                                                address.area,
                                                                "name"
                                                            )}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {address.content}
                                                        </div>
                                                        {address.phone && (
                                                            <div className="text-sm text-muted-foreground">
                                                                {t("phone", "Phone")}: {address.phone}
                                                            </div>
                                                        )}
                                                    </div>
                                                </Label>
                                            </div>
                                        ))}
                                    </RadioGroup>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        )}
                    />
                )}
            </CardContent>
        </Card>
    );
}
