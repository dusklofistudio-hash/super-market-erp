import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function StockAdjustmentsIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'ref_no', title: t('fields.ref_no') },
        { data: 'date', title: t('fields.date') },
        { data: 'branch_name', title: t('fields.branch'), orderable: false },
        { data: 'type_badge', title: t('fields.type'), orderable: false, searchable: false },
        { data: 'items_count', title: t('fields.lines'), orderable: false },
        { data: 'reason', title: t('fields.reason'), orderable: false },
        { data: 'user_name', title: t('fields.created_by'), orderable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.stock_adjustments.title')} />
            <PageHeader title={t('pages.stock_adjustments.title')}
                actions={<Link href={route('admin.stock-adjustments.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.stock-adjustments.data')} columns={columns} order={[[1, 'desc']]} />
        </>
    );
}
