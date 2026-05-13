import React from 'react';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function PermissionsIndex() {
    const t = useT();
    const columns = [
        { data: 'module', title: t('fields.module') },
        { data: 'slug', title: t('fields.slug') },
        { data: 'name', title: t('name') },
        { data: 'description', title: t('fields.description'), orderable: false },
    ];
    return (
        <>
            <Head title={t('pages.permissions.title')} />
            <PageHeader title={t('pages.permissions.title')} subtitle={t('pages.permissions.subtitle')} />
            <ServerDataTable url={route('admin.permissions.data')} columns={columns} order={[[0, 'asc'], [1, 'asc']]} />
        </>
    );
}
