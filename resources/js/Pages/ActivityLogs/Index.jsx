import React, { useRef } from 'react';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function ActivityLogsIndex() {
    const t = useT();
    const tableRef = useRef(null);
    const columns = [
        { data: 'created_at', title: t('fields.date') },
        { data: 'user_name', title: t('fields.user'), orderable: false },
        { data: 'action', title: t('fields.action') },
        { data: 'subject', title: t('fields.subject'), orderable: false, searchable: false },
        { data: 'ip_address', title: t('fields.ip') },
        { data: 'payload', title: t('fields.payload'), orderable: false, searchable: false },
    ];
    return (
        <>
            <Head title={t('pages.activity_logs.title')} />
            <PageHeader title={t('pages.activity_logs.title')} subtitle={t('pages.activity_logs.subtitle')} />
            <ServerDataTable ref={tableRef} url={route('admin.activity-logs.data')} columns={columns} order={[[0, 'desc']]} />
        </>
    );
}
