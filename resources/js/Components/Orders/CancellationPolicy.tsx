import { useI18n } from "@/hooks/use-i18n";
import { AlertCircle } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";

interface CancellationPolicyProps {}

export function CancellationPolicy({}: CancellationPolicyProps) {
    const { t } = useI18n();

    return (
        <Alert className="border-none shadow-md bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300">
            <AlertCircle className="h-5 w-5" />
            <AlertTitle className="ml-2 font-semibold">
                {t(
                    "cancellation_policy_title",
                    "Cancellation Policy"
                )}
            </AlertTitle>
            <AlertDescription className="ml-2">
                {t(
                    "cancellation_policy_desc",
                    "You can cancel your order while it's still in the processing stage. Once the order status changes to 'Shipped', it can no longer be cancelled."
                )}
            </AlertDescription>
        </Alert>
    );
}
