import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function TaxRatesIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'name', title: t('name') },
        { data: 'rate', title: t('fields.rate') },
        { data: 'inclusive', title: t('fields.is_inclusive'), orderable: false },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.tax_rates.title')} />
            <PageHeader title={t('pages.tax_rates.title')}
                actions={<Link href={route('admin.tax-rates.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.tax-rates.data')} columns={columns} />
        </>
    );
}
