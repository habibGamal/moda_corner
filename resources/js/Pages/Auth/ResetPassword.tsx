import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useI18n } from '@/hooks/use-i18n';

export default function ResetPassword({ token, email }: { token: string, email: string }) {
    const { t } = useI18n();

    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.store'));
    };

    return (
        <>
            <Head title={t('reset_password', 'Reset Password')} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">{t('reset_password', 'Reset Password')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('reset_password_description', 'Create a new secure password for your account')}
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">{t('email', 'Email')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            autoComplete="username"
                            onChange={(e) => setData('email', e.target.value)}
                            className={errors.email ? "border-destructive" : ""}
                            readOnly
                        />
                        {errors.email && (
                            <p className="text-xs text-destructive">{errors.email}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">{t('password', 'Password')}</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            autoComplete="new-password"
                            autoFocus
                            onChange={(e) => setData('password', e.target.value)}
                            className={errors.password ? "border-destructive" : ""}
                        />
                        {errors.password && (
                            <p className="text-xs text-destructive">{errors.password}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation">{t('confirm_password', 'Confirm Password')}</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            autoComplete="new-password"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className={errors.password_confirmation ? "border-destructive" : ""}
                        />
                        {errors.password_confirmation && (
                            <p className="text-xs text-destructive">{errors.password_confirmation}</p>
                        )}
                    </div>

                    <div className="flex items-center justify-end">
                        <Button type="submit" disabled={processing}>
                            {t('reset_password', 'Reset Password')}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
