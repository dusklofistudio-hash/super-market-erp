import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, CheckboxField, FileField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function BrandForm({ brand }) {
    const t = useT();
    const isEdit = !!brand;
    const { data, setData, post, processing, errors } = useForm({
        _method: isEdit ? 'put' : 'post',
        name: brand?.name ?? '',
        slug: brand?.slug ?? '',
        description: brand?.description ?? '',
        logo: null,
        is_active: brand?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        post(isEdit ? route('admin.brands.update', brand.id) : route('admin.brands.store'), { forceFormData: true });
    };
    return (
        <>
            <Head title={t('pages.brands.title')} />
            <PageHeader title={t('pages.brands.title')}
                actions={<Link href={route('admin.brands.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.slug')} value={data.slug} onChange={v => setData('slug', v)} error={errors.slug} placeholder="auto" /></div>
                    <div className="col-md-6"><FileField label={t('fields.logo')} onChange={f => setData('logo', f)} error={errors.logo} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                    <div className="col-12"><TextAreaField label={t('fields.description')} value={data.description} onChange={v => setData('description', v)} error={errors.description} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.brands.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
