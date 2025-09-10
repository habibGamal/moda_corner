import '../css/app.css';
import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { hydrateRoot } from 'react-dom/client';
import { Toaster } from './Components/ui/toaster';
import MainLayout from './Layouts/MainLayout';
import './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        // Use dynamic import for code splitting (non-eager loading)
        const page = await resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx'));

        // Apply MainLayout as default if the page doesn't specify a layout
        // @ts-ignore
        if (page && !page.default.layout) {
            // @ts-ignore
            page.default.layout = (page) => <MainLayout>{page}</MainLayout>;
        }

        return page;
    },
    setup({ el, App, props }) {
        hydrateRoot(el, (
            <>
                <App {...props} />
                <Toaster />
            </>
        ));
    },
    progress: {
        color: '#4B5563',
    },
});
