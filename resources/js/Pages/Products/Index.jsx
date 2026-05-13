import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function ProductsIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'barcode', title: t('fields.barcode') },
        { data: 'sku', title: t('fields.sku') },
        { data: 'name_en', title: t('fields.name_en') },
        { data: 'category_name', title: t('fields.category'), orderable: false },
        { data: 'brand_name', title: t('fields.brand'), orderable: false },
        { data: 'unit_name', title: t('fields.unit'), orderable: false },
        { data: 'cost_price', title: t('fields.cost_price') },
        { data: 'sale_price', title: t('fields.sale_price') },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.products.title')} />
            <PageHeader title={t('pages.products.title')} subtitle={t('pages.products.subtitle')}
                actions={<Link href={route('admin.products.create')} className="btn btn-primary">+ {t('create')}</Link>} />
            <ServerDataTable ref={tableRef} url={route('admin.products.data')} columns={columns} />
        </>
    );
}
