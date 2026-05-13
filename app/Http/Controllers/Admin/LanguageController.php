<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Models\Language;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class LanguageController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Languages/Index');
    }

    public function data(): JsonResponse
    {
        $query = Language::query()->select('languages.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (Language $l) => $this->statusBadge((bool) $l->is_active))
            ->addColumn('is_default_badge', fn (Language $l) => $l->is_default
                ? '<span class="badge bg-primary">'.e(__('messages.fields.default')).'</span>'
                : '')
            ->addColumn('action', fn (Language $l) => $this->actionCell(
                route('admin.languages.edit', $l),
                $l->is_default ? null : route('admin.languages.destroy', $l),
            ))
            ->rawColumns(['status', 'is_default_badge', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Languages/Form', ['language' => null]);
    }

    public function store(LanguageRequest $request, TranslationService $svc): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['is_default'])) {
            Language::query()->update(['is_default' => false]);
        }
        Language::create($data);
        $svc->bustAll();

        return redirect()->route('admin.languages.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.languages')]));
    }

    public function edit(Language $language): Response
    {
        return Inertia::render('Languages/Form', ['language' => $language]);
    }

    public function update(LanguageRequest $request, Language $language, TranslationService $svc): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['is_default'])) {
            Language::query()->where('id', '!=', $language->id)->update(['is_default' => false]);
        }
        $language->update($data);
        $svc->bustAll();

        return redirect()->route('admin.languages.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.languages')]));
    }

    public function destroy(Language $language, TranslationService $svc): RedirectResponse
    {
        abort_if($language->is_default, 403, 'Default language cannot be deleted.');
        $language->delete();
        $svc->bustAll();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.languages')]));
    }
}
