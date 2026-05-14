import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { DateField, SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function SalesSummary({ rows = [], totals = {}, filters = {} }) {
    const t = useT();
    const { data, setData } = useForm({
        from: filters.from?.slice?.(0, 10) ?? '',
        to: filters.to?.slice?.(0, 10) ?? '',
        branch_id: filters.branchId ?? '',
    });
    const apply = (e) => { e.preventDefault(); router.get(route('admin.reports.sales-summary'), data, { preserveState: true }); };

    return (
        <>
            <Head title={t('reports.sales_summary')} />
            <PageHeader title={t('reports.sales_summary')} />
            <form onSubmit={apply} className="card mb-3">
                <div className="card-body row">
                    <div className="col-md-3"><DateField label={t('fields.from')} value={data.from} onChange={v => setData('from', v)} /></div>
                    <div className="col-md-3"><DateField label={t('fields.to')} value={data.to} onChange={v => setData('to', v)} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)}
                        options={(filters.branches || []).map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} placeholder={t('all_branches')} /></div>
                    <div className="col-md-2 d-flex align-items-end"><button className="btn btn-primary w-100">{t('apply')}</button></div>
                </div>
            </form>
            <div className="card">
                <div className="card-body">
                    <table className="table align-middle">
                        <thead><tr>
                            <th>{t('fields.date')}</th>
                            <th className="text-end">{t('fields.count')}</th>
                            <th className="text-end">{t('fields.total')}</th>
                            <th className="text-end">{t('fields.paid')}</th>
                        </tr></thead>
                        <tbody>
                            {rows.length === 0 && <tr><td colSpan={4} className="text-muted text-center py-3">—</td></tr>}
                            {rows.map((r) => (
                                <tr key={r.day}>
                                    <td>{r.day}</td>
                                    <td className="text-end">{r.count}</td>
                                    <td className="text-end">{Number(r.total).toFixed(2)}</td>
                                    <td className="text-end">{Number(r.paid).toFixed(2)}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot>
                            <tr className="fw-bold">
                                <th>{t('fields.totals')}</th>
                                <th className="text-end">{totals.count ?? 0}</th>
                                <th className="text-end">{Number(totals.total ?? 0).toFixed(2)}</th>
                                <th className="text-end">{Number(totals.paid ?? 0).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </>
    );
}
