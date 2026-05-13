import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function UnitForm({ unit, base_units = [] }) {
    const t = useT();
    const isEdit = !!unit;
    const { data, setData, post, put, processing, errors } = useForm({
        name_en: unit?.name_en ?? '',
        name_kh: unit?.name_kh ?? '',
        short_name: unit?.short_name ?? '',
        base_unit_id: unit?.base_unit_id ?? '',
        conversion_factor: unit?.conversion_factor ?? 1,
        is_active: unit?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        isEdit ? put(route('admin.units.update', unit.id)) : post(route('admin.units.store'));
    };
    return (
        <>
            <Head title={t('pages.units.title')} />
            <PageHeader title={t('pages.units.title')}
                actions={<Link href={route('admin.units.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('fields.name_en')} value={data.name_en} onChange={v => setData('name_en', v)} error={errors.name_en} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.name_kh')} value={data.name_kh} onChange={v => setData('name_kh', v)} error={errors.name_kh} /></div>
                    <div className="col-md-4"><TextField label={t('fields.short_name')} value={data.short_name} onChange={v => setData('short_name', v)} error={errors.short_name} required /></div>
                    <div className="col-md-4"><SelectField label={t('fields.base_unit')} value={data.base_unit_id} onChange={v => setData('base_unit_id', v)} placeholder="—" options={base_units.map(u => ({ value: u.id, label: u.name_en }))} /></div>
                    <div className="col-md-4"><TextField label={t('fields.conversion_factor')} type="number" step="0.0001" value={data.conversion_factor} onChange={v => setData('conversion_factor', v)} error={errors.conversion_factor} required /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.units.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
