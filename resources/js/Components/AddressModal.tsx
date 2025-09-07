import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/Components/ui/form";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { zodResolver } from "@hookform/resolvers/zod";
import axios from "axios";
import { useState } from "react";
import { useForm } from "react-hook-form";
import * as z from "zod";
import { Textarea } from "./ui/textarea";

interface AddressModalProps {
    trigger: React.ReactNode;
    onAddressCreated: (newAddress: App.Models.Address) => void;
    areas?: App.Models.Area[];
}

const formSchema = z.object({
    area_id: z.string().min(1, "Area is required"),
    content: z.string().min(3, "Address content must be at least 3 characters"),
    phone: z.string().min(1, "Phone number is required").max(20, "Phone number must be at most 20 characters"),
});

export default function AddressModal({
    trigger,
    onAddressCreated,
    areas: initialAreas,
}: AddressModalProps) {
    const { t , direction } = useI18n();
    const [isOpen, setIsOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [areas, setAreas] = useState<App.Models.Area[]>(initialAreas || []);
    const [isLoadingAreas, setIsLoadingAreas] = useState(false);

    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            area_id: "",
            content: "",
            phone: "",
        },
    });
    // Fetch areas when modal opens if we don't have any
    const fetchAreas = async () => {
        if (areas.length === 0 && !isLoadingAreas) {
            try {
                setIsLoadingAreas(true);
                const response = await axios.get(route("addresses.areas"));
                setAreas(response.data.areas);
            } catch (error) {
                console.error("Failed to load areas", error);
            } finally {
                setIsLoadingAreas(false);
            }
        }
    };

    const onSubmit = async (values: z.infer<typeof formSchema>) => {
        try {
            setIsSubmitting(true);
            const response = await axios.post(route("addresses.store"), {
                area_id: parseInt(values.area_id),
                content: values.content,
                phone: values.phone,
            });

            onAddressCreated(response.data.address);
            form.reset();
            setIsOpen(false);
        } catch (error: any) {
            if (error.response?.data?.errors) {
                // Map backend validation errors to form fields
                Object.keys(error.response.data.errors).forEach((key) => {
                    form.setError(key as any, {
                        type: "manual",
                        message: error.response.data.errors[key][0],
                    });
                });
            }
        } finally {
            setIsSubmitting(false);
        }
    };
    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => {
                setIsOpen(open);
                if (open) {
                    fetchAreas();
                }
            }}
        >
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader dir="rtl">
                    <DialogTitle>
                        {t("add_new_address", "Add New Address")}
                    </DialogTitle>
                    <DialogDescription>
                        {t(
                            "address_description",
                            "Enter your address details below to create a new shipping address."
                        )}
                    </DialogDescription>
                </DialogHeader>
                <Form {...form}>
                    <form
                        onSubmit={form.handleSubmit(onSubmit)}
                        className="space-y-4"
                    >
                        <FormField
                            control={form.control}
                            name="area_id"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>{t("area", "Area")}</FormLabel>
                                    <Select
                                        onValueChange={field.onChange}
                                        defaultValue={field.value}
                                        disabled={isSubmitting}
                                        dir={direction}
                                    >
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue
                                                    placeholder={t(
                                                        "select_area",
                                                        "Select area"
                                                    )}
                                                />
                                            </SelectTrigger>
                                        </FormControl>{" "}
                                        <SelectContent>
                                            {isLoadingAreas ? (
                                                <SelectItem
                                                    value="loading"
                                                    disabled
                                                >
                                                    {t("loading", "Loading...")}
                                                </SelectItem>
                                            ) : areas.length === 0 ? (
                                                <SelectItem
                                                    value="none"
                                                    disabled
                                                >
                                                    {t(
                                                        "no_areas",
                                                        "No areas available"
                                                    )}
                                                </SelectItem>
                                            ) : (
                                                areas.map((area) => (
                                                    <SelectItem
                                                        key={area.id}
                                                        value={area.id.toString()}
                                                    >
                                                        {area.gov?.name_en},{" "}
                                                        {area.name_en}
                                                    </SelectItem>
                                                ))
                                            )}
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="phone"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>
                                        {t("phone_number", "Phone Number")}
                                    </FormLabel>
                                    <FormControl>
                                        <Input
                                            placeholder={t(
                                                "phone_placeholder",
                                                "Enter phone number..."
                                            )}
                                            {...field}
                                            disabled={isSubmitting}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="content"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>
                                        {t(
                                            "address_details",
                                            "Address Details"
                                        )}
                                    </FormLabel>
                                    <FormControl>
                                        <Textarea
                                            placeholder={t(
                                                "address_placeholder",
                                                "Building, street, landmark..."
                                            )}
                                            {...field}
                                            disabled={isSubmitting}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <DialogFooter>
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting
                                    ? t("saving_address", "Saving...")
                                    : t("save_address", "Save Address")}
                            </Button>
                        </DialogFooter>
                    </form>
                </Form>
            </DialogContent>
        </Dialog>
    );
}
