import React from 'react';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useT } from '@/lib/i18n';

export default function DashboardIndex({ stats = {} }) {
    const t = useT();
    const cards = [
        { key: 'branches', label: t('menu.branches'), value: stats.branches ?? 0, color: 'primary' },
        { key: 'products', label: t('menu.products'), value: stats.products ?? 0, color: 'success' },
        { key: 'customers', label: t('menu.customers'), value: stats.customers ?? 0, color: 'info' },
        { key: 'suppliers', label: t('menu.suppliers'), value: stats.suppliers ?? 0, color: 'warning' },
    ];

    return (
        <>
            <Head title={t('pages.dashboard.title')} />
            <PageHeader
                title={t('pages.dashboard.welcome')}
                subtitle={t('pages.dashboard.overview')}
            />

            <div className="row g-3">
                {cards.map((c) => (
                    <div key={c.key} className="col-12 col-md-6 col-xl-3">
                        <div className="card">
                            <div className="card-body">
                                <div className="text-muted small">{c.label}</div>
                                <div className={`display-6 fw-semibold text-${c.color}`}>{c.value}</div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
