import '../css/app.css';
import './bootstrap';
import './ziggy-setup';
import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import { Toaster } from './Components/ui/toaster';
import MainLayout from './Layouts/MainLayout';
import './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(page =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => {
            // Use eager loading for SSR to avoid dynamic imports
            const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
            const pageComponent = pages[`./Pages/${name}.tsx`];

            // Apply MainLayout as default if the page doesn't specify a layout
            // @ts-ignore
            if (pageComponent && !pageComponent.default.layout) {
                // @ts-ignore
                pageComponent.default.layout = (page) => <MainLayout>{page}</MainLayout>;
            }

            return pageComponent;
        },
        setup: ({ App, props }) => (
            <>
                <App {...props} />
                <Toaster />
            </>
        ),
    }),
);
