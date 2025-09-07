import { useI18n } from "@/hooks/use-i18n";

interface OrderThankYouProps {}

export function OrderThankYou({}: OrderThankYouProps) {
    const { t } = useI18n();

    return (
        <div className="text-center py-6 mt-2">
            <h3 className="text-xl font-semibold text-primary mb-1">
                {t("thank_you", "Thank You for Your Order!")}
            </h3>
            <p className="text-sm text-muted-foreground">
                {t(
                    "order_support",
                    "If you have any questions about your order, please contact our support team."
                )}
            </p>
        </div>
    );
}
