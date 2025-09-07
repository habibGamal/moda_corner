import React from 'react';
import { useSiteBranding, useSettings } from '@/hooks/useSettings';
import { resolveStoragePath } from '@/utils/storageUtils';
import { Facebook, X, Instagram, Linkedin, Youtube, Music2 } from 'lucide-react';

interface SiteLogoProps {
    className?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    showTitle?: boolean;
}

/**
 * Component to display site logo with fallback to title
 */
export function SiteLogo({ className = '', size = 'md', showTitle = true }: SiteLogoProps) {
    const { title, logo } = useSiteBranding();

    const sizeClasses = {
        sm: 'h-8 w-auto',
        md: 'h-12 w-auto',
        lg: 'h-16 w-auto',
        xl: 'h-24 w-auto',
    };

    // Resolve logo path using utility
    const resolvedLogo = resolveStoragePath(logo);

    if (resolvedLogo) {
        return (
            <div className={`flex items-center gap-3 ${className}`}>
                <img
                    src={resolvedLogo}
                    alt={title}
                    className={sizeClasses[size] + ' rounded-xl'}
                />
                {showTitle && (
                    <span className="font-bold text-lg">{title}</span>
                )}
            </div>
        );
    }

    return (
        <div className={`flex items-center ${className}`}>
            <h1 className="font-bold text-xl">{title}</h1>
        </div>
    );
}

interface SiteIconProps {
    className?: string;
    size?: number;
}

/**
 * Component to display site favicon
 */
export function SiteIcon({ className = '', size = 32 }: SiteIconProps) {
    const settings = useSettings();
    const { title } = useSiteBranding();

    // Resolve icon path using utility
    const resolvedIcon = resolveStoragePath(settings.site_icon);

    if (resolvedIcon) {
        return (
            <img
                src={resolvedIcon}
                alt={`${title} icon`}
                width={size}
                height={size}
                className={className}
            />
        );
    }

    // Fallback to first letter of title
    return (
        <div
            className={`inline-flex items-center justify-center rounded bg-primary text-primary-foreground font-bold ${className}`}
            style={{ width: size, height: size }}
        >
            {title.charAt(0).toUpperCase()}
        </div>
    );
}

interface SocialLinksProps {
    className?: string;
    iconSize?: number;
    showLabels?: boolean;
}

/**
 * Component to display social media links
 */
export function SocialLinks({ className = '', iconSize = 24, showLabels = false }: SocialLinksProps) {
    const settings = useSettings();
    const socialLinks = JSON.parse(settings.social_links || "{}");
    const socialPlatforms = [
        { key: 'facebook', label: 'Facebook', icon: Facebook },
        { key: 'twitter', label: 'X (Twitter)', icon: X },
        { key: 'instagram', label: 'Instagram', icon: Instagram },
        { key: 'linkedin', label: 'LinkedIn', icon: Linkedin },
        { key: 'youtube', label: 'YouTube', icon: Youtube },
        { key: 'tiktok', label: 'TikTok', icon: Music2 },
    ];

    const activePlatforms = socialPlatforms.filter(platform =>
        socialLinks[platform.key] && socialLinks[platform.key].trim() !== ''
    );

    if (activePlatforms.length === 0) {
        return null;
    }

    return (
        <div className={`flex gap-3 ${className}`}>
            {activePlatforms.map((platform) => {
                const IconComponent = platform.icon;
                return (
                    <a
                        key={platform.key}
                        href={socialLinks[platform.key]}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-muted-foreground hover:text-foreground transition-colors"
                    >
                        {showLabels ? (
                            <span className="flex items-center gap-2">
                                <IconComponent size={iconSize} />
                                {platform.label}
                            </span>
                        ) : (
                            <IconComponent size={iconSize} />
                        )}
                    </a>
                );
            })}
        </div>
    );
}

/**
 * Component to display contact information
 */
export function ContactInfo({ className = '' }: { className?: string }) {
    const settings = useSettings();

    if (!settings.contact_email) {
        return null;
    }

    return (
        <div className={className}>
            <a
                href={`mailto:${settings.contact_email}`}
                className="text-muted-foreground hover:text-foreground transition-colors"
            >
                {settings.contact_email}
            </a>
        </div>
    );
}

/**
 * Higher-order component to show maintenance message when maintenance mode is enabled
 */
export function MaintenanceWrapper({ children }: { children: React.ReactNode }) {
    const settings = useSettings();

    if (settings.maintenance_mode) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-muted">
                <div className="max-w-md mx-auto text-center p-6">
                    <SiteLogo className="justify-center mb-6" />
                    <h1 className="text-2xl font-bold mb-4">Under Maintenance</h1>
                    <p className="text-muted-foreground mb-6">
                        We're currently performing scheduled maintenance.
                        Please check back later.
                    </p>
                    <ContactInfo />
                </div>
            </div>
        );
    }

    return <>{children}</>;
}
