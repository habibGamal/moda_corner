import { Button } from "@/Components/ui/button";
import { Calendar } from "@/Components/ui/calendar";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
} from "@/Components/ui/form";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { cn } from "@/lib/utils";
import { useI18n } from "@/hooks/use-i18n";
import { usePage } from "@inertiajs/react";
import { format } from "date-fns";
import { Calendar as CalendarIcon } from "lucide-react";
import { Control } from "react-hook-form";

interface BirthdateSectionProps {
    control: Control<any>;
}

export function BirthdateSection({ control }: BirthdateSectionProps) {
    const { t } = useI18n();
    const { auth } = usePage().props as any;
    const user = auth?.user;

    // Don't show if user already has a birthdate
    if (user?.birthdate) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CalendarIcon className="h-5 w-5" />
                    {t("birthdate_optional", "Birthdate (Optional)")}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <FormField
                    control={control}
                    name="birthdate"
                    render={({ field }) => (
                        <FormItem className="flex flex-col">
                            <FormLabel>
                                {t("your_birthdate", "Your Birthdate")}
                            </FormLabel>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <FormControl>
                                        <Button
                                            variant="outline"
                                            data-empty={!field.value}
                                            className={cn(
                                                "w-full justify-start text-left font-normal",
                                                !field.value &&
                                                    "text-muted-foreground"
                                            )}
                                        >
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {field.value ? (
                                                format(
                                                    new Date(field.value),
                                                    "PPP"
                                                )
                                            ) : (
                                                <span>
                                                    {t(
                                                        "select_date",
                                                        "Select date"
                                                    )}
                                                </span>
                                            )}
                                        </Button>
                                    </FormControl>
                                </PopoverTrigger>
                                <PopoverContent
                                    className="w-auto p-0"
                                    align="start"
                                >
                                    <Calendar
                                        mode="single"
                                        captionLayout="dropdown"
                                        selected={
                                            field.value
                                                ? new Date(field.value)
                                                : undefined
                                        }
                                        onSelect={(date) => {
                                            field.onChange(
                                                date
                                                    ? format(date, "yyyy-MM-dd")
                                                    : ""
                                            );
                                        }}
                                        disabled={(date) =>
                                            date > new Date() ||
                                            date < new Date("1900-01-01")
                                        }
                                    />
                                </PopoverContent>
                            </Popover>
                            <FormDescription>
                                {t(
                                    "birthdate_description",
                                    "Help us celebrate your special day with exclusive offers"
                                )}{" "}
                                <span className="font-bold underline">
                                    {t("birthdate_please_note")}
                                </span>
                            </FormDescription>
                        </FormItem>
                    )}
                />
            </CardContent>
        </Card>
    );
}
