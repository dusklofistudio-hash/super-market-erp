import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useT } from '@/lib/i18n';

export default function SaleShow({ sale }) {
    const t = useT();
    const balance = Number(sale.total) - Number(sale.paid);
    return (
        <>
            <Head title={`${t('pages.sales.title')} · ${sale.ref_no}`} />
            <PageHeader title={`${t('pages.sales.title')} · ${sale.ref_no}`}
                actions={<Link href={route('admin.sales.index')} className="btn btn-light">{t('back')}</Link>} />
            <div className="row">
                <div className="col-lg-8">
                    <div className="card mb-3"><div className="card-body">
                        <table className="table table-borderless mb-0">
                            <tbody>
                                <tr><th>{t('fields.branch')}</th><td>{sale.branch?.name_en}</td></tr>
                                <tr><th>{t('fields.customer')}</th><td>{sale.customer?.name ?? '—'}</td></tr>
                                <tr><th>{t('fields.cashier')}</th><td>{sale.user?.name ?? '—'}</td></tr>
                                <tr><th>{t('fields.date')}</th><td>{sale.date}</td></tr>
                                <tr><th>{t('status')}</th><td>{sale.status}</td></tr>
                            </tbody>
                        </table>
                    </div></div>
                    <div className="card"><div className="card-body">
                        <table className="table align-middle mb-0">
                            <thead><tr>
                                <th>{t('fields.product')}</th>
                                <th className="text-end">{t('fields.qty')}</th>
                                <th className="text-end">{t('fields.unit_price')}</th>
                                <th className="text-end">{t('fields.tax')}</th>
                                <th className="text-end">{t('fields.line_total')}</th>
                            </tr></thead>
                            <tbody>
                                {(sale.items || []).map(it => (
                                    <tr key={it.id}>
                                        <td>{it.product?.name_en}</td>
                                        <td className="text-end">{Number(it.qty).toFixed(2)}</td>
                                        <td className="text-end">{Number(it.unit_price).toFixed(2)}</td>
                                        <td className="text-end">{Number(it.tax).toFixed(2)}</td>
                                        <td className="text-end">{Number(it.subtotal).toFixed(2)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div></div>
                </div>
                <div className="col-lg-4">
                    <div className="card mb-3"><div className="card-body">
                        <h6 className="mb-3">{t('fields.totals')}</h6>
                        <dl className="row mb-0">
                            <dt className="col-6">{t('fields.subtotal')}</dt><dd className="col-6 text-end">{Number(sale.subtotal).toFixed(2)}</dd>
                            <dt className="col-6">{t('fields.tax')}</dt><dd className="col-6 text-end">{Number(sale.tax).toFixed(2)}</dd>
                            <dt className="col-6">{t('fields.discount')}</dt><dd className="col-6 text-end">{Number(sale.discount).toFixed(2)}</dd>
                            <dt className="col-6 fw-bold">{t('fields.total')}</dt><dd className="col-6 text-end fw-bold">{Number(sale.total).toFixed(2)}</dd>
                            <dt className="col-6">{t('fields.paid')}</dt><dd className="col-6 text-end">{Number(sale.paid).toFixed(2)}</dd>
                            <dt className="col-6 fw-bold">{t('fields.balance')}</dt><dd className="col-6 text-end fw-bold text-danger">{balance.toFixed(2)}</dd>
                        </dl>
                    </div></div>
                    <div className="card"><div className="card-body">
                        <h6 className="mb-2">{t('fields.payments')}</h6>
                        {(sale.payments || []).length === 0 && <div className="text-muted small">—</div>}
                        <ul className="list-unstyled mb-0">
                            {(sale.payments || []).map(p => (
                                <li key={p.id} className="d-flex justify-content-between border-bottom py-1">
                                    <span>{p.date?.slice(0, 10)} · {p.method}</span>
                                    <span>{Number(p.amount).toFixed(2)}</span>
                                </li>
                            ))}
                        </ul>
                    </div></div>
                </div>
            </div>
        </>
    );
}
