import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ProfileEdit({ user }) {
    const t = useT();
    const profile = useForm({ name: user.name, email: user.email, phone: user.phone ?? '', locale: user.locale ?? 'en' });
    const pwd = useForm({ current_password: '', password: '', password_confirmation: '' });

    return (
        <>
            <Head title={t('my_profile')} />
            <PageHeader title={t('my_profile')} />

            <form onSubmit={(e) => { e.preventDefault(); profile.put(route('admin.profile.update')); }} className="card mb-3">
                <div className="card-body row">
                    <div className="col-md-6"><TextField label={t('name')} value={profile.data.name} onChange={v => profile.setData('name', v)} error={profile.errors.name} /></div>
                    <div className="col-md-6"><TextField label={t('email')} type="email" value={profile.data.email} onChange={v => profile.setData('email', v)} error={profile.errors.email} /></div>
                    <div className="col-md-6"><TextField label={t('phone')} value={profile.data.phone} onChange={v => profile.setData('phone', v)} error={profile.errors.phone} /></div>
                </div>
                <div className="card-footer text-end"><button className="btn btn-primary" disabled={profile.processing}>{t('save')}</button></div>
            </form>

            <form onSubmit={(e) => { e.preventDefault(); pwd.put(route('admin.profile.password'), { onSuccess: () => pwd.reset() }); }} className="card">
                <div className="card-header"><strong>{t('password')}</strong></div>
                <div className="card-body row">
                    <div className="col-md-4"><TextField label="Current password" type="password" value={pwd.data.current_password} onChange={v => pwd.setData('current_password', v)} error={pwd.errors.current_password} /></div>
                    <div className="col-md-4"><TextField label="New password" type="password" value={pwd.data.password} onChange={v => pwd.setData('password', v)} error={pwd.errors.password} /></div>
                    <div className="col-md-4"><TextField label="Confirm new password" type="password" value={pwd.data.password_confirmation} onChange={v => pwd.setData('password_confirmation', v)} error={pwd.errors.password_confirmation} /></div>
                </div>
                <div className="card-footer text-end"><button className="btn btn-primary" disabled={pwd.processing}>{t('save')}</button></div>
            </form>
        </>
    );
}
