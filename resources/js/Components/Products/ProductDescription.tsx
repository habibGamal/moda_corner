import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { Separator } from "@/Components/ui/separator";
import { ScrollArea } from "@/Components/ui/scroll-area";

interface ProductDescriptionProps {
    product: App.Models.Product;
}

export default function ProductDescription({
    product,
}: ProductDescriptionProps) {
    const { getLocalizedField, t, direction } = useI18n();

    const description = getLocalizedField(product, "description");

    return (
        <div className="w-full">
            <h3 className="text-lg font-medium mb-2">
                {t("description", "Description")}
            </h3>
            {description ? (
                <ScrollArea className="h-[200px]" dir={direction}>
                    <div className="text-muted-foreground whitespace-pre-wrap">
                        {description}
                    </div>
                </ScrollArea>
            ) : (
                <div className="text-muted-foreground italic">
                    {t("no_description", "No description available.")}
                </div>
            )}
            <Separator className="mt-6" />
        </div>
    );
}
