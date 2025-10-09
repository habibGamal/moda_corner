import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useI18n } from '@/hooks/use-i18n';

export default function ConfirmPassword() {
    const { t } = useI18n();

    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="container mt-4">
            <Head title={t('confirm_password', 'Confirm Password')} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">{t('confirm_password', 'Confirm Password')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('secure_area_confirmation', 'This is a secure area of the application. Please confirm your password before continuing.')}
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="password">{t('password', 'Password')}</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            autoComplete="current-password"
                            autoFocus
                            onChange={(e) => setData('password', e.target.value)}
                            className={errors.password ? "border-destructive" : ""}
                        />
                        {errors.password && (
                            <p className="text-xs text-destructive">{errors.password}</p>
                        )}
                    </div>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing}>
                            {t('confirm', 'Confirm')}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
