import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function LanguageForm({ language }) {
    const t = useT();
    const isEdit = !!language;
    const { data, setData, post, put, processing, errors } = useForm({
        code: language?.code ?? '',
        name: language?.name ?? '',
        native_name: language?.native_name ?? '',
        direction: language?.direction ?? 'ltr',
        is_default: language?.is_default ?? false,
        is_active: language?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        isEdit ? put(route('admin.languages.update', language.id)) : post(route('admin.languages.store'));
    };

    return (
        <>
            <Head title={t('pages.languages.title')} />
            <PageHeader title={t('pages.languages.title')}
                actions={<Link href={route('admin.languages.index')} className="btn btn-light">{t('back')}</Link>}
            />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-4"><TextField label={t('fields.language_code')} value={data.code} onChange={v => setData('code', v)} error={errors.code} required /></div>
                    <div className="col-md-4"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                    <div className="col-md-4"><TextField label={t('fields.native_name')} value={data.native_name} onChange={v => setData('native_name', v)} error={errors.native_name} /></div>
                    <div className="col-md-4">
                        <SelectField label={t('fields.direction')} value={data.direction} onChange={v => setData('direction', v)}
                            options={[{value:'ltr',label:'LTR'},{value:'rtl',label:'RTL'}]} error={errors.direction} />
                    </div>
                    <div className="col-md-4"><CheckboxField label={t('fields.default')} value={data.is_default} onChange={v => setData('is_default', v)} /></div>
                    <div className="col-md-4"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.languages.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
