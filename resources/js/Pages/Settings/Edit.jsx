import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function SettingsEdit({ settings = {} }) {
    const t = useT();
    const { data, setData, put, processing, errors } = useForm({
        company_name: settings.company_name ?? '',
        company_email: settings.company_email ?? '',
        company_phone: settings.company_phone ?? '',
        company_address: settings.company_address ?? '',
        default_currency: settings.default_currency ?? '',
        default_currency_symbol: settings.default_currency_symbol ?? '',
        date_format: settings.date_format ?? '',
        time_format: settings.time_format ?? '',
    });
    const submit = (e) => { e.preventDefault(); put(route('admin.settings.update')); };

    return (
        <>
            <Head title={t('pages.settings.title')} />
            <PageHeader title={t('pages.settings.title')} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label="Company name" value={data.company_name} onChange={v => setData('company_name', v)} error={errors.company_name} /></div>
                    <div className="col-md-6"><TextField label="Company email" type="email" value={data.company_email} onChange={v => setData('company_email', v)} error={errors.company_email} /></div>
                    <div className="col-md-6"><TextField label="Company phone" value={data.company_phone} onChange={v => setData('company_phone', v)} error={errors.company_phone} /></div>
                    <div className="col-md-6"><TextAreaField label="Company address" value={data.company_address} onChange={v => setData('company_address', v)} error={errors.company_address} /></div>
                    <div className="col-md-3"><TextField label="Currency code" value={data.default_currency} onChange={v => setData('default_currency', v)} error={errors.default_currency} /></div>
                    <div className="col-md-3"><TextField label="Currency symbol" value={data.default_currency_symbol} onChange={v => setData('default_currency_symbol', v)} error={errors.default_currency_symbol} /></div>
                    <div className="col-md-3"><TextField label="Date format" value={data.date_format} onChange={v => setData('date_format', v)} error={errors.date_format} /></div>
                    <div className="col-md-3"><TextField label="Time format" value={data.time_format} onChange={v => setData('time_format', v)} error={errors.time_format} /></div>
                </div>
                <div className="card-footer text-end">
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
