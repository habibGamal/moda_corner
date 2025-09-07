import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useI18n } from '@/hooks/use-i18n';

interface PolicyPageProps {
    content: {
        en: string;
        ar: string;
    };
    title: {
        en: string;
        ar: string;
    };
}

export default function PolicyPage({ content, title }: PolicyPageProps) {
    const { currentLocale, direction } = useI18n();

    // Get the content for the current locale, fallback to English
    const localizedContent = content[currentLocale as 'en' | 'ar'] || content.en || '';
    const localizedTitle = title[currentLocale as 'en' | 'ar'] || title.en || '';

    return (
        <>
            <Head title={localizedTitle} />

            {/* Hero Section */}
            <div className="relative bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
                <div className="relative">
                    <div className="container mx-auto px-4 py-16 sm:py-24">
                        <div className="text-center">
                            <div className="mx-auto mb-6 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 p-0.5">
                                <div className="flex h-full w-full items-center justify-center rounded-full bg-white dark:bg-slate-900">
                                    <svg className="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            </div>
                            <h1 className="text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                                <span className="bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
                                    {localizedTitle}
                                </span>
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                                {currentLocale === 'ar'
                                    ? 'نحن ملتزمون بالشفافية وحماية حقوقك. اقرأ سياساتنا بعناية.'
                                    : 'We are committed to transparency and protecting your rights. Please read our policies carefully.'
                                }
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="bg-white dark:bg-slate-900">
                <div className="container mx-auto px-4 py-16">
                    <div className="mx-auto max-w-4xl">
                        <Card className="border-0 bg-white/70 shadow-2xl backdrop-blur-sm dark:bg-slate-800/70 dark:shadow-slate-900/50">
                            <div className="absolute inset-0 rounded-lg bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-blue-500/5"></div>
                            <CardContent className="relative p-8 sm:p-12">
                                {/* Decorative Elements */}
                                <div className="absolute -top-6 left-8 h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 opacity-20"></div>
                                <div className="absolute -bottom-6 right-8 h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 opacity-20"></div>

                                <div
                                    className={`prose prose-lg max-w-none text-slate-700 dark:prose-invert dark:text-slate-300 ${
                                        direction === 'rtl' ? 'prose-rtl' : ''
                                    }`}
                                    style={{
                                        lineHeight: '1.8',
                                        fontSize: '1.1rem'
                                    }}
                                >
                                    <div className="space-y-6" dangerouslySetInnerHTML={{ __html: localizedContent }} />
                                </div>

                                {/* Bottom decorative line */}
                                <div className="mt-12 flex justify-center">
                                    <div className="h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-blue-500"></div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Last Updated Notice */}
                        <div className="mt-8 text-center">
                            <p className="text-sm text-slate-500 dark:text-slate-400">
                                {currentLocale === 'ar'
                                    ? 'آخر تحديث: ' + new Date().toLocaleDateString('ar-EG')
                                    : 'Last updated: ' + new Date().toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    })
                                }
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
