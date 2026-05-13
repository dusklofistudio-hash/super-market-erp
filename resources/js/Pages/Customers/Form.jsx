import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function CustomerForm({ customer, groups = [] }) {
    const t = useT();
    const isEdit = !!customer;
    const { data, setData, post, put, processing, errors } = useForm({
        code: customer?.code ?? '',
        name: customer?.name ?? '',
        phone: customer?.phone ?? '',
        email: customer?.email ?? '',
        address: customer?.address ?? '',
        customer_group_id: customer?.customer_group_id ?? '',
        opening_balance: customer?.opening_balance ?? 0,
        is_active: customer?.is_active ?? true,
    });
    const submit = (e) => { e.preventDefault();
        isEdit ? put(route('admin.customers.update', customer.id)) : post(route('admin.customers.store')); };
    return (
        <>
            <Head title={t('pages.customers.title')} />
            <PageHeader title={t('pages.customers.title')}
                actions={<Link href={route('admin.customers.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('fields.code')} value={data.code} onChange={v => setData('code', v)} error={errors.code} required /></div>
                    <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-4"><TextField label={t('phone')} value={data.phone} onChange={v => setData('phone', v)} error={errors.phone} /></div>
                    <div className="col-md-4"><TextField label={t('email')} type="email" value={data.email} onChange={v => setData('email', v)} error={errors.email} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.customer_group')} value={data.customer_group_id} onChange={v => setData('customer_group_id', v)} placeholder="—" options={groups.map(g => ({ value: g.id, label: g.name }))} /></div>
                    <div className="col-md-6"><TextField label={t('fields.opening_balance')} type="number" step="0.0001" value={data.opening_balance} onChange={v => setData('opening_balance', v)} error={errors.opening_balance} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                    <div className="col-12"><TextAreaField label={t('address')} value={data.address} onChange={v => setData('address', v)} error={errors.address} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.customers.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
