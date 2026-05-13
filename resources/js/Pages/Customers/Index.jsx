import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function CustomersIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'code', title: t('fields.code') },
        { data: 'name', title: t('name') },
        { data: 'phone', title: t('phone') },
        { data: 'email', title: t('email') },
        { data: 'group_name', title: t('fields.customer_group'), orderable: false },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.customers.title')} />
            <PageHeader title={t('pages.customers.title')}
                actions={<Link href={route('admin.customers.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.customers.data')} columns={columns} />
        </>
    );
}
