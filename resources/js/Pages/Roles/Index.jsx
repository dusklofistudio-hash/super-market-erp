import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function RolesIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'name', title: t('name') },
        { data: 'slug', title: t('fields.slug') },
        { data: 'permissions_count', title: t('fields.permissions'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.roles.title')} />
            <PageHeader title={t('pages.roles.title')} subtitle={t('pages.roles.subtitle')}
                actions={<Link href={route('admin.roles.create')} className="btn btn-primary">+ {t('create')}</Link>}
            />
            <ServerDataTable ref={tableRef} url={route('admin.roles.data')} columns={columns} />
        </>
    );
}
