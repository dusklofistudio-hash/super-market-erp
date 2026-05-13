<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class TranslationController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Translations/Index', [
            'languages' => Language::active()->get(['code', 'name']),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = Translation::query()->select('translations.*');

        return DataTables::eloquent($query)
            ->addColumn('action', fn (Translation $t) => $this->actionCell(
                route('admin.translations.edit', $t),
                route('admin.translations.destroy', $t),
            ))
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Translations/Form', [
            'translation' => null,
            'languages' => Language::active()->get(['code', 'name']),
        ]);
    }

    public function store(Request $request, TranslationService $svc): RedirectResponse
    {
        $data = $this->validateRow($request, null);
        Translation::create($data);
        $svc->bust($data['language_code']);

        return redirect()->route('admin.translations.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.translations')]));
    }

    public function edit(Translation $translation): Response
    {
        return Inertia::render('Translations/Form', [
            'translation' => $translation,
            'languages' => Language::active()->get(['code', 'name']),
        ]);
    }

    public function update(Request $request, Translation $translation, TranslationService $svc): RedirectResponse
    {
        $data = $this->validateRow($request, $translation->id);
        $translation->update($data);
        $svc->bust($data['language_code']);
        if ($data['language_code'] !== $translation->getOriginal('language_code')) {
            $svc->bust($translation->getOriginal('language_code'));
        }

        return redirect()->route('admin.translations.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.translations')]));
    }

    public function destroy(Translation $translation, TranslationService $svc): RedirectResponse
    {
        $code = $translation->language_code;
        $translation->delete();
        $svc->bust($code);

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.translations')]));
    }

    protected function validateRow(Request $request, ?int $ignoreId): array
    {
        return $request->validate([
            'language_code' => ['required', 'string', 'exists:languages,code'],
            'group' => ['required', 'string', 'max:64'],
            'key' => ['required', 'string', 'max:191', Rule::unique('translations', 'key')
                ->where(fn ($q) => $q->where('language_code', $request->language_code)->where('group', $request->group))
                ->ignore($ignoreId)],
            'value' => ['required', 'string'],
        ]);
    }
}
