import React, { useRef, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ServerDataTable from '@/Components/ServerDataTable';
import { SelectField, TextField, TextAreaField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function PosSessionsIndex({ registers = [] }) {
    const t = useT();
    const tableRef = useRef(null);
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        register_id: registers[0]?.id ?? '',
        opening_cash: '0',
        note: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.pos-sessions.open'), { onSuccess: () => { reset(); setOpen(false); } });
    };

    const columns = [
        { data: 'register_name', title: t('fields.register'), orderable: false },
        { data: 'branch_name', title: t('fields.branch'), orderable: false },
        { data: 'user_name', title: t('fields.cashier'), orderable: false },
        { data: 'opened_at', title: t('fields.opened_at') },
        { data: 'closed_at', title: t('fields.closed_at'), orderable: false },
        { data: 'opening_cash', title: t('fields.opening_cash'), className: 'text-end' },
        { data: 'expected_cash', title: t('fields.expected_cash'), className: 'text-end' },
        { data: 'closing_cash', title: t('fields.closing_cash'), className: 'text-end' },
        { data: 'state', title: t('status'), orderable: false, searchable: false },
    ];

    return (
        <>
            <Head title={t('pages.pos_sessions.title')} />
            <PageHeader title={t('pages.pos_sessions.title')}
                actions={
                    <button className="btn btn-primary" onClick={() => setOpen(true)} disabled={registers.length === 0}>
                        + {t('pos.open_session')}
                    </button>
                }
            />

            {open && (
                <div className="card mb-3">
                    <form onSubmit={submit} className="card-body row">
                        <div className="col-md-4">
                            <SelectField label={t('fields.register')} value={data.register_id}
                                onChange={v => setData('register_id', v)}
                                options={registers.map(r => ({ value: r.id, label: r.name }))}
                                error={errors.register_id} required />
                        </div>
                        <div className="col-md-4">
                            <TextField label={t('fields.opening_cash')} type="number" step="0.01"
                                value={data.opening_cash} onChange={v => setData('opening_cash', v)}
                                error={errors.opening_cash} required />
                        </div>
                        <div className="col-12">
                            <TextAreaField label={t('fields.note')} value={data.note} onChange={v => setData('note', v)} error={errors.note} />
                        </div>
                        <div className="col-12 text-end">
                            <button type="button" className="btn btn-light me-2" onClick={() => setOpen(false)}>{t('cancel')}</button>
                            <button className="btn btn-primary" disabled={processing}>{t('pos.open_session')}</button>
                        </div>
                    </form>
                </div>
            )}

            <ServerDataTable ref={tableRef} url={route('admin.pos-sessions.data')} columns={columns} order={[[3, 'desc']]} />
        </>
    );
}
