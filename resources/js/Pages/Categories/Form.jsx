import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, SelectField, CheckboxField, FileField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function CategoryForm({ category, parents = [] }) {
    const t = useT();
    const isEdit = !!category;
    const { data, setData, post, processing, errors } = useForm({
        _method: isEdit ? 'put' : 'post',
        parent_id: category?.parent_id ?? '',
        name_en: category?.name_en ?? '',
        name_kh: category?.name_kh ?? '',
        slug: category?.slug ?? '',
        image: null,
        is_active: category?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        post(isEdit ? route('admin.categories.update', category.id) : route('admin.categories.store'), { forceFormData: true });
    };
    return (
        <>
            <Head title={t('pages.categories.title')} />
            <PageHeader title={t('pages.categories.title')}
                actions={<Link href={route('admin.categories.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('fields.name_en')} value={data.name_en} onChange={v => setData('name_en', v)} error={errors.name_en} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.name_kh')} value={data.name_kh} onChange={v => setData('name_kh', v)} error={errors.name_kh} /></div>
                    <div className="col-md-6"><TextField label={t('fields.slug')} value={data.slug} onChange={v => setData('slug', v)} error={errors.slug} placeholder="auto" /></div>
                    <div className="col-md-6"><SelectField label={t('fields.parent_category')} value={data.parent_id} onChange={v => setData('parent_id', v)} placeholder="—" options={parents.map(p => ({ value: p.id, label: p.name_en }))} /></div>
                    <div className="col-md-6"><FileField label={t('fields.image')} onChange={f => setData('image', f)} error={errors.image} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.categories.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
