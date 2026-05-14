import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function StockByBranch({ rows = [], filters = {} }) {
    const t = useT();
    const { data, setData } = useForm({ branch_id: filters.branchId ?? '' });
    const apply = (e) => { e.preventDefault(); router.get(route('admin.reports.stock-by-branch'), data, { preserveState: true }); };
    return (
        <>
            <Head title={t('reports.stock_by_branch')} />
            <PageHeader title={t('reports.stock_by_branch')} />
            <form onSubmit={apply} className="card mb-3">
                <div className="card-body row">
                    <div className="col-md-6"><SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)}
                        options={(filters.branches || []).map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} placeholder={t('all_branches')} /></div>
                    <div className="col-md-2 d-flex align-items-end"><button className="btn btn-primary w-100">{t('apply')}</button></div>
                </div>
            </form>
            <div className="card">
                <div className="card-body">
                    <table className="table align-middle">
                        <thead><tr>
                            <th>{t('fields.branch')}</th>
                            <th>{t('fields.product')}</th>
                            <th>{t('fields.sku')}</th>
                            <th className="text-end">{t('fields.qty')}</th>
                            <th className="text-end">{t('fields.alert_qty')}</th>
                        </tr></thead>
                        <tbody>
                            {rows.length === 0 && <tr><td colSpan={5} className="text-muted text-center py-3">—</td></tr>}
                            {rows.map((r) => {
                                const low = Number(r.qty) <= Number(r.product?.alert_qty ?? 0);
                                return (
                                    <tr key={r.id} className={low ? 'table-warning' : ''}>
                                        <td>{r.branch?.name_en}</td>
                                        <td>{r.product?.name_en}</td>
                                        <td>{r.product?.sku}</td>
                                        <td className="text-end">{Number(r.qty).toFixed(2)}</td>
                                        <td className="text-end">{Number(r.product?.alert_qty ?? 0).toFixed(2)}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
