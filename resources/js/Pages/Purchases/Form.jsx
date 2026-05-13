import React, { useEffect, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { SelectField, DateField, TextField, TextAreaField } from '@/Components/Form';
import LineItemsEditor from '@/Components/LineItemsEditor';
import { useT } from '@/lib/i18n';

export default function PurchaseForm({ purchase, branches = [], suppliers = [], products = [] }) {
    const t = useT();
    const isEdit = !!purchase;
    const today = new Date().toISOString().slice(0, 10);

    const seedItems = (purchase?.items || []).map((it) => {
        const p = products.find((x) => x.id === it.product_id);
        return { product_id: it.product_id, name: p?.name_en ?? `#${it.product_id}`, qty: Number(it.qty), unit_cost: Number(it.unit_cost), tax: Number(it.tax) };
    });

    const [items, setItems] = useState(seedItems);
    const { data, setData, post, put, processing, errors } = useForm({
        branch_id: purchase?.branch_id ?? (branches[0]?.id ?? ''),
        supplier_id: purchase?.supplier_id ?? '',
        date: purchase?.date?.slice(0, 10) ?? today,
        discount: purchase?.discount ?? 0,
        note: purchase?.note ?? '',
        items: seedItems,
    });

    useEffect(() => { setData('items', items); /* eslint-disable-next-line */ }, [items]);

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) put(route('admin.purchases.update', purchase.id));
        else post(route('admin.purchases.store'));
    };

    return (
        <>
            <Head title={t('pages.purchases.title')} />
            <PageHeader title={t('pages.purchases.title')}
                actions={<Link href={route('admin.purchases.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-4">
                            <SelectField label={t('fields.branch')} value={data.branch_id} onChange={v => setData('branch_id', v)}
                                options={branches.map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} error={errors.branch_id} required />
                        </div>
                        <div className="col-md-4">
                            <SelectField label={t('fields.supplier')} value={data.supplier_id} onChange={v => setData('supplier_id', v)}
                                options={suppliers.map(s => ({ value: s.id, label: s.name }))} placeholder="—" error={errors.supplier_id} />
                        </div>
                        <div className="col-md-4">
                            <DateField label={t('fields.date')} value={data.date} onChange={v => setData('date', v)} error={errors.date} required />
                        </div>
                    </div>

                    <LineItemsEditor items={items} setItems={setItems} products={products} priceField="unit_cost" showTax errors={errors} />

                    <div className="row mt-3">
                        <div className="col-md-4 offset-md-8">
                            <TextField label={t('fields.discount')} type="number" step="0.01" value={data.discount} onChange={v => setData('discount', v)} error={errors.discount} />
                        </div>
                        <div className="col-12">
                            <TextAreaField label={t('fields.note')} value={data.note} onChange={v => setData('note', v)} error={errors.note} />
                        </div>
                    </div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.purchases.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
