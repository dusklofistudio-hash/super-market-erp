import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function TaxRateForm({ tax_rate }) {
    const t = useT();
    const isEdit = !!tax_rate;
    const { data, setData, post, put, processing, errors } = useForm({
        name: tax_rate?.name ?? '',
        rate: tax_rate?.rate ?? 0,
        is_inclusive: tax_rate?.is_inclusive ?? false,
        is_active: tax_rate?.is_active ?? true,
    });
    const submit = (e) => { e.preventDefault();
        isEdit ? put(route('admin.tax-rates.update', tax_rate.id)) : post(route('admin.tax-rates.store')); };
    return (
        <>
            <Head title={t('pages.tax_rates.title')} />
            <PageHeader title={t('pages.tax_rates.title')}
                actions={<Link href={route('admin.tax-rates.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-3"><TextField label={t('fields.rate')} type="number" step="0.0001" value={data.rate} onChange={v => setData('rate', v)} error={errors.rate} required /></div>
                    <div className="col-md-3"><CheckboxField label={t('fields.is_inclusive')} value={data.is_inclusive} onChange={v => setData('is_inclusive', v)} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.tax-rates.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
