import { Moon, Sun, Monitor } from "lucide-react";
import { Button } from "@/Components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { useTheme } from "@/Contexts/ThemeContext";
import { useI18n } from "@/hooks/use-i18n";

export default function ThemeToggle() {
    const { theme, setTheme } = useTheme();
    const { t } = useI18n();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    aria-label={t("toggle_theme", "Toggle theme")}
                >
                    <Sun className="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                    <Moon className="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                    <span className="sr-only">{t("toggle_theme", "Toggle theme")}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    onClick={() => setTheme("light")}
                    className="cursor-pointer"
                >
                    <Sun className="h-4 w-4 ltr:mr-2 rtl:ml-2" />
                    <span>{t("light", "Light")}</span>
                    {theme === "light" && (
                        <span className="ltr:ml-auto rtl:mr-auto">✓</span>
                    )}
                </DropdownMenuItem>
                <DropdownMenuItem
                    onClick={() => setTheme("dark")}
                    className="cursor-pointer"
                >
                    <Moon className="h-4 w-4 ltr:mr-2 rtl:ml-2" />
                    <span>{t("dark", "Dark")}</span>
                    {theme === "dark" && (
                        <span className="ltr:ml-auto rtl:mr-auto">✓</span>
                    )}
                </DropdownMenuItem>
                <DropdownMenuItem
                    onClick={() => setTheme("system")}
                    className="cursor-pointer"
                >
                    <Monitor className="h-4 w-4 ltr:mr-2 rtl:ml-2" />
                    <span>{t("system", "System")}</span>
                    {theme === "system" && (
                        <span className="ltr:ml-auto rtl:mr-auto">✓</span>
                    )}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
