import React from 'react';
import { Link, router } from '@inertiajs/react';
import { confirmDelete } from '../lib/flasher';
import { useT } from '../lib/i18n';

/**
 * Helper that renders edit/delete row buttons used inside the Yajra "action"
 * column. Because Yajra serialises HTML strings for the column, we ship a
 * small helper that returns the HTML and a global delegated click handler
 * registered in window.smkBindRowActions.
 */
export function RowActions({ editHref, onDelete }) {
    const t = useT();
    return (
        <div className="btn-group btn-group-sm">
            {editHref && (
                <Link href={editHref} className="btn btn-outline-primary">
                    {t('edit')}
                </Link>
            )}
            {onDelete && (
                <button type="button" className="btn btn-outline-danger" onClick={onDelete}>
                    {t('delete')}
                </button>
            )}
        </div>
    );
}

/** Imperative confirm + delete using SweetAlert2 + Inertia. */
export function confirmAndDelete(url, onSuccess) {
    const tt = typeof window !== 'undefined' && typeof window.__smkT === 'function'
        ? window.__smkT
        : (k) => k;
    return confirmDelete({
        title: tt('confirm_delete.title'),
        text: tt('confirm_delete.text'),
        confirmText: tt('confirm_delete.confirm'),
        cancelText: tt('confirm_delete.cancel'),
    }).then((res) => {
        if (!res.isConfirmed) return;
        router.delete(url, {
            preserveScroll: true,
            onSuccess: () => onSuccess?.(),
        });
    });
}

// Delegated handler used by Yajra-rendered action buttons. We use data-attrs
// to keep the integration with raw HTML simple. The global click listener is
// registered exactly once via a window flag so calling smkBindRowActions
// repeatedly (every DataTable redraw) does not stack handlers — each call
// only swaps the `reload` callback that will be invoked on success.
if (typeof window !== 'undefined') {
    window.__smkRowActionsReload = null;
    if (!window.__smkRowActionsBound) {
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-smk-delete]');
            if (!btn) return;
            e.preventDefault();
            const url = btn.getAttribute('data-smk-delete');
            confirmAndDelete(url, window.__smkRowActionsReload || undefined);
        });
        window.__smkRowActionsBound = true;
    }
    window.smkBindRowActions = function (reload) {
        window.__smkRowActionsReload = reload || null;
    };
}
