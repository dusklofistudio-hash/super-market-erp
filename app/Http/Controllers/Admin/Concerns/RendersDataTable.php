<?php

namespace App\Http\Controllers\Admin\Concerns;

/**
 * Shared helpers for building Yajra DataTables action / status cells.
 */
trait RendersDataTable
{
    protected function actionCell(?string $editRoute, ?string $deleteRoute): string
    {
        $html = '<div class="btn-group btn-group-sm">';
        if ($editRoute) {
            $html .= '<a href="'.$editRoute.'" class="btn btn-outline-primary smk-inertia">'.e(__('messages.edit')).'</a>';
        }
        if ($deleteRoute) {
            $html .= '<button type="button" class="btn btn-outline-danger" data-smk-delete="'.$deleteRoute.'">'.e(__('messages.delete')).'</button>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function statusBadge(bool $active): string
    {
        return $active
            ? '<span class="badge bg-success">'.e(__('messages.active')).'</span>'
            : '<span class="badge bg-secondary">'.e(__('messages.inactive')).'</span>';
    }
}
