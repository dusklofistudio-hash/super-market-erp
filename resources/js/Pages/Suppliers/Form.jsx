import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function SupplierForm({ supplier }) {
    const t = useT();
    const isEdit = !!supplier;
    const { data, setData, post, put, processing, errors } = useForm({
        code: supplier?.code ?? '',
        name: supplier?.name ?? '',
        phone: supplier?.phone ?? '',
        email: supplier?.email ?? '',
        company: supplier?.company ?? '',
        address: supplier?.address ?? '',
        opening_balance: supplier?.opening_balance ?? 0,
        is_active: supplier?.is_active ?? true,
    });
    const submit = (e) => { e.preventDefault();
        isEdit ? put(route('admin.suppliers.update', supplier.id)) : post(route('admin.suppliers.store')); };
    return (
        <>
            <Head title={t('pages.suppliers.title')} />
            <PageHeader title={t('pages.suppliers.title')}
                actions={<Link href={route('admin.suppliers.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('fields.code')} value={data.code} onChange={v => setData('code', v)} error={errors.code} required /></div>
                    <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.company')} value={data.company} onChange={v => setData('company', v)} error={errors.company} /></div>
                    <div className="col-md-3"><TextField label={t('phone')} value={data.phone} onChange={v => setData('phone', v)} error={errors.phone} /></div>
                    <div className="col-md-3"><TextField label={t('email')} type="email" value={data.email} onChange={v => setData('email', v)} error={errors.email} /></div>
                    <div className="col-md-6"><TextField label={t('fields.opening_balance')} type="number" step="0.0001" value={data.opening_balance} onChange={v => setData('opening_balance', v)} error={errors.opening_balance} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                    <div className="col-12"><TextAreaField label={t('address')} value={data.address} onChange={v => setData('address', v)} error={errors.address} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.suppliers.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
