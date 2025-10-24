import { usePage } from '@inertiajs/react';
import { PageProps } from '@inertiajs/core';

export interface SiteConfig {
    site_title: string;
    site_logo?: string;
    site_logo_dark?: string;
    site_icon?: string;
    maintenance_mode: boolean;
    contact_email?: string;
    social_links: string;
    facebook_pixel_url?: string;
    facebook_pixel_id?: string;
    show_privacy_policy?: boolean;
    show_return_policy?: boolean;
    show_terms_of_service?: boolean;
    show_contact_page?: boolean;
    show_exchange_policy?: boolean;
    show_about_us?: boolean;
    show_delivery_policy?: boolean;
    show_shipping_policy?: boolean;
    soon_banner?: boolean;
}

interface PagePropsWithSettings extends PageProps {
    settings?: SiteConfig;
    [key: string]: any;
}

/**
 * Custom hook to access site settings from Inertia page props
 */
export function useSettings(): SiteConfig {
    const { props } = usePage<PagePropsWithSettings>();
    return props.settings || {
        site_title: 'Vilain',
        maintenance_mode: false,
        social_links: "{}",
    };
}

/**
 * Get a specific setting value with fallback
 */
export function getSetting<T = any>(key: keyof SiteConfig, fallback?: T): T {
    const settings = useSettings();
    const value = settings[key];
    return (value !== undefined ? value : fallback) as T;
}

/**
 * Check if maintenance mode is enabled
 */
export function useMaintenanceMode(): boolean {
    return getSetting('maintenance_mode', false);
}

/**
 * Get social media links
 */
export function useSocialLinks(): Record<string, string> {
    return getSetting('social_links', {});
}

/**
 * Get site branding information
 */
export function useSiteBranding() {
    const settings = useSettings();
    return {
        title: settings.site_title || 'Vilain',
        logo: settings.site_logo,
        logoDark: settings.site_logo_dark,
        icon: settings.site_icon,
    };
}
/**
 * Higher-order component to provide settings to any component
 */
export function withSettings<P extends object>(
    Component: React.ComponentType<P & { settings: SiteConfig }>
) {
    return function WrappedComponent(props: P) {
        const settings = useSettings();
        return <Component {...props} settings={settings} />;
    };
}
