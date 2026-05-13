import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { DateField, SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ExpensesReport({ rows = [], byCategory = {}, total = 0, filters = {} }) {
    const t = useT();
    const { data, setData } = useForm({
        from: filters.from?.slice?.(0, 10) ?? '',
        to: filters.to?.slice?.(0, 10) ?? '',
        branch_id: filters.branchId ?? '',
    });
    const apply = (e) => { e.preventDefault(); router.get(route('admin.reports.expenses'), data, { preserveState: true }); };

    return (
        <>
            <Head title={t('reports.expenses')} />
            <PageHeader title={t('reports.expenses')} />
            <form onSubmit={apply} className="card mb-3">
                <div className="card-body row">
                    <div className="col-md-3"><DateField label={t('fields.from')} value={data.from} onChange={v => setData('from', v)} /></div>
                    <div className="col-md-3"><DateField label={t('fields.to')} value={data.to} onChange={v => setData('to', v)} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)}
                        options={(filters.branches || []).map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} placeholder={t('all_branches')} /></div>
                    <div className="col-md-2 d-flex align-items-end"><button className="btn btn-primary w-100">{t('apply')}</button></div>
                </div>
            </form>

            <div className="row g-3">
                <div className="col-lg-4">
                    <div className="card">
                        <div className="card-body">
                            <h6>{t('reports.by_category')}</h6>
                            <table className="table mb-0">
                                <tbody>
                                    {Object.entries(byCategory).map(([name, amt]) => (
                                        <tr key={name}>
                                            <td>{name}</td>
                                            <td className="text-end">{Number(amt).toFixed(2)}</td>
                                        </tr>
                                    ))}
                                    <tr className="fw-bold">
                                        <td>{t('fields.total')}</td>
                                        <td className="text-end">{Number(total).toFixed(2)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <div className="card">
                        <div className="card-body">
                            <table className="table align-middle mb-0">
                                <thead><tr>
                                    <th>{t('fields.date')}</th>
                                    <th>{t('fields.branch')}</th>
                                    <th>{t('fields.category')}</th>
                                    <th className="text-end">{t('fields.amount')}</th>
                                </tr></thead>
                                <tbody>
                                    {rows.length === 0 && <tr><td colSpan={4} className="text-muted text-center py-3">—</td></tr>}
                                    {rows.map((r) => (
                                        <tr key={r.id}>
                                            <td>{r.date?.slice(0, 10)}</td>
                                            <td>{r.branch?.name_en}</td>
                                            <td>{r.category?.name ?? '—'}</td>
                                            <td className="text-end">{Number(r.amount).toFixed(2)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
