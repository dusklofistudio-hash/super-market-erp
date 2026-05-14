import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, SelectField, DateField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ExpenseForm({ expense, branches = [], categories = [] }) {
    const t = useT();
    const isEdit = !!expense;
    const today = new Date().toISOString().slice(0, 10);
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: expense?.branch_id ?? (branches[0]?.id ?? ''),
        category_id: expense?.category_id ?? '',
        date: expense?.date?.slice(0, 10) ?? today,
        amount: expense?.amount ?? '',
        note: expense?.note ?? '',
    });
    const submit = (e) => {
        e.preventDefault();
        if (isEdit) put(route('admin.expenses.update', expense.id));
        else post(route('admin.expenses.store'));
    };
    return (
        <>
            <Head title={t('pages.expenses.title')} />
            <PageHeader title={t('pages.expenses.title')}
                actions={<Link href={route('admin.expenses.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-6"><SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)} options={branches.map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} error={errors.branch_id} required /></div>
                    <div className="col-md-6"><SelectField label={t('fields.category')} value={data.category_id} onChange={v => setData('category_id', v)} options={categories.map(c => ({ value: c.id, label: c.name }))} placeholder="—" error={errors.category_id} /></div>
                    <div className="col-md-6"><DateField label={t('fields.date')} value={data.date} onChange={v => setData('date', v)} error={errors.date} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.amount')} type="number" step="0.01" value={data.amount} onChange={v => setData('amount', v)} error={errors.amount} required /></div>
                    <div className="col-12"><TextAreaField label={t('fields.note')} value={data.note} onChange={v => setData('note', v)} error={errors.note} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.expenses.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
