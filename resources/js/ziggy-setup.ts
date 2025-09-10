import { Ziggy } from './ziggy.js';

// Simple route helper that uses the Ziggy config
function createRouteFunction(config: any) {
    return function route(name: string, params?: any): string {
        const routeConfig = config.routes[name];
        if (!routeConfig) {
            console.warn(`Route "${name}" not found`);
            return '/';
        }

        let uri = routeConfig.uri;

        // Replace route parameters
        if (params && routeConfig.parameters) {
            routeConfig.parameters.forEach((param: string) => {
                const value = params[param];
                if (value !== undefined) {
                    uri = uri.replace(`{${param}}`, value);
                }
            });
        }

        // Handle optional parameters (remove if not provided)
        uri = uri.replace(/\/\{[^}]*\?\}/g, '');

        return `${config.url}${uri === '/' ? '' : '/'}${uri}`;
    };
}

// Make route function available globally for both client and SSR
const routeFn = createRouteFunction(Ziggy);

if (typeof globalThis !== 'undefined') {
    // @ts-ignore
    globalThis.route = routeFn;
}

// Also make it available on window if in browser
if (typeof window !== 'undefined') {
    // @ts-ignore
    window.route = routeFn;
}

export { Ziggy };
