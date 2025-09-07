import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/Components/ui/form";
import { useToast } from '@/hooks/use-toast';
import { Input } from '@/Components/ui/input';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm as useHookForm } from 'react-hook-form';
import * as z from 'zod';
import { useI18n } from '@/hooks/use-i18n';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}) {
    // @ts-ignore - Type issue with usePage
    const user = usePage().props.auth.user;
    const { toast } = useToast();
    const { t } = useI18n();

    // Inertia form for submission
    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    // Form validation schema
    const formSchema = z.object({
        name: z.string().min(2, { message: t('name_min_length', 'Name must be at least 2 characters.') }),
        email: z.string().email({ message: t('valid_email', 'Please enter a valid email address.') }),
    });

    // React Hook Form with Zod validation
    const form = useHookForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            name: user.name,
            email: user.email,
        },
    });

    // Submit handler that syncs react-hook-form with Inertia
    const onSubmit = (values: z.infer<typeof formSchema>) => {
        setData(values);
        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium">
                    {t('profile_information', 'Profile Information')}
                </h2>

                <p className="mt-1 text-sm text-muted-foreground">
                    {t('update_profile_information', 'Update your account\'s profile information and email address.')}
                </p>
            </header>

            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="mt-6 space-y-6">
                    <FormField
                        control={form.control}
                        name="name"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel htmlFor="name">{t('name', 'Name')}</FormLabel>
                                <FormControl>
                                    <Input
                                        id="name"
                                        autoFocus
                                        autoComplete="name"
                                        {...field}
                                    />
                                </FormControl>
                                <FormMessage />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </FormItem>
                        )}
                    />

                    <FormField
                        control={form.control}
                        name="email"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel htmlFor="email">{t('email', 'Email')}</FormLabel>
                                <FormControl>
                                    <Input
                                        id="email"
                                        type="email"
                                        autoComplete="username"
                                        {...field}
                                    />
                                </FormControl>
                                <FormMessage />
                                {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                            </FormItem>
                        )}
                    />

                    {mustVerifyEmail && user.email_verified_at === null && (
                        <Alert variant="default" className="mt-2">
                            <AlertDescription>
                                {t('verify_email_description', 'Your email address is unverified.')}{' '}
                                <Link
                                    href={route('verification.send')}
                                    method="post"
                                    as="button"
                                    className="text-primary underline hover:text-primary/80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    {t('click_resend_verification', 'Click here to re-send the verification email.')}
                                </Link>
                            </AlertDescription>
                        </Alert>
                    )}

                    {status === 'verification-link-sent' && (
                        <Alert variant="default" className="mt-2 border-green-500 bg-green-50 dark:bg-green-950/50">
                            <AlertDescription className="text-sm font-medium">
                                {t('verification_link_sent', 'A new verification link has been sent to your email address.')}
                            </AlertDescription>
                        </Alert>
                    )}

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
