import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function UnitsIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'name_en', title: t('fields.name_en') },
        { data: 'name_kh', title: t('fields.name_kh') },
        { data: 'short_name', title: t('fields.short_name') },
        { data: 'base_unit_name', title: t('fields.base_unit'), orderable: false },
        { data: 'conversion_factor', title: t('fields.conversion_factor') },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.units.title')} />
            <PageHeader title={t('pages.units.title')}
                actions={<Link href={route('admin.units.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.units.data')} columns={columns} />
        </>
    );
}
