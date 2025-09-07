import { useForm as useInertiaForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/Components/ui/form";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter
} from "@/Components/ui/dialog";
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import * as z from 'zod';
import { useI18n } from '@/hooks/use-i18n';

export default function DeleteUserForm({
    className = '',
}: {
    className?: string;
}) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);
    const { t } = useI18n();

    // Form validation schema
    const formSchema = z.object({
        password: z.string().min(1, { message: t('password_required_deletion', 'Password is required to confirm deletion') }),
    });

    // React Hook Form with Zod validation
    const form = useForm<z.infer<typeof formSchema>>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            password: '',
        },
    });

    // Inertia form handling
    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset: resetInertia,
        errors,
        clearErrors,
    } = useInertiaForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    // Submit handler that syncs react-hook-form with Inertia
    const onSubmit = (values: z.infer<typeof formSchema>) => {
        setData(values);

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => {
                if (errors.password) {
                    form.setError('password', { message: errors.password });
                }
                passwordInput.current?.focus();
            },
            onFinish: () => resetInertia(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        form.reset();
        clearErrors();
        resetInertia();
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-medium">
                    {t('delete_account', 'Delete Account')}
                </h2>

                <p className="mt-1 text-sm text-muted-foreground">
                    {t('delete_account_warning',
                        'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'
                    )}
                </p>
            </header>

            <Button
                variant="destructive"
                onClick={confirmUserDeletion}
            >
                {t('delete_account', 'Delete Account')}
            </Button>

            <Dialog open={confirmingUserDeletion} onOpenChange={(open) => {
                if (!open) closeModal();
            }}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>
                            {t('confirm_delete_account', 'Are you sure you want to delete your account?')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('delete_account_confirmation',
                                'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.'
                            )}
                        </DialogDescription>
                    </DialogHeader>

                    <Form {...form}>
                        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                            <FormField
                                control={form.control}
                                name="password"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormControl>
                                            <Input
                                                id="password"
                                                type="password"
                                                className="w-full"
                                                autoFocus
                                                placeholder={t('password', 'Password')}
                                                {...field}
                                                ref={(e) => {
                                                    field.ref(e);
                                                    // @ts-ignore - set ref for both field and passwordInput
                                                    passwordInput.current = e;
                                                }}
                                            />
                                        </FormControl>
                                        <FormMessage />
                                        {errors.password && (
                                            <p className="text-sm text-destructive">{errors.password}</p>
                                        )}
                                    </FormItem>
                                )}
                            />

                            <DialogFooter className="sm:justify-end">
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={closeModal}
                                >
                                    {t('cancel', 'Cancel')}
                                </Button>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    {t('delete_account', 'Delete Account')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </Form>
                </DialogContent>
            </Dialog>
        </section>
    );
}
