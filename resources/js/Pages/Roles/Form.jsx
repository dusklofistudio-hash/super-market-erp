import React, { useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function RoleForm({ role, permissions = [], selected_permissions = [] }) {
    const t = useT();
    const isEdit = !!role;
    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name ?? '',
        slug: role?.slug ?? '',
        description: role?.description ?? '',
        permissions: selected_permissions,
    });

    const groups = useMemo(() => {
        const out = {};
        for (const p of permissions) {
            (out[p.module] = out[p.module] || []).push(p);
        }
        return out;
    }, [permissions]);

    const toggle = (id) => {
        setData('permissions', data.permissions.includes(id)
            ? data.permissions.filter(p => p !== id)
            : [...data.permissions, id]);
    };

    const toggleGroup = (modulePerms) => {
        const ids = modulePerms.map(p => p.id);
        const allOn = ids.every(id => data.permissions.includes(id));
        if (allOn) {
            setData('permissions', data.permissions.filter(p => !ids.includes(p)));
        } else {
            setData('permissions', Array.from(new Set([...data.permissions, ...ids])));
        }
    };

    const submit = (e) => {
        e.preventDefault();
        isEdit ? put(route('admin.roles.update', role.id)) : post(route('admin.roles.store'));
    };

    return (
        <>
            <Head title={t('pages.roles.title')} />
            <PageHeader title={t('pages.roles.title')}
                actions={<Link href={route('admin.roles.index')} className="btn btn-light">{t('back')}</Link>}
            />
            <form onSubmit={submit} className="card">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-6"><TextField label={t('name')} value={data.name} onChange={v => setData('name', v)} error={errors.name} required /></div>
                        <div className="col-md-6"><TextField label={t('fields.slug')} value={data.slug} onChange={v => setData('slug', v)} error={errors.slug} placeholder="auto" /></div>
                        <div className="col-12"><TextAreaField label={t('fields.description')} value={data.description} onChange={v => setData('description', v)} error={errors.description} /></div>
                    </div>

                    <h6 className="mt-3 mb-2">{t('fields.permissions')}</h6>
                    <div className="row g-3">
                        {Object.entries(groups).map(([module, items]) => {
                            const all = items.every(p => data.permissions.includes(p.id));
                            return (
                                <div key={module} className="col-md-6 col-lg-4">
                                    <div className="card h-100">
                                        <div className="card-header py-2 d-flex justify-content-between align-items-center">
                                            <strong>{module}</strong>
                                            <button type="button" onClick={() => toggleGroup(items)} className="btn btn-sm btn-link p-0">
                                                {all ? 'Clear' : 'All'}
                                            </button>
                                        </div>
                                        <div className="card-body py-2">
                                            {items.map(p => (
                                                <div className="form-check" key={p.id}>
                                                    <input type="checkbox" id={`p${p.id}`} className="form-check-input" checked={data.permissions.includes(p.id)} onChange={() => toggle(p.id)} />
                                                    <label className="form-check-label" htmlFor={`p${p.id}`}>{p.slug}</label>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.roles.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
