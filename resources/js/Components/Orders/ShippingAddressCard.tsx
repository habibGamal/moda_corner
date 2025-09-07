import { useI18n } from "@/hooks/use-i18n";
import {
    Home,
    AlertCircle,
    MapPin,
    Building,
    User,
    Navigation,
} from "lucide-react";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";
import { cn } from "@/lib/utils";
import { App } from "@/types";

interface ShippingAddressCardProps {
    address?: App.Models.Address;
}

export function ShippingAddressCard({ address }: ShippingAddressCardProps) {
    const { t, getLocalizedField } = useI18n();

    return (
        <Card className="overflow-hidden border-none shadow-md transition-all hover:shadow-lg">
            <CardHeader className="pb-3 bg-muted/50 border-b">
                <CardTitle className="flex items-center gap-2">
                    <Home className="w-5 h-5 text-primary" />
                    {t("shipping_address", "Shipping Address")}
                </CardTitle>
            </CardHeader>
            <CardContent className="p-6">
                {address ? (
                    <div className="space-y-4">
                        <div className="flex flex-wrap gap-2">
                            <Badge
                                variant="outline"
                                className="bg-primary/5 border-primary/20 text-primary"
                            >
                                <Building className="w-3 h-3 mr-1" />
                                {getLocalizedField(address.area?.gov, "name")}
                            </Badge>
                            <Badge
                                variant="outline"
                                className="bg-primary/5 border-primary/20 text-primary"
                            >
                                <MapPin className="w-3 h-3 mr-1" />
                                {getLocalizedField(address.area, "name")}
                            </Badge>
                        </div>

                        <div
                            className={cn(
                                "relative bg-gradient-to-r from-muted/80 to-muted p-4 rounded-lg border border-border/50",
                            )}
                        >
                            <p className="whitespace-pre-line">
                                {address.content}
                            </p>
                            {address.user && (
                                <div className="flex items-center mt-3 pt-3 border-t border-border/30">
                                    <User className="w-4 h-4 text-muted-foreground mr-2" />
                                    <span className="text-sm text-muted-foreground">
                                        {t("recipient", "Recipient")}:{" "}
                                        {address.user.name}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center h-[160px] bg-muted/30 rounded-lg border border-dashed border-muted-foreground/20">
                        <div className="bg-muted/50 p-3 rounded-full mb-3">
                            <AlertCircle className="h-6 w-6 text-muted-foreground" />
                        </div>
                        <p className="text-sm font-medium text-muted-foreground">
                            {t(
                                "address_not_available",
                                "Address information not available"
                            )}
                        </p>
                        <p className="text-xs text-muted-foreground/70 max-w-[200px] text-center mt-1">
                            {t(
                                "address_not_found",
                                "The shipping address for this order could not be found"
                            )}
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
