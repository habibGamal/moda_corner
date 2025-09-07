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
import { Textarea } from "@/Components/ui/textarea";
import { useI18n } from "@/hooks/use-i18n";
import { Receipt } from "lucide-react";
import { Control } from "react-hook-form";

interface OrderNotesSectionProps {
    control: Control<any>;
}

export function OrderNotesSection({ control }: OrderNotesSectionProps) {
    const { t } = useI18n();

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center">
                    <Receipt className="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    {t("order_notes", "Order Notes")}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <FormField
                    control={control}
                    name="notes"
                    render={({ field }) => (
                        <FormItem>
                            <FormControl>
                                <Textarea
                                    placeholder={t(
                                        "notes_placeholder",
                                        "Add any special instructions or notes for your order..."
                                    )}
                                    {...field}
                                    rows={3}
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />
            </CardContent>
        </Card>
    );
}
