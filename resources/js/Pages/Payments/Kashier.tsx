import React, { useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { Card } from '@/Components/ui/card';
import { Loader2, Check, AlertCircle } from 'lucide-react';
import { Alert, AlertTitle, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { useI18n } from '@/hooks/use-i18n';
import { PageTitle } from '@/Components/ui/page-title';
import { App } from '@/types';
import { Spinner } from '@/Components/ui/spinner';

interface KashierProps extends App.Interfaces.AppPageProps {
    order: App.Models.Order;
    kashierParams: {
        merchantId: string;
        orderId: string;
        amount: string;
        currency: string;
        hash: string;
        mode: string;
        merchantRedirect: string;
        serverWebhook: string;
        failureRedirect: string;
        allowedMethods: string;
        displayMode: string;
        paymentRequestId: string;
    }
}

export default function Kashier({ kashierParams }: KashierProps) {
    const { t } = useI18n();
    const iframeLoaded = useRef(false);
    const iframeErrored = useRef(false);
    console.log('Kashier Params:', kashierParams);
    useEffect(() => {
        // Load the Kashier checkout script
        const script = document.createElement('script');
        script.id = 'kashier-iFrame';
        script.src = 'https://payments.kashier.io/kashier-checkout.js';
        script.setAttribute('data-amount', kashierParams.amount);
        script.setAttribute('data-hash', kashierParams.hash);
        script.setAttribute('data-currency', kashierParams.currency);
        script.setAttribute('data-orderid', kashierParams.orderId);
        script.setAttribute('data-merchantid', kashierParams.merchantId);
        script.setAttribute('data-merchantredirect', kashierParams.merchantRedirect);
        script.setAttribute('data-serverwebhook', kashierParams.serverWebhook);
        script.setAttribute('data-failureredirect', kashierParams.failureRedirect);
        script.setAttribute('data-mode', kashierParams.mode);
        script.setAttribute('data-display', kashierParams.displayMode);
        script.setAttribute('data-allowedmethods', kashierParams.allowedMethods);
        script.setAttribute('data-paymentrequestId', kashierParams.paymentRequestId);

        // Handle script loading success
        script.onload = () => {
            console.log('Kashier script loaded successfully');
            iframeLoaded.current = true;
        };

        // Handle script loading error
        script.onerror = () => {
            console.error('Failed to load Kashier script');
            iframeErrored.current = true;
        };

        // Append the script to the body
        document.getElementById('kashier-iFrame-container')!.appendChild(script);

        // Cleanup function
        return () => {
            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }
        };
    }, [kashierParams]);

    return (
        <>
            <Head title={t('processing_payment', 'Processing Payment')} />

            <div className="space-y-6">
                <PageTitle
                    title={t('processing_payment', 'Processing Payment')}
                    backUrl={route('checkout.index')}
                    backText={t('back_to_order', 'Back to Order')}
                />

                <div className="grid gap-6">
                    <Card className="p-6 flex flex-col items-center justify-center min-h-[400px]">
                        {/* Payment Status */}
                        <div className="text-center space-y-6">
                            <div className="flex flex-col items-center gap-4">
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
                                    <div className="font-medium text-end">{kashierParams.orderId}</div>

                                    <div className="text-muted-foreground">
                                        {t('amount', 'Amount')}:
                                    </div>
                                    <div className="font-medium text-end">{kashierParams.amount} {kashierParams.currency}</div>
                                </div>
                            </div>

                            {/* Kashier iframe will be automatically injected here */}
                            <div id="kashier-iFrame-container"></div>
                        </div>
                    </Card>

                    {/* Help text */}
                    <Alert>
                        <AlertCircle className="h-4 w-4" />
                        <AlertTitle>{t('payment_help_title', 'Having trouble with payment?')}</AlertTitle>
                        <AlertDescription>
                            {t('payment_help_message', 'If the payment window does not appear, please try refreshing this page. For assistance, contact our support team.')}
                        </AlertDescription>
                    </Alert>
                </div>
            </div>
        </>
    );
}
