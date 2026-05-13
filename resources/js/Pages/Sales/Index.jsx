import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function SalesIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'ref_no', title: t('fields.ref_no') },
        { data: 'date', title: t('fields.date') },
        { data: 'branch_name', title: t('fields.branch'), orderable: false },
        { data: 'customer_name', title: t('fields.customer'), orderable: false },
        { data: 'cashier_name', title: t('fields.cashier'), orderable: false },
        { data: 'total', title: t('fields.total'), className: 'text-end' },
        { data: 'paid', title: t('fields.paid'), className: 'text-end' },
        { data: 'balance', title: t('fields.balance'), className: 'text-end', orderable: false, searchable: false },
        { data: 'status_badge', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.sales.title')} />
            <PageHeader title={t('pages.sales.title')}
                actions={<Link href={route('admin.pos.register')} className="btn btn-primary">{t('pos.open_register')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.sales.data')} columns={columns} order={[[1, 'desc']]} />
        </>
    );
}
