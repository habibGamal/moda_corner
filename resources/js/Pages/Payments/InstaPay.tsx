import React, { useState } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import { Card } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Alert, AlertTitle, AlertDescription } from "@/Components/ui/alert";
import { useI18n } from "@/hooks/use-i18n";
import { PageTitle } from "@/Components/ui/page-title";
import { App } from "@/types";
import { AlertCircle, Upload, CheckCircle2 } from "lucide-react";

interface InstaPayProps extends App.Interfaces.AppPageProps {
    order: App.Models.Order;
    canReupload?: boolean;
}

export default function InstaPay({
    order,
    canReupload = false,
}: InstaPayProps) {
    const { t } = useI18n();
    const [previewImage, setPreviewImage] = useState<string | null>(null);
    const { data, setData, post, processing, errors, progress } = useForm({
        instapay_account: "",
        payment_proof: null as File | null,
    });

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData("payment_proof", file);

            // Create preview
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewImage(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const url = canReupload
            ? route("instapay.reupload", order.id)
            : route("instapay.store", order.id);

        post(url, {
            forceFormData: true,
            onSuccess: () => {
                // Redirect handled by controller
            },
        });
    };

    const instapayAccount = "https://ipn.eg/S/mena.usama4/instapay/554XWR";

    return (
        <div className="container mt-4">
            <Head title={t("instapay_payment", "InstaPay Payment")} />

            <div className="space-y-6">
                <PageTitle
                    title={
                        canReupload
                            ? t(
                                  "reupload_payment_proof",
                                  "Re-upload Payment Proof"
                              )
                            : t("upload_payment_proof", "Upload Payment Proof")
                    }
                    backUrl={route("orders.show", order.id)}
                    backText={t("back_to_order", "Back to Order")}
                />

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Main Form */}
                    <Card className="p-6 md:col-span-2">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div>
                                <h2 className="text-xl font-semibold mb-4">
                                    {t("payment_details", "Payment Details")}
                                </h2>
                                <p className="text-sm text-muted-foreground mb-6">
                                    {t(
                                        "instapay_instructions",
                                        "Please transfer the order amount to our InstaPay account and upload proof of the transaction below."
                                    )}
                                </p>
                                <a target="_blank" href={instapayAccount} className="mb-6 underline">
                                    {instapayAccount}
                                </a>
                            </div>

                            {/* InstaPay Account Field */}
                            <div className="space-y-2">
                                <Label htmlFor="instapay_account">
                                    {t(
                                        "your_instapay_account",
                                        "Your InstaPay Account"
                                    )}
                                    <span className="text-xs text-muted-foreground ms-2">
                                        ({t("optional", "Optional")})
                                    </span>
                                </Label>
                                <Input
                                    id="instapay_account"
                                    type="text"
                                    placeholder={t(
                                        "enter_instapay_account",
                                        "e.g., 01XXXXXXXXX"
                                    )}
                                    value={data.instapay_account}
                                    onChange={(e) =>
                                        setData(
                                            "instapay_account",
                                            e.target.value
                                        )
                                    }
                                    disabled={processing}
                                />
                                {errors.instapay_account && (
                                    <p className="text-sm text-destructive">
                                        {errors.instapay_account}
                                    </p>
                                )}
                            </div>

                            {/* Payment Proof Upload */}
                            <div className="space-y-2">
                                <Label htmlFor="payment_proof">
                                    {t("payment_proof", "Payment Proof Image")}
                                    <span className="text-destructive ms-1">
                                        *
                                    </span>
                                </Label>
                                <div className="flex items-center gap-4">
                                    <Input
                                        id="payment_proof"
                                        type="file"
                                        accept="image/*"
                                        onChange={handleImageChange}
                                        disabled={processing}
                                        className="cursor-pointer"
                                    />
                                </div>
                                {errors.payment_proof && (
                                    <p className="text-sm text-destructive">
                                        {errors.payment_proof}
                                    </p>
                                )}

                                {/* Image Preview */}
                                {previewImage && (
                                    <div className="mt-4">
                                        <p className="text-sm font-medium mb-2">
                                            {t("preview", "Preview")}:
                                        </p>
                                        <img
                                            src={previewImage}
                                            alt="Payment proof preview"
                                            className="max-w-full h-auto rounded-lg border max-h-96 object-contain"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* Upload Progress */}
                            {progress && (
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span>
                                            {t("uploading", "Uploading")}...
                                        </span>
                                        <span>{progress.percentage}%</span>
                                    </div>
                                    <div className="w-full bg-secondary rounded-full h-2">
                                        <div
                                            className="bg-primary h-2 rounded-full transition-all"
                                            style={{
                                                width: `${progress.percentage}%`,
                                            }}
                                        />
                                    </div>
                                </div>
                            )}

                            {/* Submit Button */}
                            <Button
                                type="submit"
                                className="w-full gap-2"
                                disabled={processing || !data.payment_proof}
                            >
                                <Upload className="h-4 w-4" />
                                {processing
                                    ? t("uploading", "Uploading...")
                                    : canReupload
                                    ? t("reupload", "Re-upload")
                                    : t("submit", "Submit")}
                            </Button>
                        </form>
                    </Card>

                    {/* Order Summary */}
                    <div className="space-y-4">
                        <Card className="p-6">
                            <h3 className="font-semibold mb-4">
                                {t("order_summary", "Order Summary")}
                            </h3>
                            <div className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {t("order_number", "Order Number")}:
                                    </span>
                                    <span className="font-medium">
                                        #{order.id}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {t("amount_to_pay", "Amount to Pay")}:
                                    </span>
                                    <span className="font-bold text-lg">
                                        {order.total} {t("egp", "EGP")}
                                    </span>
                                </div>
                            </div>
                        </Card>

                        {/* Instructions */}
                        <Alert>
                            <AlertCircle className="h-4 w-4" />
                            <AlertTitle>
                                {t("important", "Important")}
                            </AlertTitle>
                            <AlertDescription className="text-sm space-y-2">
                                <p>
                                    {t(
                                        "instapay_instruction_1",
                                        "1. Transfer the exact amount to our InstaPay account"
                                    )}
                                </p>
                                <p>
                                    {t(
                                        "instapay_instruction_2",
                                        "2. Take a screenshot of the successful transaction"
                                    )}
                                </p>
                                <p>
                                    {t(
                                        "instapay_instruction_3",
                                        "3. Upload the screenshot above"
                                    )}
                                </p>
                                <p>
                                    {t(
                                        "instapay_instruction_4",
                                        "4. Wait for admin verification (usually within 24 hours)"
                                    )}
                                </p>
                            </AlertDescription>
                        </Alert>

                        {/* Success Note */}
                        <Alert
                            variant="default"
                            className="border-success/20 bg-success/5"
                        >
                            <CheckCircle2 className="h-4 w-4 text-success" />
                            <AlertTitle>
                                {t("after_verification", "After Verification")}
                            </AlertTitle>
                            <AlertDescription className="text-sm">
                                {t(
                                    "verification_message",
                                    "Once verified, your order will be processed and shipped to your address."
                                )}
                            </AlertDescription>
                        </Alert>
                    </div>
                </div>
            </div>
        </div>
    );
}
