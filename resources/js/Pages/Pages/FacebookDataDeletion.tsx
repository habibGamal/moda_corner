import React from "react";
import { Head } from "@inertiajs/react";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
// import { useI18n } from '@/hooks/use-i18n';

import { Shield, Trash2, ExternalLink, Mail, Facebook } from "lucide-react";
import { useSettings } from "@/hooks/useSettings";

export default function FacebookDataDeletion() {
    const settings = useSettings();

    return (
        <div className="container mt-4">
            <div dir="ltr">
                <Head title="Facebook Deletion" />

                {/* Hero Section */}
                <div className="relative bg-gradient-to-br from-blue-50 via-white to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                    <div className="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]" />
                    <div className="relative">
                        <div className="container mx-auto px-4 py-16 sm:py-24">
                            <div className="text-center">
                                <div className="mx-auto mb-6 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 p-0.5">
                                    <div className="flex h-full w-full items-center justify-center rounded-full bg-white dark:bg-slate-900">
                                        <Facebook className="h-8 w-8 text-blue-500" />
                                    </div>
                                </div>
                                <h1 className="text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                                    <span className="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-600 bg-clip-text text-transparent">
                                        Facebook Deletion
                                    </span>
                                </h1>
                                <p className="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                                    Protect your privacy and control your data.
                                    Follow these steps to delete your data from
                                    Facebook.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="bg-white dark:bg-slate-900">
                    <div className="container mx-auto px-4 py-16">
                        <div className="mx-auto max-w-4xl space-y-8">
                            {/* Steps Card */}
                            <Card className="border-0 bg-white/70 shadow-2xl backdrop-blur-sm dark:bg-slate-800/70 dark:shadow-slate-900/50">
                                <CardContent className="relative p-8 sm:p-12">
                                    {/* Alternative Method */}
                                    <div className="mt-12 pt-8">
                                        <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                            <Mail className="h-5 w-5 text-blue-500" />
                                            By Email
                                        </h3>
                                        <div className="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-6">
                                            <p className="text-slate-700 dark:text-slate-300 mb-4 leading-relaxed">
                                                Users have the right to request
                                                the deletion of their personal
                                                data collected by our platform.
                                                If you would like to request
                                                deletion of your data, you can
                                                do so by contacting us through
                                                one of the following methods:
                                            </p>
                                            <div className="space-y-3">
                                                <div>
                                                    <strong className="text-slate-900 dark:text-white">
                                                        Email:{" "}
                                                        {settings.contact_email ||
                                                            ""}
                                                    </strong>
                                                    <p className="text-slate-600 dark:text-slate-400 mt-1">
                                                        Send a request to our
                                                        email with the subject
                                                        line: "Data Deletion
                                                        Request". Please include
                                                        your full name,
                                                        registered email
                                                        address, and a brief
                                                        description of your
                                                        request.
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                                <p className="text-amber-800 dark:text-amber-200 text-sm leading-relaxed">
                                                    We will respond to all
                                                    verified requests within 30
                                                    days in accordance with
                                                    applicable data protection
                                                    regulations. Some data may
                                                    be retained if required by
                                                    law or for legitimate
                                                    business purposes, such as
                                                    fraud prevention or tax
                                                    compliance.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Contact Button */}
                                    <div className="mt-8 text-center">
                                        <Button
                                            className="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105"
                                            asChild
                                        >
                                            <a
                                                href={`mailto:${
                                                    settings.contact_email ||
                                                    "info@vilain.com"
                                                }?subject=Data Deletion Request`}
                                            >
                                                <Mail className="h-4 w-4 mr-2" />
                                                Contact Us
                                                <ExternalLink className="h-4 w-4 ml-2" />
                                            </a>
                                        </Button>
                                    </div>

                                    {/* Bottom decorative line */}
                                    <div className="mt-12 flex justify-center">
                                        <div className="h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-500"></div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Last Updated Notice */}
                            <div className="text-center">
                                <p className="text-sm text-slate-500 dark:text-slate-400">
                                    {"Last updated: " +
                                        new Date().toLocaleDateString("en-US", {
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                        })}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
