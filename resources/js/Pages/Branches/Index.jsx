import React, { useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function BranchesIndex() {
    const t = useT();
    const tableRef = React.useRef(null);

    useEffect(() => {
        window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload());
    }, []);

    const columns = [
        { data: 'code', title: t('fields.code') },
        { data: 'name_en', title: t('fields.name_en') },
        { data: 'name_kh', title: t('fields.name_kh') },
        { data: 'phone', title: t('phone') },
        { data: 'manager_name', title: t('fields.manager'), orderable: false },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];

    return (
        <>
            <Head title={t('pages.branches.title')} />
            <PageHeader
                title={t('pages.branches.title')}
                subtitle={t('pages.branches.subtitle')}
                actions={
                    <Link href={route('admin.branches.create')} className="btn btn-primary">
                        + {t('create')}
                    </Link>
                }
            />
            <ServerDataTable ref={tableRef} url={route('admin.branches.data')} columns={columns} order={[[0, 'asc']]} />
        </>
    );
}
