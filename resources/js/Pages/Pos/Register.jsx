import React, { useEffect, useMemo, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { SelectField, TextField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

/**
 * Cashier register screen. Shows a product list with a quick filter, a
 * running cart, customer picker, and a payment input. Submitting POSTs to
 * `admin.pos.checkout` which creates the Sale + items + payment in one
 * transaction and decrements per-branch stock.
 */
export default function PosRegister({ session, products = [], customers = [] }) {
    const t = useT();
    const [filter, setFilter] = useState('');
    const [items, setItems] = useState([]);
    const branchId = session?.register?.branch_id;

    const { data, setData, post, processing, errors } = useForm({
        branch_id: branchId ?? '',
        customer_id: '',
        pos_session_id: session?.id ?? null,
        date: new Date().toISOString().slice(0, 10),
        discount: 0,
        paid: 0,
        payment_method: 'cash',
        note: '',
        items: [],
    });

    useEffect(() => { setData('items', items); /* eslint-disable-next-line */ }, [items]);

    const filtered = useMemo(() => {
        const q = filter.trim().toLowerCase();
        if (!q) return products.slice(0, 40);
        return products
            .filter((p) => p.barcode?.toLowerCase().includes(q) || p.sku?.toLowerCase().includes(q) || p.name_en?.toLowerCase().includes(q))
            .slice(0, 80);
    }, [products, filter]);

    const totals = useMemo(() => {
        let subtotal = 0;
        let tax = 0;
        items.forEach((r) => {
            subtotal += Number(r.qty || 0) * Number(r.unit_price || 0);
            tax += Number(r.tax || 0);
        });
        const total = Math.max(0, subtotal + tax - Number(data.discount || 0));
        return { subtotal, tax, total };
    }, [items, data.discount]);

    useEffect(() => {
        setData('paid', totals.total.toFixed(2));
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [totals.total]);

    const addProduct = (p) => {
        const idx = items.findIndex((i) => i.product_id === p.id);
        if (idx >= 0) {
            const copy = [...items];
            copy[idx] = { ...copy[idx], qty: Number(copy[idx].qty) + 1 };
            setItems(copy);
        } else {
            setItems([...items, { product_id: p.id, name: p.name_en, qty: 1, unit_price: Number(p.sale_price ?? 0), tax: 0 }]);
        }
    };

    const updateRow = (idx, patch) => {
        const copy = [...items];
        copy[idx] = { ...copy[idx], ...patch };
        setItems(copy);
    };

    const removeRow = (idx) => {
        const copy = [...items];
        copy.splice(idx, 1);
        setItems(copy);
    };

    const submit = (e) => {
        e.preventDefault();
        if (items.length === 0) return;
        post(route('admin.pos.checkout'));
    };

    const change = Math.max(0, Number(data.paid || 0) - totals.total);

    return (
        <>
            <Head title={t('pos.title')} />
            <PageHeader title={t('pos.title')}
                subtitle={session ? `${session.register?.name} · ${session.register?.branch?.name_en}` : t('pos.no_session')}
                actions={<Link href={route('admin.pos-sessions.index')} className="btn btn-light">{t('back')}</Link>} />

            {!session && (
                <div className="alert alert-warning">{t('pos.no_session_warning')}</div>
            )}

            <div className="row g-3">
                <div className="col-lg-7">
                    <div className="card mb-3">
                        <div className="card-body">
                            <input className="form-control mb-2" placeholder={t('pos.search_or_scan')} value={filter} onChange={(e) => setFilter(e.target.value)} autoFocus />
                            <div className="d-grid gap-2" style={{ maxHeight: '60vh', overflowY: 'auto' }}>
                                {filtered.map((p) => (
                                    <button key={p.id} type="button" className="btn btn-outline-secondary text-start" onClick={() => addProduct(p)}>
                                        <div className="d-flex justify-content-between">
                                            <span><strong>{p.name_en}</strong> {p.barcode && <small className="text-muted">[{p.barcode}]</small>}</span>
                                            <span>{Number(p.sale_price).toFixed(2)}</span>
                                        </div>
                                    </button>
                                ))}
                                {filtered.length === 0 && <div className="text-muted text-center py-3">{t('pos.no_products')}</div>}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-5">
                    <form onSubmit={submit} className="card">
                        <div className="card-body">
                            <SelectField label={t('fields.customer')} value={data.customer_id} onChange={v => setData('customer_id', v)}
                                options={customers.map(c => ({ value: c.id, label: c.name }))} placeholder={t('pos.walk_in')} error={errors.customer_id} />

                            <div className="table-responsive mb-3">
                                <table className="table table-sm align-middle">
                                    <thead><tr>
                                        <th>{t('fields.product')}</th>
                                        <th className="text-end" style={{ width: 90 }}>{t('fields.qty')}</th>
                                        <th className="text-end" style={{ width: 100 }}>{t('fields.unit_price')}</th>
                                        <th className="text-end" style={{ width: 100 }}>{t('fields.line_total')}</th>
                                        <th></th>
                                    </tr></thead>
                                    <tbody>
                                        {items.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-3">{t('pos.cart_empty')}</td></tr>}
                                        {items.map((r, idx) => (
                                            <tr key={idx}>
                                                <td>{r.name}</td>
                                                <td><input type="number" min="0.01" step="0.01" className="form-control form-control-sm text-end" value={r.qty} onChange={(e) => updateRow(idx, { qty: e.target.value })} /></td>
                                                <td><input type="number" min="0" step="0.01" className="form-control form-control-sm text-end" value={r.unit_price} onChange={(e) => updateRow(idx, { unit_price: e.target.value })} /></td>
                                                <td className="text-end">{(Number(r.qty || 0) * Number(r.unit_price || 0)).toFixed(2)}</td>
                                                <td><button type="button" className="btn btn-sm btn-outline-danger" onClick={() => removeRow(idx)}>×</button></td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <dl className="row mb-2">
                                <dt className="col-6">{t('fields.subtotal')}</dt><dd className="col-6 text-end">{totals.subtotal.toFixed(2)}</dd>
                                <dt className="col-6">{t('fields.tax')}</dt><dd className="col-6 text-end">{totals.tax.toFixed(2)}</dd>
                                <dt className="col-6">{t('fields.discount')}</dt>
                                <dd className="col-6 text-end"><input type="number" step="0.01" min="0" className="form-control form-control-sm text-end" value={data.discount} onChange={(e) => setData('discount', e.target.value)} /></dd>
                                <dt className="col-6 fw-bold">{t('fields.total')}</dt><dd className="col-6 text-end fw-bold">{totals.total.toFixed(2)}</dd>
                            </dl>

                            <div className="row">
                                <div className="col-6"><SelectField label={t('fields.method')} value={data.payment_method} onChange={v => setData('payment_method', v)}
                                    options={[{ value: 'cash', label: t('payments.cash') }, { value: 'card', label: t('payments.card') }, { value: 'transfer', label: t('payments.transfer') }]} /></div>
                                <div className="col-6"><TextField label={t('fields.paid')} type="number" step="0.01" value={data.paid} onChange={v => setData('paid', v)} error={errors.paid} /></div>
                            </div>
                            <div className="d-flex justify-content-between">
                                <span>{t('pos.change')}:</span><strong>{change.toFixed(2)}</strong>
                            </div>
                        </div>
                        <div className="card-footer text-end">
                            <button className="btn btn-success btn-lg" disabled={processing || items.length === 0 || !data.branch_id}>{t('pos.complete_sale')}</button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
