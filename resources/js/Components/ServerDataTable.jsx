import React, { useImperativeHandle, forwardRef } from 'react';
import useDataTable from '../Hooks/useDataTable';

/**
 * Bootstrap 5 server-side DataTable backed by a Yajra endpoint.
 *
 * Example:
 *   <ServerDataTable
 *       url="/admin/branches/data"
 *       columns=[{data:'code',title:'Code'},{data:'name_en',title:'Name'},{data:'action',orderable:false,searchable:false}]
 *   />
 */
const ServerDataTable = forwardRef(function ServerDataTable(
    { url, columns, order, extra, language, pageLength, reloadKey },
    ref,
) {
    const { tableRef, reload } = useDataTable({ url, columns, order, extra, language, pageLength, reloadKey });

    useImperativeHandle(ref, () => ({ reload }), [reload]);

    return (
        <div className="card">
            <div className="card-body">
                <table ref={tableRef} className="table table-striped table-hover align-middle w-100"></table>
            </div>
        </div>
    );
});

export default ServerDataTable;
