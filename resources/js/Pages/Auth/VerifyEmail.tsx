import { Link, Head } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { Button } from '@/Components/ui/button';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { useI18n } from '@/hooks/use-i18n';
import { router } from '@inertiajs/react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useI18n();

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        router.post(route('verification.send'));
    };

    return (
        <>
            <Head title={t('verify_email', 'Email Verification')} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">{t('verify_email', 'Email Verification')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('verify_email_description', 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?')}
                    </p>
                </div>

                <div className="mt-4 text-sm">
                    <p className="text-muted-foreground">
                        {t('verify_email_not_received', 'If you didn\'t receive the email, we will gladly send you another.')}
                    </p>
                </div>

                {status === 'verification-link-sent' && (
                    <Alert variant="success" className="my-4">
                        <AlertDescription>
                            {t('verification_link_sent', 'A new verification link has been sent to the email address you provided during registration.')}
                        </AlertDescription>
                    </Alert>
                )}

                <div className="mt-6 flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <form onSubmit={submit}>
                        <Button type="submit">
                            {t('resend_verification_email', 'Resend Verification Email')}
                        </Button>
                    </form>

                    <Link
                        href={route('profile.edit')}
                        className="text-sm text-primary hover:underline"
                    >
                        {t('update_profile', 'Edit Profile')}
                    </Link>
                </div>

                <form method="POST" action={route('logout')} className="mt-4 text-center">
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                    <Button
                        type="submit"
                        variant="ghost"
                        className="text-sm text-primary hover:underline"
                    >
                        {t('logout', 'Log Out')}
                    </Button>
                </form>
            </div>
        </>
    );
}
