import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function StockTransfersIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'ref_no', title: t('fields.ref_no') },
        { data: 'date', title: t('fields.date') },
        { data: 'from_name', title: t('fields.from_branch'), orderable: false },
        { data: 'to_name', title: t('fields.to_branch'), orderable: false },
        { data: 'status_badge', title: t('status'), orderable: false, searchable: false },
        { data: 'user_name', title: t('fields.created_by'), orderable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.stock_transfers.title')} />
            <PageHeader title={t('pages.stock_transfers.title')}
                actions={<Link href={route('admin.stock-transfers.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.stock-transfers.data')} columns={columns} order={[[1, 'desc']]} />
        </>
    );
}
