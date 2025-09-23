import { useTranslation } from "react-i18next";
import { router } from "@inertiajs/react";

interface Language {
    code: string;
    name: string;
    direction: "ltr" | "rtl";
}

const languages: Language[] = [
    { code: "en", name: "English", direction: "ltr" },
    { code: "ar", name: "العربية", direction: "rtl" },
];

export function useI18n() {
    const { t, i18n } = useTranslation();

    // Get current locale
    const currentLocale = i18n.language;

    // Get current direction
    const direction = (currentLocale === "ar" ? "rtl" : "ltr") as "ltr" | "rtl";

    // Function to change language
    const setLanguage = (langCode: string) => {
        i18n.changeLanguage(langCode);

        // Set HTML direction and lang attributes
        const newDirection = langCode === "ar" ? "rtl" : "ltr";
        document.documentElement.dir = newDirection;
        document.documentElement.lang = langCode;

        // Store in localStorage (this is handled by i18next-browser-languagedetector)
        localStorage.setItem("language", langCode);

        // Refresh the page with the new language for any backend data
        router.reload({
            only: ["translations"],
            data: { locale: langCode },
        });
    };

    // Enhanced translation function with fallback support
    const translate = (
        key: string,
        fallback?: string,
        variables?: Record<string, any>
    ): string => {
        const translation = t(key, {
            defaultValue: fallback || key,
            ...variables,
        });
        return translation;
    };

    // Helper function for getting localized fields from database (like name_en, name_ar)
    const getLocalizedField = (
        obj: any,
        fieldName: string
    ): string | undefined => {
        if (!obj) return undefined;

        const localizedFieldKey = `${fieldName}_${currentLocale}`;
        if (obj[localizedFieldKey] !== undefined) {
            return obj[localizedFieldKey];
        }

        // Fall back to English if the current locale field doesn't exist
        const englishFieldKey = `${fieldName}_en`;
        return obj[englishFieldKey];
    };

    return {
        currentLocale,
        direction,
        languages,
        setLanguage,
        t: translate,
        getLocalizedField,
        // Expose the original i18n instance for advanced usage
        i18n,
    };
}
