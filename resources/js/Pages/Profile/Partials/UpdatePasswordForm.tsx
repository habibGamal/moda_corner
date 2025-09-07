import { useForm as useInertiaForm } from '@inertiajs/react';
import { useRef } from 'react';
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/Components/ui/form";
import { useToast } from '@/hooks/use-toast';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import * as z from 'zod';
import { useI18n } from '@/hooks/use-i18n';

export default function UpdatePasswordForm({
    className = '',
}: {
    className?: string;
}) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const { toast } = useToast();
    const { t } = useI18n();

    // Inertia form handling
    const {
        data,
        setData,
        errors,
        put,
        reset: resetInertia,
        processing,
        recentlySuccessful,
    } = useInertiaForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    // Form validation schema
    const formSchema = z.object({
        current_password: z.string().min(1, { message: t('current_password_required', 'Current password is required') }),
        password: z.string().min(8, { message: t('password_min_length', 'Password must be at least 8 characters') }),
        password_confirmation: z.string().min(1, { message: t('confirm_password_required', 'Please confirm your password') }),
    }).refine((data) => data.password === data.password_confirmation, {
        message: t('passwords_dont_match', "Passwords don't match"),
        path: ["password_confirmation"],
    });

    // React Hook Form with Zod validation
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            current_password: '',
            password: '',
            password_confirmation: '',
        },
    });

    // Submit handler that syncs react-hook-form with Inertia
    const onSubmit = (values: z.infer<typeof formSchema>) => {
        setData(values);

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                resetInertia();
                toast({
                    description: t('password_updated', "Password updated successfully."),
                });
            },
            onError: (errors) => {
                if (errors.password) {
                    resetInertia('password', 'password_confirmation');
                    form.setError('password', { message: errors.password });
                }

                if (errors.current_password) {
                    resetInertia('current_password');
                    form.setError('current_password', { message: errors.current_password });
                }
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium">
                    {t('update_password', 'Update Password')}
                </h2>

                <p className="mt-1 text-sm text-muted-foreground">
                    {t('ensure_account_security', 'Ensure your account is using a long, random password to stay secure.')}
                </p>
            </header>

            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="mt-6 space-y-6">
                    <FormField
                        control={form.control}
                        name="current_password"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel htmlFor="current_password">{t('current_password', 'Current Password')}</FormLabel>
                                <FormControl>
                                    <Input
                                        id="current_password"
                                        type="password"
                                        autoComplete="current-password"
                                        {...field}
                                    />
                                </FormControl>
                                <FormMessage />
                                {errors.current_password && (
                                    <p className="text-sm text-destructive">{errors.current_password}</p>
                                )}
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="password"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel htmlFor="password">{t('new_password', 'New Password')}</FormLabel>
                                <FormControl>
                                    <Input
                                        id="password"
                                        type="password"
                                        autoComplete="new-password"
                                        {...field}
                                    />
                                </FormControl>
                                <FormMessage />
                                {errors.password && (
                                    <p className="text-sm text-destructive">{errors.password}</p>
                                )}
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="password_confirmation"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel htmlFor="password_confirmation">{t('confirm_password', 'Confirm Password')}</FormLabel>
                                <FormControl>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        autoComplete="new-password"
                                        {...field}
                                    />
                                </FormControl>
                                <FormMessage />
                                {errors.password_confirmation && (
                                    <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                                )}
                            </FormItem>
                        )}
                    />

                    <div className="flex items-center gap-4">
                        <Button
                            type="submit"
                            disabled={processing}
                        >
                            {t('save', 'Save')}
                        </Button>

                        {recentlySuccessful && (
                            <p className="text-sm text-muted-foreground">
                                {t('saved', 'Saved.')}
                            </p>
                        )}
                    </div>
                </form>
            </Form>
        </section>
    );
}
