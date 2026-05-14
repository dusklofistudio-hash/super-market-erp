import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function PosRegisterForm({ register, branches = [] }) {
    const t = useT();
    const isEdit = !!register;
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: register?.branch_id ?? (branches[0]?.id ?? ''),
        name: register?.name ?? '',
        is_active: register?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        if (isEdit) put(route('admin.pos-registers.update', register.id));
        else post(route('admin.pos-registers.store'));
    };
    return (
        <>
            <Head title={t('pages.pos_registers.title')} />
            <PageHeader title={t('pages.pos_registers.title')}
                actions={<Link href={route('admin.pos-registers.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)} options={branches.map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} error={errors.branch_id} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.pos-registers.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
