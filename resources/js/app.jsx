import './bootstrap';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import AdminLayout from './Layouts/AdminLayout';
import { I18nProvider } from './lib/i18n';
import { PermissionProvider } from './lib/permissions';
// Side-effect import so window.smkBindRowActions is registered on boot.
// Yajra-rendered tables call it from each page's useEffect.
import './Components/RowActions';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: false });

createInertiaApp({
    title: (title) => (title ? `${title} | Super Market ERP` : 'Super Market ERP'),
    resolve: async (name) => {
        const importer = pages[`./Pages/${name}.jsx`];
        if (!importer) {
            throw new Error(`Inertia page not found: ./Pages/${name}.jsx`);
        }
        const module = await importer();
        const page = module.default;
        // Wrap admin pages in AdminLayout unless the page already opts out
        if (!name.startsWith('Auth/') && !name.startsWith('Errors/') && !page.layout) {
            page.layout = (children) => <AdminLayout>{children}</AdminLayout>;
        }
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <I18nProvider initial={props.initialPage.props.translations}
                          initialLocale={props.initialPage.props.locale}
                          availableLocales={props.initialPage.props.available_locales}>
                <PermissionProvider initial={props.initialPage.props.auth?.permissions ?? []}>
                    <App {...props} />
                </PermissionProvider>
            </I18nProvider>
        );
    },
    progress: { color: '#2563eb' },
});
