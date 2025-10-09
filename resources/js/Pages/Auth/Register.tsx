import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useI18n } from "@/hooks/use-i18n";
import { Facebook, Chrome } from "lucide-react";

export default function Register() {
    const { t } = useI18n();

    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("register"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    return (
        <div className="container mt-4">
            <Head title={t("register", "Register")} />

            <div className="mx-auto max-w-md space-y-6 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold tracking-tight">
                        {t("create_account", "Create an account")}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t(
                            "register_description",
                            "Enter your information to create an account"
                        )}
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">{t("name", "Name")}</Label>
                        <Input
                            id="name"
                            name="name"
                            value={data.name}
                            autoComplete="name"
                            autoFocus
                            onChange={(e) => setData("name", e.target.value)}
                            className={errors.name ? "border-destructive" : ""}
                        />
                        {errors.name && (
                            <p className="text-xs text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="email">{t("email", "Email")}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            autoComplete="username"
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
                        <Label htmlFor="password">
                            {t("password", "Password")}
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            autoComplete="new-password"
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

                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation">
                            {t("confirm_password", "Confirm Password")}
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData("password_confirmation", e.target.value)
                            }
                            className={
                                errors.password_confirmation
                                    ? "border-destructive"
                                    : ""
                            }
                        />
                        {errors.password_confirmation && (
                            <p className="text-xs text-destructive">
                                {errors.password_confirmation}
                            </p>
                        )}
                    </div>

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={processing}
                    >
                        {t("register", "Register")}
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

                    <Button
                        variant="outline"
                        onClick={() =>
                            (window.location.href = route("social.login", {
                                provider: "google",
                            }))
                        }
                        className="flex items-center justify-center"
                    >
                        <Chrome className="mr-2 h-4 w-4" />
                        Google
                    </Button>

                    <div className="text-center text-sm">
                        <span className="text-muted-foreground">
                            {t("already_registered", "Already registered?")}{" "}
                        </span>
                        <Link
                            href={route("login")}
                            className="text-primary hover:underline"
                        >
                            {t("login", "Log in")}
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    );
}
