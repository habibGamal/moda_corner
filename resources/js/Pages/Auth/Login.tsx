import { Head, Link, router, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Checkbox } from "@/Components/ui/checkbox";
import { Label } from "@/Components/ui/label";
import { useI18n } from "@/hooks/use-i18n";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { Facebook, Chrome } from "lucide-react";

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { t } = useI18n();

    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <>
            <Head title={t("login", "Log in")} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">
                        {t("login", "Log in")}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t(
                            "login_description",
                            "Enter your information to access your account"
                        )}
                    </p>
                </div>

                {status && (
                    <Alert variant="success" className="my-6">
                        <AlertDescription>{status}</AlertDescription>
                    </Alert>
                )}

                {/* Social login section first */}
                <Button
                    asChild
                    className="flex items-center justify-center gap-2 w-full bg-[#DB4437] text-white hover:bg-[#c23321] focus:ring-2 focus:ring-red-500"
                >
                    <a href={route("social.login", { provider: "google" })}>
                        <Chrome className="h-4 w-4" />
                        Google
                    </a>
                </Button>

                <div className="relative my-4">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-muted"></div>
                    </div>
                    <div className="relative flex justify-center text-xs uppercase">
                        <span className="bg-background px-2 text-muted-foreground">
                            {t("or_continue_with", "Or continue with")}
                        </span>
                    </div>
                </div>
                {/* Login form below social login */}
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">{t("email", "Email")}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            autoComplete="username"
                            autoFocus
                            onChange={(e) => setData("email", e.target.value)}
                            className={errors.email ? "border-destructive" : ""}
                        />
                        {errors.email && (
                            <p className="text-xs text-destructive">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <Label htmlFor="password">
                                {t("password", "Password")}
                            </Label>
                            {canResetPassword && (
                                <Link
                                    href={route("password.request")}
                                    className="text-xs text-primary hover:underline"
                                >
                                    {t(
                                        "forgot_password",
                                        "Forgot your password?"
                                    )}
                                </Link>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            autoComplete="current-password"
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            className={
                                errors.password ? "border-destructive" : ""
                            }
                        />
                        {errors.password && (
                            <p className="text-xs text-destructive">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    <div className="flex items-center space-x-2 rtl:space-x-reverse">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            // @ts-ignore - known issue with checkbox type
                            onCheckedChange={(checked) =>
                                setData("remember", Boolean(checked))
                            }
                        />
                        <Label
                            htmlFor="remember"
                            className="text-sm font-normal"
                        >
                            {t("remember_me", "Remember me")}
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={processing}
                    >
                        {t("login", "Log in")}
                    </Button>

                    <div className="text-center text-sm">
                        <span className="text-muted-foreground">
                            {t("dont_have_account", "Don't have an account?")}{" "}
                        </span>
                        <Link
                            href={route("register")}
                            className="text-primary hover:underline"
                        >
                            {t("register", "Register")}
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}
