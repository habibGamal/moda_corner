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
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { router, usePage } from "@inertiajs/react";
import { Bell, Check } from "lucide-react";
import { useState, useEffect } from "react";

interface NotifyMeButtonProps {
    product: App.Models.Product;
}

export default function NotifyMeButton({ product }: NotifyMeButtonProps) {
    const { t } = useI18n();
    const { auth } = usePage<{ auth: { user: App.Models.User | null } }>().props;
    const [open, setOpen] = useState(false);
    const [email, setEmail] = useState(auth.user?.email || "");
    const [isSubscribing, setIsSubscribing] = useState(false);
    const [isSubscribed, setIsSubscribed] = useState(false);
    const [isChecking, setIsChecking] = useState(true);

    // Check subscription status on mount
    useEffect(() => {
        if (auth.user?.email || email) {
            checkSubscriptionStatus(auth.user?.email || email);
        } else {
            setIsChecking(false);
        }
    }, [product.id, auth.user?.email]);

    const checkSubscriptionStatus = async (emailToCheck: string) => {
        if (!emailToCheck) {
            setIsChecking(false);
            return;
        }

        try {
            const response = await fetch(
                `/stock-notifications/check?product_id=${product.id}&email=${encodeURIComponent(emailToCheck)}`
            );
            const data = await response.json();
            setIsSubscribed(data.subscribed);
        } catch (error) {
            console.error("Failed to check subscription status:", error);
        } finally {
            setIsChecking(false);
        }
    };

    const handleSubscribe = () => {
        if (!email) return;

        setIsSubscribing(true);
        router.post(
            route("stock-notifications.subscribe"),
            {
                product_id: product.id,
                email: email,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsSubscribed(true);
                    setOpen(false);
                },
                onFinish: () => {
                    setIsSubscribing(false);
                },
            }
        );
    };

    const handleUnsubscribe = () => {
        if (!email) return;

        setIsSubscribing(true);
        router.post(
            route("stock-notifications.unsubscribe"),
            {
                product_id: product.id,
                email: email,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsSubscribed(false);
                },
                onFinish: () => {
                    setIsSubscribing(false);
                },
            }
        );
    };

    if (isChecking) {
        return null;
    }

    if (isSubscribed) {
        return (
            <div className="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <Check className="h-5 w-5 text-green-600 dark:text-green-400" />
                <div className="flex-1">
                    <p className="text-sm font-medium text-green-900 dark:text-green-100">
                        {t("youll_be_notified", "You'll be notified when back in stock")}
                    </p>
                    <p className="text-xs text-green-700 dark:text-green-300 mt-0.5">
                        {email}
                    </p>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleUnsubscribe}
                    disabled={isSubscribing}
                    className="text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100"
                >
                    {isSubscribing
                        ? t("unsubscribing", "Unsubscribing...")
                        : t("unsubscribe", "Unsubscribe")}
                </Button>
            </div>
        );
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="outline"
                    className="w-full"
                    size="lg"
                >
                    <Bell className="h-4 w-4 mr-2" />
                    {t("notify_when_available", "Notify Me When Available")}
                </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {t("notify_when_available", "Notify Me When Available")}
                    </DialogTitle>
                    <DialogDescription>
                        {t(
                            "get_notified_back_in_stock",
                            "Get notified when this product is back in stock"
                        )}
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">
                            {t("your_email", "Your Email")}
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder={t("email_placeholder", "Enter your email address")}
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            disabled={isSubscribing}
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        onClick={handleSubscribe}
                        disabled={isSubscribing || !email}
                        className="w-full"
                    >
                        {isSubscribing
                            ? t("subscribing", "Subscribing...")
                            : t("subscribe_to_notifications", "Subscribe to Notifications")}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
