import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { router } from '@inertiajs/react';

// Import frontend translations
import englishTranslations from '../translations/en.json';
import arabicTranslations from '../translations/ar.json';

type Direction = 'ltr' | 'rtl';

interface Language {
  code: string;
  name: string;
  direction: Direction;
}

interface LanguageContextType {
  currentLocale: string;
  direction: Direction;
  languages: Language[];
  setLanguage: (langCode: string) => void;
  t: (key: string, fallback?: string, variables?: Record<string, any>) => string;
  getLocalizedField: (obj: any, fieldName: string) => string | undefined;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

// Frontend translations
const frontendTranslations: Record<string, Record<string, string>> = {
  en: englishTranslations,
  ar: arabicTranslations,
};

const languages: Language[] = [
  { code: 'en', name: 'English', direction: 'ltr' },
  { code: 'ar', name: 'العربية', direction: 'rtl' },
];

export function LanguageProvider({ children }: { children: ReactNode }) {
  // Get initial locale from localStorage or default to 'en'
  const initialLocale = typeof window !== 'undefined' ? localStorage.getItem('language') || 'en' : 'en';
  const [currentLocale, setCurrentLocale] = useState<string>(initialLocale);
  const [direction, setDirection] = useState<Direction>(initialLocale === 'ar' ? 'rtl' : 'ltr');
  const [backendTranslations, setBackendTranslations] = useState<Record<string, string>>({});

  useEffect(() => {
    // Set document direction and language on mount and when locale changes
    document.documentElement.dir = direction;
    document.documentElement.lang = currentLocale;

    // Watch for Inertia page updates to get any backend-specific translations
    // (For database content like brands that still need backend translation)
    const handlePageUpdate = (e: any) => {
      if (e.detail.page?.props) {
        const pageProps = e.detail.page.props;

        // Store backend translations separately (for database content)
        if (pageProps.translations) {
          setBackendTranslations(pageProps.translations);
        }

        // Update locale if different from current and not overridden by user
        if (pageProps.locale && !localStorage.getItem('language')) {
          const newLocale = pageProps.locale;
          setCurrentLocale(newLocale);
          setDirection(newLocale === 'ar' ? 'rtl' : 'ltr');
          document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr';
          document.documentElement.lang = newLocale;
        }
      }
    };

    // Listen for Inertia page updates
    document.addEventListener('inertia:success', handlePageUpdate);

    return () => {
      document.removeEventListener('inertia:success', handlePageUpdate);
    };
  }, [direction, currentLocale]);

  // Function to set a new language
  const setLanguage = (langCode: string) => {
    const newDirection = langCode === 'ar' ? 'rtl' : 'ltr';

    setCurrentLocale(langCode);
    setDirection(newDirection);
    localStorage.setItem('language', langCode);

    // Set HTML direction and lang attributes
    document.documentElement.dir = newDirection;
    document.documentElement.lang = langCode;

    // Refresh the page with the new language
    router.reload({
      only: ['translations'],
      data: { locale: langCode },
    });
  };

  // Updated translation function with variable substitution support
  const t = (key: string, fallback?: string, variables?: Record<string, any>): string => {
    // Get the translation string
    let translatedText: string;

    // First check frontend translations
    if (frontendTranslations[currentLocale] && frontendTranslations[currentLocale][key]) {
      translatedText = frontendTranslations[currentLocale][key];
    }
    // Then check backend translations (for any dynamic content still coming from backend)
    else if (backendTranslations[key]) {
      translatedText = backendTranslations[key];
    }
    // Fall back to provided fallback or the key itself
    else {
      translatedText = fallback || key;
    }

    // Replace variables if provided
    if (variables) {
      Object.entries(variables).forEach(([varName, varValue]) => {
        const regex = new RegExp(`\\{\\{${varName}\\}\\}`, 'g');
        translatedText = translatedText.replace(regex, String(varValue));
      });
    }

    return translatedText;
  };

  // Helper function for getting localized fields from database (like name_en, name_ar)
  const getLocalizedField = (obj: any, fieldName: string): string | undefined => {
    if (!obj) return undefined;

    const localizedFieldKey = `${fieldName}_${currentLocale}`;
    if (obj[localizedFieldKey] !== undefined) {
      return obj[localizedFieldKey];
    }

    // Fall back to English if the current locale field doesn't exist
    const englishFieldKey = `${fieldName}_en`;
    return obj[englishFieldKey];
  };

  return (
    <LanguageContext.Provider
      value={{
        currentLocale,
        direction,
        languages,
        setLanguage,
        t,
        getLocalizedField,
      }}
    >
      {children}
    </LanguageContext.Provider>
  );
}

// Custom hook to use the language context
export function useLanguage() {
  const context = useContext(LanguageContext);
  if (context === undefined) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
}
