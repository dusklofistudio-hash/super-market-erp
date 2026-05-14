import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useT } from '@/lib/i18n';

export default function StockTransferShow({ transfer }) {
    const t = useT();
    const receive = () => {
        if (window.Swal) {
            window.Swal.fire({ icon: 'question', title: t('pos.confirm_receive'), showCancelButton: true, confirmButtonText: t('confirm_delete.confirm'), cancelButtonText: t('cancel') })
                .then((r) => { if (r.isConfirmed) router.post(route('admin.stock-transfers.receive', transfer.id)); });
        } else {
            router.post(route('admin.stock-transfers.receive', transfer.id));
        }
    };
    return (
        <>
            <Head title={`${t('pages.stock_transfers.title')} · ${transfer.ref_no}`} />
            <PageHeader title={`${t('pages.stock_transfers.title')} · ${transfer.ref_no}`}
                actions={
                    <>
                        {transfer.status === 'sent' && <button onClick={receive} className="btn btn-success me-2">{t('transfer_receive')}</button>}
                        <Link href={route('admin.stock-transfers.index')} className="btn btn-light">{t('back')}</Link>
                    </>
                } />
            <div className="card mb-3">
                <div className="card-body">
                    <table className="table table-borderless mb-0">
                        <tbody>
                            <tr><th>{t('fields.from_branch')}</th><td>{transfer.from_branch?.name_en}</td></tr>
                            <tr><th>{t('fields.to_branch')}</th><td>{transfer.to_branch?.name_en}</td></tr>
                            <tr><th>{t('fields.date')}</th><td>{transfer.date?.slice(0, 10)}</td></tr>
                            <tr><th>{t('status')}</th><td>{transfer.status}</td></tr>
                            <tr><th>{t('fields.note')}</th><td>{transfer.note ?? '—'}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div className="card">
                <div className="card-body">
                    <table className="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{t('fields.product')}</th>
                                <th className="text-end">{t('fields.qty')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(transfer.items || []).map((it) => (
                                <tr key={it.id}>
                                    <td>{it.product?.name_en}</td>
                                    <td className="text-end">{Number(it.qty).toFixed(2)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
