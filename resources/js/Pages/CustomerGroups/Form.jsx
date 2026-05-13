import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function CustomerGroupForm({ customer_group }) {
    const t = useT();
    const isEdit = !!customer_group;
    const { data, setData, post, put, processing, errors } = useForm({
        name: customer_group?.name ?? '',
        discount_percent: customer_group?.discount_percent ?? 0,
        is_active: customer_group?.is_active ?? true,
    });
    const submit = (e) => { e.preventDefault();
        isEdit ? put(route('admin.customer-groups.update', customer_group.id)) : post(route('admin.customer-groups.store')); };
    return (
        <>
            <Head title={t('pages.customer_groups.title')} />
            <PageHeader title={t('pages.customer_groups.title')}
                actions={<Link href={route('admin.customer-groups.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-3"><TextField label={t('fields.discount_percent')} type="number" step="0.01" value={data.discount_percent} onChange={v => setData('discount_percent', v)} error={errors.discount_percent} /></div>
                    <div className="col-md-3"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.customer-groups.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
