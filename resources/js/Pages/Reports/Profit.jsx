import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { DateField, SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ProfitReport({ rows = {}, filters = {} }) {
    const t = useT();
    const { data, setData } = useForm({
        from: filters.from?.slice?.(0, 10) ?? '',
        to: filters.to?.slice?.(0, 10) ?? '',
        branch_id: filters.branchId ?? '',
    });
    const apply = (e) => { e.preventDefault(); router.get(route('admin.reports.profit'), data, { preserveState: true }); };
    return (
        <>
            <Head title={t('reports.profit')} />
            <PageHeader title={t('reports.profit')} />
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
                {[
                    { key: 'sales', label: t('reports.sales_total'), color: 'success' },
                    { key: 'purchases', label: t('reports.purchases_total'), color: 'info' },
                    { key: 'expenses', label: t('reports.expenses_total'), color: 'warning' },
                    { key: 'profit', label: t('reports.profit'), color: 'primary' },
                ].map((card) => (
                    <div key={card.key} className="col-md-3">
                        <div className={`card text-bg-${card.color}`}>
                            <div className="card-body">
                                <div className="small">{card.label}</div>
                                <div className="h3 mb-0">{Number(rows[card.key] ?? 0).toFixed(2)}</div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
