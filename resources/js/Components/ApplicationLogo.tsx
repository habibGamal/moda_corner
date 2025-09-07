import { SiteLogo } from '@/Components/Settings/SettingsComponents';

interface ApplicationLogoProps {
    className?: string;
    showTitle?: boolean;
}

export default function ApplicationLogo({ className, showTitle = false }: ApplicationLogoProps) {
    return (
        <SiteLogo
            className={className}
            size="md"
            showTitle={showTitle}
        />
    );
}
