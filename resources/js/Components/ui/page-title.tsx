import { ReactNode } from "react";
import { Button } from "@/Components/ui/button";
import { useI18n } from "@/hooks/use-i18n";
import { Link } from "@inertiajs/react";
import { ArrowLeft, ArrowRight } from "lucide-react";

interface PageTitleProps {
    title: string;
    icon?: ReactNode;
    backUrl?: string;
    backText?: string;
    actions?: ReactNode;
    className?: string;
}

export function PageTitle({
    title,
    icon,
    backUrl,
    backText,
    actions,
    className = "",
}: PageTitleProps) {
    const { t, direction } = useI18n();
    const ArrowIcon = direction === "rtl" ? ArrowRight : ArrowLeft;

    return (
        <div className={`flex flex-col gap-2 mb-8 ${className}`}>
            {backUrl && (
                <Button
                    variant="ghost"
                    size="sm"
                    asChild
                    className="self-start hover:bg-transparent px-0 mb-1"
                >
                    <Link href={backUrl}>
                        <ArrowIcon className="w-4 h-4 mr-2" />
                        {backText || t("back", "Back")}
                    </Link>
                </Button>
            )}
            <div className="flex items-center justify-between">
                <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                    {icon}
                    {title}
                </h1>
                {actions}
            </div>
        </div>
    );
}
