<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Users/Index');
    }

    public function data(): JsonResponse
    {
        $query = User::query()->with(['roles:id,name', 'defaultBranch:id,name_en'])->select('users.*');

        return DataTables::eloquent($query)
            ->addColumn('roles_list', fn (User $u) => $u->roles->pluck('name')->join(', '))
            ->addColumn('default_branch', fn (User $u) => $u->defaultBranch?->name_en)
            ->addColumn('status', fn (User $u) => $this->statusBadge((bool) $u->is_active))
            ->addColumn('action', fn (User $u) => $this->actionCell(
                route('admin.users.edit', $u),
                $u->is_super_admin ? null : route('admin.users.destroy', $u),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form', [
            'user' => null,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->syncRoles($request->input('roles', []));
        $user->syncBranches($request->input('branches', []));

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.users')]));

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.users')]));
    }

    public function edit(User $user): Response
    {
        $user->load('roles:id', 'branches:id');

        return Inertia::render('Users/Form', [
            'user' => array_merge($user->toArray(), [
                'roles' => $user->roles->pluck('id')->all(),
                'branches' => $user->branches->pluck('id')->all(),
            ]),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        $user->syncRoles($request->input('roles', []));
        $user->syncBranches($request->input('branches', []));

        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.users')]));

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.users')]));
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is_super_admin, 403);
        $user->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.users')]));

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.users')]));
    }
}
