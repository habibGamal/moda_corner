import React, { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import { Card } from '@/Components/ui/card';
import { Loader2, AlertCircle } from 'lucide-react';
import { Alert, AlertTitle, AlertDescription } from '@/Components/ui/alert';
import { useI18n } from '@/hooks/use-i18n';
import { PageTitle } from '@/Components/ui/page-title';
import { App } from '@/types';

interface PaymobProps extends App.Interfaces.AppPageProps {
    order: App.Models.Order;
    paymentData: {
        merchantId: string;
        orderId: string;
        amount: string;
        currency: string;
        mode: string;
        redirectUrl: string;
        failureUrl: string;
        webhookUrl: string;
        iframeUrl: string;
        intentionId: string;
        clientSecret: string;
    };
}

export default function Paymob({ paymentData, order }: PaymobProps) {
    const { t } = useI18n();
    const [isLoading, setIsLoading] = useState(true);
    const iframeRef = useRef<HTMLIFrameElement>(null);

    console.log('Paymob Payment Data:', paymentData);

    useEffect(() => {
        window.location.href = paymentData.iframeUrl;
    }, []);

    const handleIframeLoad = () => {
        console.log('Paymob iframe loaded successfully');
        setIsLoading(false);
    };

    const handleIframeError = () => {
        console.error('Failed to load Paymob iframe');
        setIsLoading(false);
    };

    return (
        <div className="container mt-4">
            <Head title={t('processing_payment', 'Processing Payment')} />

            <div className="space-y-6">
                <PageTitle
                    title={t('processing_payment', 'Processing Payment')}
                    backUrl={route('checkout.index')}
                    backText={t('back_to_order', 'Back to Order')}
                />

                <div className="grid gap-6">
                    <Card className="p-6 flex flex-col items-center justify-center min-h-[600px]">
                        {/* Payment Status */}
                        {isLoading && (
                            <div className="text-center space-y-6">
                                <div className="flex flex-col items-center gap-4">
                                    <Loader2 className="h-12 w-12 animate-spin text-primary" />
                                    <h2 className="text-xl font-medium">
                                        {t('initializing_payment', 'Initializing Payment')}
                                    </h2>
                                    <p className="text-muted-foreground max-w-md">
                                        {t('payment_message', 'Please wait while we connect to the secure payment gateway. Do not close this page.')}
                                    </p>
                                </div>

                                {/* Payment info */}
                                <div className="p-4 bg-muted rounded-lg mt-8">
                                    <div className="grid grid-cols-2 gap-3 text-sm">
                                        <div className="text-muted-foreground">
                                            {t('order_number', 'Order Number')}:
                                        </div>
                                        <div className="font-medium text-end">{paymentData.orderId}</div>

                                        <div className="text-muted-foreground">
                                            {t('amount', 'Amount')}:
                                        </div>
                                        <div className="font-medium text-end">
                                            {paymentData.amount} {paymentData.currency}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                        <a href={paymentData.iframeUrl}>Pay</a>
                        {/* Paymob Iframe */}
                        {/* {paymentData.iframeUrl && (
                            <iframe
                                ref={iframeRef}
                                src={paymentData.iframeUrl}
                                className={`w-full border-0 rounded-lg transition-opacity duration-300 ${
                                    isLoading ? 'opacity-0 h-0' : 'opacity-100 h-[600px]'
                                }`}
                                title="Paymob Payment"
                                 onLoad={handleIframeLoad}
                                onError={handleIframeError}
                                allow="payment"
                            />
                        )} */}
                    </Card>

                    {/* Help text */}
                    <Alert>
                        <AlertCircle className="h-4 w-4" />
                        <AlertTitle>{t('payment_help_title', 'Having trouble with payment?')}</AlertTitle>
                        <AlertDescription>
                            {t('payment_help_message', 'If the payment window does not appear, please try refreshing this page. For assistance, contact our support team.')}
                        </AlertDescription>
                    </Alert>

                    {/* Security notice */}
                    <Alert variant="default" className="border-primary/20 bg-primary/5">
                        <svg
                            className="h-4 w-4 text-primary"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            />
                        </svg>
                        <AlertTitle>{t('secure_payment', 'Secure Payment')}</AlertTitle>
                        <AlertDescription>
                            {t('secure_payment_message', 'Your payment information is encrypted and processed securely through Paymob. We do not store your card details.')}
                        </AlertDescription>
                    </Alert>
                </div>
            </div>
        </div>
    );
}
