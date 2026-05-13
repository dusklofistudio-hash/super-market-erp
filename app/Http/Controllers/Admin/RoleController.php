<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Roles/Index');
    }

    public function data(): JsonResponse
    {
        $query = Role::query()->withCount('permissions')->select('roles.*');

        return DataTables::eloquent($query)
            ->addColumn('action', fn (Role $r) => $this->actionCell(
                route('admin.roles.edit', $r),
                $r->is_locked ? null : route('admin.roles.destroy', $r),
            ))
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Form', [
            'role' => null,
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get(['id', 'module', 'name', 'slug']),
            'selected_permissions' => [],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $role = Role::create($data);
        $role->syncPermissions($request->input('permissions', []));
        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.roles')]));

        return redirect()->route('admin.roles.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.roles')]));
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('Roles/Form', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get(['id', 'module', 'name', 'slug']),
            'selected_permissions' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        if ($role->is_locked) {
            unset($data['slug']);
        } else {
            $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        }
        $role->update($data);
        $role->syncPermissions($request->input('permissions', []));
        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.roles')]));

        return redirect()->route('admin.roles.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.roles')]));
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_locked, 403);
        $role->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.roles')]));

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.roles')]));
    }
}
