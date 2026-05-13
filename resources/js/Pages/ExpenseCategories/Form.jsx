import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ExpenseCategoryForm({ category }) {
    const t = useT();
    const isEdit = !!category;
    const { data, setData, post, put, processing, errors } = useForm({
        name: category?.name ?? '',
        description: category?.description ?? '',
        is_active: category?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        if (isEdit) put(route('admin.expense-categories.update', category.id));
        else post(route('admin.expense-categories.store'));
    };
    return (
        <>
            <Head title={t('pages.expense_categories.title')} />
            <PageHeader title={t('pages.expense_categories.title')}
                actions={<Link href={route('admin.expense-categories.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('fields.name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-12"><TextAreaField label={t('fields.description')} value={data.description} onChange={v => setData('description', v)} error={errors.description} /></div>
                    <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.expense-categories.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
