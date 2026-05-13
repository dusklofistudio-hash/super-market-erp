import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function ExpensesIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'ref_no', title: t('fields.ref_no') },
        { data: 'date', title: t('fields.date') },
        { data: 'branch_name', title: t('fields.branch'), orderable: false },
        { data: 'category_name', title: t('fields.category'), orderable: false },
        { data: 'amount', title: t('fields.amount'), className: 'text-end' },
        { data: 'user_name', title: t('fields.created_by'), orderable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.expenses.title')} />
            <PageHeader title={t('pages.expenses.title')}
                actions={<Link href={route('admin.expenses.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.expenses.data')} columns={columns} order={[[1, 'desc']]} />
        </>
    );
}
