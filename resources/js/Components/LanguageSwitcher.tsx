import { Button } from '@/Components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { useI18n } from '@/hooks/use-i18n';
import { Languages } from 'lucide-react';

interface LanguageSwitcherProps {
  className?: string;
}

export default function LanguageSwitcher({ className }: LanguageSwitcherProps) {
  const { currentLocale, languages, setLanguage } = useI18n();

  // Find the current language object to display its name
  const currentLanguage = languages.find(lang => lang.code === currentLocale);

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="outline"
          className={`flex items-center gap-2 ${className}`}
          aria-label="Switch language"
        >
          <Languages className="h-4 w-4" />
          <span className="text-sm font-medium">
            {currentLanguage?.name || 'Language'}
          </span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {languages.map((language) => (
          <DropdownMenuItem
            key={language.code}
            className={currentLocale === language.code ? "bg-muted" : ""}
            onClick={() => setLanguage(language.code)}
          >
            {language.name}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
