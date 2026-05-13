import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function BranchForm({ branch, managers = [] }) {
    const t = useT();
    const isEdit = !!branch;
    const { data, setData, post, put, processing, errors } = useForm({
        code: branch?.code ?? '',
        name_en: branch?.name_en ?? '',
        name_kh: branch?.name_kh ?? '',
        phone: branch?.phone ?? '',
        email: branch?.email ?? '',
        address: branch?.address ?? '',
        manager_id: branch?.manager_id ?? '',
        is_active: branch?.is_active ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.branches.update', branch.id));
        } else {
            post(route('admin.branches.store'));
        }
    };

    return (
        <>
            <Head title={t('pages.branches.title')} />
            <PageHeader
                title={t('pages.branches.title')}
                actions={<Link href={route('admin.branches.index')} className="btn btn-light">{t('back')}</Link>}
            />

            <form onSubmit={submit} className="card">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-6">
                            <TextField label={t('fields.code')} name="code" value={data.code} onChange={(v) => setData('code', v)} error={errors.code} required />
                        </div>
                        <div className="col-md-6">
                            <SelectField label={t('fields.manager')} name="manager_id" value={data.manager_id} onChange={(v) => setData('manager_id', v)} placeholder="—" options={managers.map((m) => ({ value: m.id, label: m.name }))} error={errors.manager_id} />
                        </div>
                        <div className="col-md-6">
                            <TextField label={t('fields.name_en')} name="name_en" value={data.name_en} onChange={(v) => setData('name_en', v)} error={errors.name_en} required />
                        </div>
                        <div className="col-md-6">
                            <TextField label={t('fields.name_kh')} name="name_kh" value={data.name_kh} onChange={(v) => setData('name_kh', v)} error={errors.name_kh} />
                        </div>
                        <div className="col-md-6">
                            <TextField label={t('phone')} name="phone" value={data.phone} onChange={(v) => setData('phone', v)} error={errors.phone} />
                        </div>
                        <div className="col-md-6">
                            <TextField label={t('email')} type="email" name="email" value={data.email} onChange={(v) => setData('email', v)} error={errors.email} />
                        </div>
                        <div className="col-12">
                            <TextAreaField label={t('address')} name="address" value={data.address} onChange={(v) => setData('address', v)} error={errors.address} />
                        </div>
                        <div className="col-12">
                            <CheckboxField label={t('active')} name="is_active" value={data.is_active} onChange={(v) => setData('is_active', v)} />
                        </div>
                    </div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.branches.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button type="submit" className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
