import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function CategoriesIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'name_en', title: t('fields.name_en') },
        { data: 'name_kh', title: t('fields.name_kh') },
        { data: 'parent_name', title: t('fields.parent_category'), orderable: false },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.categories.title')} />
            <PageHeader title={t('pages.categories.title')}
                actions={<Link href={route('admin.categories.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.categories.data')} columns={columns} />
        </>
    );
}
