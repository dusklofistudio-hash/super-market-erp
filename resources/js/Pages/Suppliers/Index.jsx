import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function SuppliersIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'code', title: t('fields.code') },
        { data: 'name', title: t('name') },
        { data: 'company', title: t('fields.company') },
        { data: 'phone', title: t('phone') },
        { data: 'email', title: t('email') },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.suppliers.title')} />
            <PageHeader title={t('pages.suppliers.title')}
                actions={<Link href={route('admin.suppliers.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.suppliers.data')} columns={columns} />
        </>
    );
}
