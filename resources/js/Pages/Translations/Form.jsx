import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function TranslationForm({ translation, languages = [] }) {
    const t = useT();
    const isEdit = !!translation;
    const { data, setData, post, put, processing, errors } = useForm({
        language_code: translation?.language_code ?? (languages[0]?.code ?? 'en'),
        group: translation?.group ?? 'messages',
        key: translation?.key ?? '',
        value: translation?.value ?? '',
    });
    const submit = (e) => {
        e.preventDefault();
        isEdit ? put(route('admin.translations.update', translation.id)) : post(route('admin.translations.store'));
    };
    return (
        <>
            <Head title={t('pages.translations.title')} />
            <PageHeader title={t('pages.translations.title')}
                actions={<Link href={route('admin.translations.index')} className="btn btn-light">{t('back')}</Link>}
            />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-4"><SelectField label={t('fields.language_code')} value={data.language_code} onChange={v => setData('language_code', v)} options={languages.map(l => ({ value: l.code, label: l.name }))} required /></div>
                    <div className="col-md-4"><TextField label={t('fields.group')} value={data.group} onChange={v => setData('group', v)} error={errors.group} required /></div>
                    <div className="col-md-4"><TextField label={t('fields.key')} value={data.key} onChange={v => setData('key', v)} error={errors.key} required /></div>
                    <div className="col-12"><TextAreaField label={t('fields.value')} value={data.value} onChange={v => setData('value', v)} error={errors.value} rows={4} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.translations.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
