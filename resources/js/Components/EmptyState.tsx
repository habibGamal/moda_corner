import { Box, PackageOpen } from "lucide-react";
import { Button } from "./ui/button";

interface EmptyStateProps {
    title: string;
    description?: string;
    icon?: React.ReactNode;
    action?: {
        label: string;
        onClick: () => void;
    };
}

export default function EmptyState({
    title,
    description,
    icon = <PackageOpen className="h-10 w-10 text-muted-foreground" />,
    action,
}: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div className="rounded-full bg-muted p-3 mb-4">{icon}</div>
            <h3 className="text-lg font-medium">{title}</h3>
            {description && <p className="text-muted-foreground mt-2 max-w-sm">{description}</p>}
            {action && (
                <Button onClick={action.onClick} className="mt-4">
                    {action.label}
                </Button>
            )}
        </div>
    );
}
