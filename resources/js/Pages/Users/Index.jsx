import React, { useEffect, useRef } from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { useT } from '@/lib/i18n';

export default function UsersIndex() {
    const t = useT();
    const tableRef = useRef(null);
    useEffect(() => { window.smkBindRowActions && window.smkBindRowActions(() => tableRef.current?.reload()); }, []);

    const columns = [
        { data: 'name', title: t('name') },
        { data: 'username', title: t('username') },
        { data: 'email', title: t('email') },
        { data: 'roles_list', title: t('fields.roles'), orderable: false, searchable: false },
        { data: 'default_branch', title: t('menu.branches'), orderable: false, searchable: false },
        { data: 'status', title: t('status'), orderable: false, searchable: false },
        { data: 'action', title: t('actions'), orderable: false, searchable: false, className: 'text-end' },
    ];

    return (
        <>
            <Head title={t('pages.users.title')} />
            <PageHeader
                title={t('pages.users.title')}
                subtitle={t('pages.users.subtitle')}
                actions={<Link href={route('admin.users.create')} className="btn btn-primary">+ {t('create')}</Link>}
            />
            <ServerDataTable ref={tableRef} url={route('admin.users.data')} columns={columns} />
        </>
    );
}
