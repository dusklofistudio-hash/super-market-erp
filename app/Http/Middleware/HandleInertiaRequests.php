<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The Blade shell that wraps every Inertia page. Defined in
     * resources/views/admin/layouts/admin_layout.blade.php.
     */
    protected $rootView = 'admin.layouts.admin_layout';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $locale = app()->getLocale();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_super_admin' => $user->is_super_admin,
                    'default_branch_id' => $user->default_branch_id,
                ] : null,
                'permissions' => fn () => $user?->getPermissionSlugs()->all() ?? [],
                'roles' => fn () => $user?->roles->pluck('slug')->all() ?? [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => $locale,
            'available_locales' => fn () => Language::active()->orderBy('name')->get()
                ->map(fn ($l) => [
                    'code' => $l->code,
                    'name' => $l->name,
                    'native_name' => $l->native_name,
                    'is_default' => $l->is_default,
                ])->all(),
            'translations' => fn () => [
                $locale => app(TranslationService::class)->all($locale),
            ],
            'app' => [
                'name' => config('app.name'),
            ],
        ]);
    }
}
