import React, { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { flushInertiaFlash } from '../lib/flasher';

export default function AdminLayout({ children }) {
    const page = usePage();

    useEffect(() => {
        flushInertiaFlash(page.props);
    }, [page.props.flash?.success, page.props.flash?.error]);

    // The actual chrome (sidebar/header) is rendered by the Blade layout shell.
    // This wrapper just provides the React "content" island.
    return (
        <div className="smk-page">
            {children}
        </div>
    );
}
