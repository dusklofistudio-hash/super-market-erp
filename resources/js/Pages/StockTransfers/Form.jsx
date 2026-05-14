import React, { useEffect, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { SelectField, DateField, TextAreaField } from '@/Components/Form';
import LineItemsEditor from '@/Components/LineItemsEditor';
import { useT } from '@/lib/i18n';

export default function StockTransferForm({ branches = [], products = [] }) {
    const t = useT();
    const today = new Date().toISOString().slice(0, 10);
    const [items, setItems] = useState([]);
    const { data, setData, post, processing, errors } = useForm({
        from_branch_id: branches[0]?.id ?? '',
        to_branch_id: branches[1]?.id ?? '',
        date: today,
        note: '',
        items: [],
    });

    useEffect(() => { setData('items', items); /* eslint-disable-next-line */ }, [items]);

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.stock-transfers.store'));
    };

    return (
        <>
            <Head title={t('pages.stock_transfers.title')} />
            <PageHeader title={t('pages.stock_transfers.title')}
                actions={<Link href={route('admin.stock-transfers.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-4">
                            <SelectField label={t('fields.from_branch')} value={data.from_branch_id} onChange={v => setData('from_branch_id', v)}
                                options={branches.map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} error={errors.from_branch_id} required />
                        </div>
                        <div className="col-md-4">
                            <SelectField label={t('fields.to_branch')} value={data.to_branch_id} onChange={v => setData('to_branch_id', v)}
                                options={branches.map(b => ({ value: b.id, label: `${b.code} · ${b.name_en}` }))} error={errors.to_branch_id} required />
                        </div>
                        <div className="col-md-4">
                            <DateField label={t('fields.date')} value={data.date} onChange={v => setData('date', v)} error={errors.date} required />
                        </div>
                        <div className="col-12">
                            <TextAreaField label={t('fields.note')} value={data.note} onChange={v => setData('note', v)} error={errors.note} />
                        </div>
                    </div>
                    <LineItemsEditor items={items} setItems={setItems} products={products} priceField={null} showTax={false} errors={errors} />
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.stock-transfers.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
