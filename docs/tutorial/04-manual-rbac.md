# Chapter 04 — Manual RBAC (no third-party package)

The user explicitly asked: build roles and permissions **without** a
package like `spatie/laravel-permission`. This chapter shows the
minimum viable RBAC layer that supports:

- Many-to-many users ↔ roles ↔ permissions.
- Per-user branch scoping.
- Route middleware (`Route::middleware('permission:users.view')`).
- Blade `@can('…')` gating.
- React `usePermission('…')` hook.
- A super-admin bypass.

The whole system is ~120 lines of PHP plus three tiny tables. Everything
lives in:

```text
app/Models/Role.php
app/Models/Permission.php
app/Models/Concerns/HasRolesAndPermissions.php  (trait used by User)
app/Http/Middleware/EnsurePermission.php
app/Providers/AuthServiceProvider.php (or AppServiceProvider boot())
resources/js/lib/PermissionProvider.jsx
```

## 1. The three pivot tables

These already exist from Chapter 02:

- `role_permission(role_id, permission_id)`
- `user_role(user_id, role_id)`
- `user_branch(user_id, branch_id)` — restricts which branches a user
  can transact against.

No timestamps on pivots; sync operations replace rows wholesale.

## 2. The `Role` and `Permission` models

```php
// app/Models/Role.php
class Role extends Model {
    protected $fillable = ['name', 'slug', 'description'];

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_role');
    }
}

// app/Models/Permission.php
class Permission extends Model {
    protected $fillable = ['name', 'slug', 'group'];

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
```

Notice each table has both `name` (human label) and `slug` (machine key
like `products.create`). The slug is what middleware and `@can` check.

## 3. The `HasRolesAndPermissions` trait

The whole user-facing RBAC API is on `App\Models\User` via a trait. The
real file lives at `app/Models/Concerns/HasRolesAndPermissions.php`.
Key methods:

```php
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class, 'user_role');
}

public function branches(): BelongsToMany
{
    return $this->belongsToMany(Branch::class, 'user_branch');
}

public function isSuperAdmin(): bool
{
    return (bool) ($this->is_super_admin ?? false);
}

public function getPermissionSlugs(): Collection
{
    return $this->roles
        ->loadMissing('permissions')
        ->flatMap(fn (Role $r) => $r->permissions->pluck('slug'))
        ->unique()
        ->values();
}

public function hasPermission(string $slug): bool
{
    if ($this->isSuperAdmin()) return true;
    return $this->getPermissionSlugs()->contains($slug);
}
```

Two design notes:

1. **Super-admin bypass** — a user row with `is_super_admin = true`
   short-circuits every permission check. We use this for the seeded
   `admin` account so newly added permissions never lock you out.
2. **No caching** — `getPermissionSlugs()` runs a query per call. For
   small permission sets this is fine; if you grow past ~50 perms,
   memoize on the request via `Cache::remember` keyed by user id.

## 4. The `permission:` route middleware

```php
// app/Http/Middleware/EnsurePermission.php
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        abort_unless($user, 403, 'Not authenticated');
        if (! $user->hasPermission($permission)) {
            abort(403, "Missing permission: $permission");
        }
        return $next($request);
    }
}
```

Register in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\EnsurePermission::class,
    ]);
})
```

Apply on routes:

```php
Route::middleware(['auth', 'permission:products.view'])
    ->get('/admin/products', [ProductController::class, 'index'])
    ->name('admin.products.index');
```

## 5. Wire the Blade `@can` directive

Laravel's `@can` directive calls `Gate::allows(...)`. We tell the Gate
to fall through to our `hasPermission()` method by defining a single
catch-all gate in `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    Gate::before(function (User $user, string $ability) {
        return $user->hasPermission($ability) ? true : null;
    });
}
```

`Gate::before` returning `null` means "I have no opinion, ask the next
gate." Returning `true` short-circuits as allowed. This single hook lets
**every** Laravel authorization API — `@can`, `$user->can()`,
`Gate::allows`, `authorize()` — work against our permission slugs.

## 6. Expose permissions to React via Inertia

In `app/Http/Middleware/HandleInertiaRequests.php`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => fn () => [
            'user' => $request->user()?->only(['id', 'name', 'username', 'avatar', 'locale']),
            'permissions' => $request->user()?->getPermissionSlugs()->all() ?? [],
            'is_super_admin' => $request->user()?->isSuperAdmin() ?? false,
        ],
        'flash' => fn () => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ],
    ]);
}
```

React side — `resources/js/lib/PermissionProvider.jsx`:

```jsx
import { createContext, useContext } from 'react';
import { usePage } from '@inertiajs/react';

const PermissionContext = createContext({ permissions: [], isSuperAdmin: false });

export function PermissionProvider({ children }) {
    const { auth } = usePage().props;
    return (
        <PermissionContext.Provider value={{
            permissions: auth?.permissions ?? [],
            isSuperAdmin: auth?.is_super_admin ?? false,
        }}>
            {children}
        </PermissionContext.Provider>
    );
}

export function usePermission(slug) {
    const ctx = useContext(PermissionContext);
    if (ctx.isSuperAdmin) return true;
    return ctx.permissions.includes(slug);
}

export function Can({ permission, children, fallback = null }) {
    return usePermission(permission) ? children : fallback;
}
```

Now in any page:

```jsx
<Can permission="products.create">
    <Link href={route('admin.products.create')} className="btn btn-primary">
        Add product
    </Link>
</Can>
```

## 7. Seed roles, permissions, and a super admin

A complete seeded permission set lives in
`database/seeders/PermissionSeeder.php`. The shape:

```php
$groups = [
    'Users'      => ['users.view', 'users.create', 'users.update', 'users.delete'],
    'Products'   => ['products.view', 'products.create', 'products.update', 'products.delete'],
    'Sales'      => ['sales.view', 'sales.create', 'pos.access'],
    // ...
];

foreach ($groups as $group => $slugs) {
    foreach ($slugs as $slug) {
        Permission::firstOrCreate(
            ['slug' => $slug],
            ['name' => Str::headline($slug), 'group' => $group],
        );
    }
}
```

Three seeded roles (see `RoleSeeder` + `RolePermissionSeeder`):

| Role            | Slug             | Permissions granted                                                  |
|-----------------|------------------|----------------------------------------------------------------------|
| Super Admin     | `super-admin`    | None directly (super-admin flag bypasses checks).                    |
| Branch Manager  | `branch-manager` | All `*.view` + `*.create` + `*.update`. No `users.*` / `roles.*`.    |
| Cashier         | `cashier`        | `pos.access`, `sales.view`, `sales.create`, `products.view`, `customers.*`. |

Seeded users (see `UserSeeder` + `UserRoleSeeder`):

| Username | Email                 | Roles            |
|----------|-----------------------|------------------|
| `admin`  | `admin@example.com`   | super-admin + is_super_admin flag |
| `manager`| `manager@example.com` | branch-manager   |
| `cashier`| `cashier@example.com` | cashier          |

All three share password `password` for development.

## 8. Sidebar gating

Recall Chapter 03 wrapped each sidebar link in `@can('…')`. With the
gate hook from step 5, those checks now resolve correctly. Walk through
what happens when the cashier loads `/admin`:

1. Login resolves, controller renders `Dashboard.jsx` with the admin
   chrome.
2. Sidebar Blade runs. Every `@can('users.view')` block returns false
   because the cashier role has no such permission.
3. The cashier's sidebar shows only Dashboard + POS + Sales + Customers
   + Products (read-only).
4. If the cashier types `/admin/users` directly, the `permission:`
   middleware returns 403 with `Missing permission: users.view`.

## Verify

```bash
php artisan migrate:fresh --seed

php artisan tinker --execute='
use App\Models\User;
$admin = User::where("username","admin")->first();
$cashier = User::where("username","cashier")->first();

echo "admin->isSuperAdmin: " . ($admin->isSuperAdmin() ? "true" : "false") . "\n";
echo "admin->hasPermission(users.view): " . ($admin->hasPermission("users.view") ? "true" : "false") . "\n";
echo "cashier->hasPermission(users.view): " . ($cashier->hasPermission("users.view") ? "true" : "false") . "\n";
echo "cashier->hasPermission(pos.access): " . ($cashier->hasPermission("pos.access") ? "true" : "false") . "\n";
'
```

Expected:

```text
admin->isSuperAdmin: true
admin->hasPermission(users.view): true
cashier->hasPermission(users.view): false
cashier->hasPermission(pos.access): true
```

Then in the browser:

```text
POST /login as cashier/password   → 302 to /admin
GET  /admin/users                 → 403 "Missing permission: users.view"
GET  /admin/pos/register          → 200 OK
```

If any of those four behaviors is wrong, fix RBAC before Chapter 05.
