import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TextField, TextAreaField, SelectField, CheckboxField, FileField } from '@/Components/Form';
import { useT } from '@/lib/i18n';

export default function ProductForm({ product, lookups = {} }) {
    const t = useT();
    const isEdit = !!product;
    const { categories = [], brands = [], units = [], tax_rates = [] } = lookups;
    const { data, setData, post, processing, errors } = useForm({
        _method: isEdit ? 'put' : 'post',
        barcode: product?.barcode ?? '',
        sku: product?.sku ?? '',
        name_en: product?.name_en ?? '',
        name_kh: product?.name_kh ?? '',
        description: product?.description ?? '',
        category_id: product?.category_id ?? '',
        brand_id: product?.brand_id ?? '',
        unit_id: product?.unit_id ?? '',
        tax_rate_id: product?.tax_rate_id ?? '',
        cost_price: product?.cost_price ?? 0,
        sale_price: product?.sale_price ?? 0,
        alert_qty: product?.alert_qty ?? 0,
        image: null,
        is_active: product?.is_active ?? true,
    });
    const submit = (e) => {
        e.preventDefault();
        post(isEdit ? route('admin.products.update', product.id) : route('admin.products.store'), { forceFormData: true });
    };
    return (
        <>
            <Head title={t('pages.products.title')} />
            <PageHeader title={t('pages.products.title')}
                actions={<Link href={route('admin.products.index')} className="btn btn-light">{t('back')}</Link>} />
            <form onSubmit={submit} className="card">
                <div className="card-body row">
                    <div className="col-md-4"><TextField label={t('fields.barcode')} value={data.barcode} onChange={v => setData('barcode', v)} error={errors.barcode} required /></div>
                    <div className="col-md-4"><TextField label={t('fields.sku')} value={data.sku} onChange={v => setData('sku', v)} error={errors.sku} required /></div>
                    <div className="col-md-4"><SelectField label={t('fields.category')} value={data.category_id} onChange={v => setData('category_id', v)} placeholder="—" options={categories.map(c => ({ value: c.id, label: c.name_en }))} /></div>
                    <div className="col-md-6"><TextField label={t('fields.name_en')} value={data.name_en} onChange={v => setData('name_en', v)} error={errors.name_en} required /></div>
                    <div className="col-md-6"><TextField label={t('fields.name_kh')} value={data.name_kh} onChange={v => setData('name_kh', v)} error={errors.name_kh} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.brand')} value={data.brand_id} onChange={v => setData('brand_id', v)} placeholder="—" options={brands.map(b => ({ value: b.id, label: b.name }))} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.unit')} value={data.unit_id} onChange={v => setData('unit_id', v)} placeholder="—" options={units.map(u => ({ value: u.id, label: u.name_en }))} /></div>
                    <div className="col-md-4"><SelectField label={t('fields.tax_rate')} value={data.tax_rate_id} onChange={v => setData('tax_rate_id', v)} placeholder="—" options={tax_rates.map(tr => ({ value: tr.id, label: `${tr.name} (${tr.rate}%)` }))} /></div>
                    <div className="col-md-3"><TextField label={t('fields.cost_price')} type="number" step="0.0001" value={data.cost_price} onChange={v => setData('cost_price', v)} error={errors.cost_price} required /></div>
                    <div className="col-md-3"><TextField label={t('fields.sale_price')} type="number" step="0.0001" value={data.sale_price} onChange={v => setData('sale_price', v)} error={errors.sale_price} required /></div>
                    <div className="col-md-3"><TextField label={t('fields.alert_qty')} type="number" step="0.0001" value={data.alert_qty} onChange={v => setData('alert_qty', v)} error={errors.alert_qty} required /></div>
                    <div className="col-md-3"><FileField label={t('fields.image')} onChange={f => setData('image', f)} error={errors.image} /></div>
                    <div className="col-12"><TextAreaField label={t('fields.description')} value={data.description} onChange={v => setData('description', v)} error={errors.description} /></div>
                    <div className="col-12"><CheckboxField label={t('active')} value={data.is_active} onChange={v => setData('is_active', v)} /></div>
                </div>
                <div className="card-footer text-end">
                    <Link href={route('admin.products.index')} className="btn btn-light me-2">{t('cancel')}</Link>
                    <button className="btn btn-primary" disabled={processing}>{t('save')}</button>
                </div>
            </form>
        </>
    );
}
