import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, SelectField, CheckboxField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function UserForm({ user, roles = [], branches = [] }) {
    const t = useT();
    const isEdit = !!user;
    const { data, setData, post, processing, errors, transform } = useForm({
        name: user?.name ?? '',
        username: user?.username ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        password: '',
        default_branch_id: user?.default_branch_id ?? '',
        is_active: user?.is_active ?? true,
        is_super_admin: user?.is_super_admin ?? false,
        locale: user?.locale ?? 'en',
        avatar: null,
        roles: user?.roles ?? [],
        branches: user?.branches ?? [],
        _method: isEdit ? 'put' : 'post',
    });

    // Inertia + multipart: always POST with `_method` override on edits so we
    // can stream the avatar file via FormData. forceFormData is required for
    // empty file fields.
    const submit = (e) => {
        e.preventDefault();
        const action = isEdit ? route('admin.users.update', user.id) : route('admin.users.store');
        post(action, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const localeOptions = [
        { value: 'en', label: 'English' },
        { value: 'kh', label: 'ខ្មែរ' },
    ];

    return (
        <>
            <Head title={t('pages.users.title')} />
            <PageHeader title={t('pages.users.title')}
                actions={<Link href={route('admin.users.index')} className="btn btn-light">{t('back')}</Link>}
            />
            <form onSubmit={submit} className="card" encType="multipart/form-data">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                        <div className="col-md-6"><TextField label={t('username')} value={data.username} onChange={v => setData('username', v)} error={errors.username} required /></div>
                        <div className="col-md-6"><TextField label={t('email')} type="email" value={data.email} onChange={v => setData('email', v)} error={errors.email} required /></div>
                        <div className="col-md-6"><TextField label={t('phone')} value={data.phone} onChange={v => setData('phone', v)} error={errors.phone} /></div>
                        <div className="col-md-6"><TextField label={t('password')} type="password" value={data.password} onChange={v => setData('password', v)} error={errors.password} placeholder={isEdit ? '••••••••' : ''} /></div>
                        <div className="col-md-6"><SelectField label={t('menu.branches')} value={data.default_branch_id} onChange={v => setData('default_branch_id', v)} placeholder="—" options={branches.map(b => ({ value: b.id, label: b.name_en }))} error={errors.default_branch_id} /></div>
                        <div className="col-md-6"><SelectField label={t('fields.locale')} value={data.locale} onChange={v => setData('locale', v)} options={localeOptions} error={errors.locale} /></div>
                        <div className="col-md-6">
                            <label className="form-label">{t('fields.avatar')}</label>
                            {user?.avatar && (
                                <div className="mb-2">
                                    <img src={`/storage/${user.avatar}`} alt="avatar" style={{ height: 48, width: 48, borderRadius: 24, objectFit: 'cover' }} />
                                </div>
                            )}
                            <input
                                type="file"
                                accept="image/*"
                                className={`form-control ${errors.avatar ? 'is-invalid' : ''}`}
                                onChange={e => setData('avatar', e.target.files?.[0] ?? null)}
                            />
                            {errors.avatar && <div className="invalid-feedback d-block">{errors.avatar}</div>}
                        </div>
                        <div className="col-md-6"><SelectField label={t('fields.roles')} value={data.roles} onChange={v => setData('roles', v)} multiple options={roles.map(r => ({ value: r.id, label: r.name }))} error={errors.roles} /></div>
                        <div className="col-md-6"><SelectField label={t('fields.branches')} value={data.branches} onChange={v => setData('branches', v)} multiple options={branches.map(b => ({ value: b.id, label: b.name_en }))} error={errors.branches} /></div>
                        <div className="col-md-6"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                        <div className="col-md-6"><CheckboxField label={t('fields.super_admin')} value={data.is_super_admin} onChange={v => setData('is_super_admin', v)} /></div>
                    </div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.users.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button type="submit" className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
