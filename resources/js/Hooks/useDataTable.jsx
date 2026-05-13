import { useEffect, useRef } from 'react';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';

window.$ = window.jQuery = $;

/**
 * Hook that wires a server-side Yajra DataTable to a <table ref>.
 *
 * @param {object}   config
 * @param {string}   config.url        - Yajra data endpoint
 * @param {Array}    config.columns    - DataTables column definitions
 * @param {object}   [config.order]    - default order, e.g. [[0,'desc']]
 * @param {object}   [config.extra]    - extra ajax data
 * @param {string}   [config.language] - DataTables language strings
 */
export default function useDataTable({
    url,
    columns,
    order = [[0, 'desc']],
    extra = {},
    language = {},
    pageLength = 10,
    reloadKey = 0,
}) {
    const tableRef = useRef(null);
    const dtRef = useRef(null);

    useEffect(() => {
        if (!tableRef.current) return undefined;
        const $table = $(tableRef.current);

        dtRef.current = $table.DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            order,
            pageLength,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: 'Loading…',
                emptyTable: 'No data',
                ...language,
            },
            ajax: {
                url,
                type: 'GET',
                data: (d) => ({ ...d, ...extra }),
            },
            columns,
            drawCallback: function () {
                $('.pagination').addClass('pagination-sm');
            },
        });

        return () => {
            try { dtRef.current?.destroy(); } catch (_e) { /* noop */ }
            $table.empty();
            dtRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [url, reloadKey]);

    const reload = () => {
        try { dtRef.current?.ajax.reload(null, false); } catch (_e) { /* noop */ }
    };

    return { tableRef, reload };
}
