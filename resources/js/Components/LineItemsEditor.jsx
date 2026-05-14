import React, { useMemo, useState } from 'react';
import { useT } from '@/lib/i18n';

/**
 * Editable line-item table shared by Purchases, Sales (POS), Stock
 * Adjustments and Stock Transfers. Each row has product + qty + optional
 * price/tax columns. Caller passes in `products` and current `items` array
 * (state) plus `priceField` (`unit_cost` for purchases, `unit_price` for
 * sales, null to hide pricing for adjustments/transfers).
 */
export default function LineItemsEditor({ items, setItems, products = [], priceField = null, showTax = false, errors = {} }) {
    const t = useT();
    const [pick, setPick] = useState('');

    const productMap = useMemo(() => {
        const map = new Map();
        products.forEach((p) => map.set(p.id, p));
        return map;
    }, [products]);

    const addProduct = (id) => {
        const product = productMap.get(Number(id));
        if (!product) return;
        const existing = items.findIndex((it) => Number(it.product_id) === Number(id));
        if (existing >= 0) {
            const copy = [...items];
            copy[existing] = { ...copy[existing], qty: Number(copy[existing].qty) + 1 };
            setItems(copy);
            return;
        }
        const row = { product_id: product.id, name: product.name_en, qty: 1 };
        if (priceField === 'unit_cost') row.unit_cost = Number(product.cost_price ?? 0);
        if (priceField === 'unit_price') row.unit_price = Number(product.sale_price ?? 0);
        if (showTax) row.tax = 0;
        setItems([...items, row]);
        setPick('');
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

    const totals = useMemo(() => {
        let subtotal = 0;
        let tax = 0;
        items.forEach((row) => {
            const price = Number(row[priceField] ?? 0);
            subtotal += Number(row.qty || 0) * price;
            tax += Number(row.tax || 0);
        });
        return { subtotal, tax, total: subtotal + tax };
    }, [items, priceField]);

    return (
        <div>
            <div className="row align-items-end mb-3">
                <div className="col-md-8">
                    <label className="form-label">{t('pos.add_product')}</label>
                    <select className="form-select" value={pick} onChange={(e) => { setPick(e.target.value); addProduct(e.target.value); }}>
                        <option value="">— {t('search')} —</option>
                        {products.map((p) => (
                            <option key={p.id} value={p.id}>{p.barcode ? `[${p.barcode}] ` : ''}{p.name_en}</option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="table-responsive">
                <table className="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>{t('fields.product')}</th>
                            <th className="text-end" style={{ width: 120 }}>{t('fields.qty')}</th>
                            {priceField && <th className="text-end" style={{ width: 140 }}>{priceField === 'unit_cost' ? t('fields.unit_cost') : t('fields.unit_price')}</th>}
                            {showTax && <th className="text-end" style={{ width: 120 }}>{t('fields.tax')}</th>}
                            {priceField && <th className="text-end" style={{ width: 140 }}>{t('fields.line_total')}</th>}
                            <th style={{ width: 50 }}></th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.length === 0 && (
                            <tr><td colSpan={6} className="text-center text-muted py-3">{t('pos.no_items')}</td></tr>
                        )}
                        {items.map((row, idx) => {
                            const price = Number(row[priceField] ?? 0);
                            const lineTotal = Number(row.qty || 0) * price + Number(row.tax || 0);
                            const err = errors[`items.${idx}.qty`] || errors[`items.${idx}.${priceField}`];
                            return (
                                <tr key={idx}>
                                    <td>{row.name}</td>
                                    <td>
                                        <input type="number" min="0" step="0.01" className="form-control text-end"
                                            value={row.qty} onChange={(e) => updateRow(idx, { qty: e.target.value })} />
                                    </td>
                                    {priceField && (
                                        <td>
                                            <input type="number" min="0" step="0.01" className="form-control text-end"
                                                value={row[priceField] ?? 0}
                                                onChange={(e) => updateRow(idx, { [priceField]: e.target.value })} />
                                        </td>
                                    )}
                                    {showTax && (
                                        <td>
                                            <input type="number" min="0" step="0.01" className="form-control text-end"
                                                value={row.tax ?? 0}
                                                onChange={(e) => updateRow(idx, { tax: e.target.value })} />
                                        </td>
                                    )}
                                    {priceField && <td className="text-end">{lineTotal.toFixed(2)}</td>}
                                    <td className="text-end">
                                        <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => removeRow(idx)} aria-label="remove">×</button>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                    {priceField && items.length > 0 && (
                        <tfoot>
                            <tr>
                                <th colSpan={2} className="text-end">{t('fields.subtotal')}</th>
                                <th className="text-end" colSpan={showTax ? 1 : 1}>{totals.subtotal.toFixed(2)}</th>
                                {showTax && <th className="text-end">{totals.tax.toFixed(2)}</th>}
                                <th className="text-end">{totals.total.toFixed(2)}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>
            {errors.items && <div className="text-danger small">{errors.items}</div>}
        </div>
    );
}
