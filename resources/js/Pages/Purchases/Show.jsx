import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, DateField, TextAreaField, SelectField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function PurchaseShow({ purchase }) {
    const t = useT();
    const [open, setOpen] = useState(false);
    const balance = Number(purchase.total) - Number(purchase.paid);
    const today = new Date().toISOString().slice(0, 10);
    const { data, setData, post, processing, errors, reset } = useForm({
        date: today,
        amount: balance > 0 ? balance.toFixed(2) : '0',
        method: 'cash',
        reference: '',
        note: '',
    });
    const submit = (e) => {
        e.preventDefault();
        post(route('admin.purchases.payments.add', purchase.id), { onSuccess: () => { reset(); setOpen(false); } });
    };

    return (
        <>
            <Head title={`${t('pages.purchases.title')} · ${purchase.ref_no}`} />
            <PageHeader title={`${t('pages.purchases.title')} · ${purchase.ref_no}`}
                actions={<Link href={route('admin.purchases.index')} className="btn btn-light">{t('back')}</Link>} />
            <div className="row">
                <div className="col-lg-8">
                    <div className="card mb-3">
                        <div className="card-body">
                            <table className="table table-borderless mb-0">
                                <tbody>
                                    <tr><th>{t('fields.branch')}</th><td>{purchase.branch?.name_en}</td></tr>
                                    <tr><th>{t('fields.supplier')}</th><td>{purchase.supplier?.name ?? '—'}</td></tr>
                                    <tr><th>{t('fields.date')}</th><td>{purchase.date?.slice(0, 10)}</td></tr>
                                    <tr><th>{t('status')}</th><td>{purchase.status}</td></tr>
                                    <tr><th>{t('fields.note')}</th><td>{purchase.note ?? '—'}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="card mb-3">
                        <div className="card-body">
                            <table className="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{t('fields.product')}</th>
                                        <th className="text-end">{t('fields.qty')}</th>
                                        <th className="text-end">{t('fields.unit_cost')}</th>
                                        <th className="text-end">{t('fields.tax')}</th>
                                        <th className="text-end">{t('fields.line_total')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {(purchase.items || []).map((it) => (
                                        <tr key={it.id}>
                                            <td>{it.product?.name_en}</td>
                                            <td className="text-end">{Number(it.qty).toFixed(2)}</td>
                                            <td className="text-end">{Number(it.unit_cost).toFixed(2)}</td>
                                            <td className="text-end">{Number(it.tax).toFixed(2)}</td>
                                            <td className="text-end">{Number(it.subtotal).toFixed(2)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="card mb-3">
                        <div className="card-body">
                            <h6 className="mb-3">{t('fields.totals')}</h6>
                            <dl className="row mb-0">
                                <dt className="col-6">{t('fields.subtotal')}</dt><dd className="col-6 text-end">{Number(purchase.subtotal).toFixed(2)}</dd>
                                <dt className="col-6">{t('fields.tax')}</dt><dd className="col-6 text-end">{Number(purchase.tax).toFixed(2)}</dd>
                                <dt className="col-6">{t('fields.discount')}</dt><dd className="col-6 text-end">{Number(purchase.discount).toFixed(2)}</dd>
                                <dt className="col-6 fw-bold">{t('fields.total')}</dt><dd className="col-6 text-end fw-bold">{Number(purchase.total).toFixed(2)}</dd>
                                <dt className="col-6">{t('fields.paid')}</dt><dd className="col-6 text-end">{Number(purchase.paid).toFixed(2)}</dd>
                                <dt className="col-6 fw-bold">{t('fields.balance')}</dt><dd className="col-6 text-end fw-bold text-danger">{balance.toFixed(2)}</dd>
                            </dl>
                        </div>
                    </div>

                    <div className="card mb-3">
                        <div className="card-body">
                            <div className="d-flex justify-content-between align-items-center mb-2">
                                <h6 className="mb-0">{t('fields.payments')}</h6>
                                {balance > 0 && (
                                    <button type="button" className="btn btn-sm btn-primary" onClick={() => setOpen(true)}>
                                        + {t('fields.add_payment')}
                                    </button>
                                )}
                            </div>
                            {(purchase.payments || []).length === 0 && <div className="text-muted small">—</div>}
                            <ul className="list-unstyled mb-0">
                                {(purchase.payments || []).map((p) => (
                                    <li key={p.id} className="d-flex justify-content-between border-bottom py-1">
                                        <span>{p.date?.slice(0, 10)} · {p.method}</span>
                                        <span>{Number(p.amount).toFixed(2)}</span>
                                    </li>
                                ))}
                            </ul>

                            {open && (
                                <form onSubmit={submit} className="mt-3">
                                    <DateField label={t('fields.date')} value={data.date} onChange={v => setData('date', v)} error={errors.date} required />
                                    <TextField label={t('fields.amount')} type="number" step="0.01" value={data.amount} onChange={v => setData('amount', v)} error={errors.amount} required />
                                    <SelectField label={t('fields.method')} value={data.method} onChange={v => setData('method', v)}
                                        options={[{ value: 'cash', label: t('payments.cash') }, { value: 'card', label: t('payments.card') }, { value: 'transfer', label: t('payments.transfer') }]} required />
                                    <TextField label={t('fields.reference')} value={data.reference} onChange={v => setData('reference', v)} error={errors.reference} />
                                    <TextAreaField label={t('fields.note')} value={data.note} onChange={v => setData('note', v)} error={errors.note} />
                                    <div className="text-end">
                                        <button type="button" className="btn btn-light me-2" onClick={() => setOpen(false)}>{t('cancel')}</button>
                                        <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
