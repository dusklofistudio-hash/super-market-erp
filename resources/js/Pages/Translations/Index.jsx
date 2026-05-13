import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function TranslationsIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);
    const columns = [
        { data: 'language_code', title: t('fields.language_code') },
        { data: 'group', title: t('fields.group') },
        { data: 'key', title: t('fields.key') },
        { data: 'value', title: t('fields.value') },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];
    return (
        <>
            <Head title={t('pages.translations.title')} />
            <PageHeader title={t('pages.translations.title')} subtitle={t('pages.translations.subtitle')}
                actions={<Link href={route('admin.translations.create')} className="btn btn-primary">+ {t('create')}</Link>}
            />
            <ServerDataTable ref={tableRef} url={route('admin.translations.data')} columns={columns} />
        </>
    );
}
