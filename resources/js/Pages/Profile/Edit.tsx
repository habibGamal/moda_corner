import { App } from "@/types";
import { Head } from "@inertiajs/react";
import { Card } from "@/Components/ui/card";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdateProfileInformation from "./Partials/UpdateProfileInformationForm";
import LanguageSwitcher from "@/Components/LanguageSwitcher";
import { useI18n } from "@/hooks/use-i18n";
import { PageTitle } from "@/Components/ui/page-title";
import { UserCog, Languages } from "lucide-react";

export default function Edit({
    mustVerifyEmail,
    status,
}: { mustVerifyEmail: boolean; status?: string }) {
    const { t } = useI18n();

    return (
        <div className="container mt-4">
            <Head title={t("profile", "Profile")} />

            <PageTitle
                title={t("profile", "Profile")}
                icon={<UserCog className="h-6 w-6 text-primary" />}
            />

            <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <Card className="p-4 sm:p-8">
                    <UpdateProfileInformation
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-xl"
                    />
                </Card>

                <Card className="p-4 sm:p-8">
                    <UpdatePasswordForm className="max-w-xl" />
                </Card>

                <Card className="p-4 sm:p-8">
                    <div className="max-w-xl">
                        <header className="mb-4">
                            <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <Languages className="h-5 w-5 text-primary" />
                                {t("language_preferences", "Language Preferences")}
                            </h2>
                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {t("language_preferences_description", "Choose your preferred language for the interface.")}
                            </p>
                        </header>
                        <div className="flex items-center gap-4">
                            <span className="text-sm font-medium">
                                {t("select_language", "Select Language")}:
                            </span>
                            <LanguageSwitcher />
                        </div>
                    </div>
                </Card>

                <Card className="p-4 sm:p-8">
                    <DeleteUserForm className="max-w-xl" />
                </Card>
            </div>
        </div>
    );
}
