import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useI18n } from '@/hooks/use-i18n';
import { Alert, AlertDescription } from '@/Components/ui/alert';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useI18n();

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <div className="container mt-4">
            <Head title={t('forgot_password', 'Forgot Password')} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">{t('forgot_password', 'Forgot Password')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('forgot_password_description', 'Enter your email and we\'ll send you a link to reset your password.')}
                    </p>
                </div>

                {status && (
                    <Alert className="my-6">
                        <AlertDescription>{status}</AlertDescription>
                    </Alert>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">{t('email', 'Email')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            autoComplete="username"
                            autoFocus
                            onChange={(e) => setData('email', e.target.value)}
                            className={errors.email ? "border-destructive" : ""}
                        />
                        {errors.email && (
                            <p className="text-xs text-destructive">{errors.email}</p>
                        )}
                    </div>

                    <div className="flex items-center justify-between">
                        <Link
                            href={route('login')}
                            className="text-sm text-primary hover:underline"
                        >
                            {t('back_to_login', 'Back to Login')}
                        </Link>

                        <Button
                            type="submit"
                            disabled={processing}
                        >
                            {t('send_reset_link', 'Email Password Reset Link')}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
