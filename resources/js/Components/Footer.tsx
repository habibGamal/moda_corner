import React from 'react';
import { Link } from '@inertiajs/react';
import { SocialLinks, ContactInfo, SiteLogo } from '@/Components/Settings/SettingsComponents';
import { useI18n } from '@/hooks/use-i18n';
import { useSettings } from '@/hooks/useSettings';

export default function Footer() {
    const { t } = useI18n();
    const settings = useSettings();

    // Get policy page visibility settings
    const showPrivacyPolicy = settings.show_privacy_policy !== false;
    const showReturnPolicy = settings.show_return_policy !== false;
    const showTermsOfService = settings.show_terms_of_service !== false;

    // Check if any policy links should be shown
    const hasPolicyLinks = showPrivacyPolicy || showReturnPolicy || showTermsOfService;

    return (
        <footer className="bg-muted/50 dark:bg-muted/30 border-t dark:border-border/50 mt-auto mb-12 lg:mb-0">
            <div className="container px-4 py-8">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {/* Logo & Description */}
                    <div className="space-y-4">
                        <SiteLogo showTitle={true} />
                        <p className="text-sm text-muted-foreground dark:text-muted-foreground/80">
                            {t('footer_description', 'Your trusted online shopping destination')}
                        </p>
                        <ContactInfo />
                    </div>

                    {/* Quick Links */}
                    <div className="space-y-4">
                        <h3 className="font-semibold">{t('quick_links', 'Quick Links')}</h3>
                        <ul className="space-y-2 text-sm">
                            <li>
                                <a href="/categories" className="text-muted-foreground hover:text-foreground transition-colors">
                                    {t('categories', 'Categories')}
                                </a>
                            </li>
                            <li>
                                <a href="/brands" className="text-muted-foreground hover:text-foreground transition-colors">
                                    {t('brands', 'Brands')}
                                </a>
                            </li>
                            <li>
                                <a href="/orders" className="text-muted-foreground hover:text-foreground transition-colors">
                                    {t('my_orders', 'My Orders')}
                                </a>
                            </li>
                            <li>
                                <a href="/wishlist" className="text-muted-foreground hover:text-foreground transition-colors">
                                    {t('wishlist', 'Wishlist')}
                                </a>
                            </li>
                        </ul>
                    </div>

                    {/* Social Links */}
                    <div className="space-y-4">
                        <h3 className="font-semibold">{t('follow_us', 'Follow Us')}</h3>
                        <SocialLinks showLabels={true} className="flex-col items-start" />
                    </div>
                </div>

                <hr className="my-6" />

                <div className="flex flex-col md:flex-row justify-between items-center text-sm text-muted-foreground">
                    <p>
                        © {new Date().getFullYear()} {t('all_rights_reserved', 'All rights reserved')}
                    </p>
                    {hasPolicyLinks && (
                        <div className="flex gap-4 mt-4 md:mt-0">
                            {showPrivacyPolicy && (
                                <Link href="/privacy" className="hover:text-foreground transition-colors">
                                    {t('privacy_policy', 'Privacy Policy')}
                                </Link>
                            )}
                            {showReturnPolicy && (
                                <Link href="/returns" className="hover:text-foreground transition-colors">
                                    {t('return_policy', 'Return Policy')}
                                </Link>
                            )}
                            {showTermsOfService && (
                                <Link href="/terms" className="hover:text-foreground transition-colors">
                                    {t('terms_of_service', 'Terms of Service')}
                                </Link>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </footer>
    );
}
