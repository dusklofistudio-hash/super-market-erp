{{--
    Left sidebar partial. Menu items are permission-aware via the manual RBAC
    system (auth()->user()?->hasPermission()). The active state is computed from
    the current request route name.
--}}
@php
    $user = auth()->user();
    $can = fn ($perm) => $user && $user->hasPermission($perm);
    $is = fn ($prefix) => request()->routeIs($prefix);
    // Each menu entry carries its dotted i18n key so we can both render the
    // server-side translation AND emit `data-i18n` so the no-refresh language
    // switcher in scripts.blade.php can update the text in place.
    $sections = [
        [
            'key' => 'menu.main',
            'items' => [
                ['key' => 'menu.dashboard', 'route' => 'admin.dashboard', 'icon' => 'home', 'permission' => null],
            ],
        ],
        [
            'key' => 'menu.catalog',
            'items' => [
                ['key' => 'menu.categories', 'route' => 'admin.categories.index', 'icon' => 'tag', 'permission' => 'categories.view'],
                ['key' => 'menu.brands', 'route' => 'admin.brands.index', 'icon' => 'award', 'permission' => 'brands.view'],
                ['key' => 'menu.units', 'route' => 'admin.units.index', 'icon' => 'package', 'permission' => 'units.view'],
                ['key' => 'menu.tax_rates', 'route' => 'admin.tax-rates.index', 'icon' => 'percent', 'permission' => 'tax_rates.view'],
                ['key' => 'menu.products', 'route' => 'admin.products.index', 'icon' => 'box', 'permission' => 'products.view'],
            ],
        ],
        [
            'key' => 'menu.parties',
            'items' => [
                ['key' => 'menu.suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'truck', 'permission' => 'suppliers.view'],
                ['key' => 'menu.customers', 'route' => 'admin.customers.index', 'icon' => 'users', 'permission' => 'customers.view'],
                ['key' => 'menu.customer_groups', 'route' => 'admin.customer-groups.index', 'icon' => 'user-check', 'permission' => 'customer_groups.view'],
            ],
        ],
        [
            'key' => 'menu.operations',
            'items' => [
                ['key' => 'menu.purchases', 'route' => 'admin.purchases.index', 'icon' => 'truck', 'permission' => 'purchases.view'],
                ['key' => 'menu.pos', 'route' => 'admin.pos.register', 'icon' => 'shopping-cart', 'permission' => 'pos.use'],
                ['key' => 'menu.pos_registers', 'route' => 'admin.pos-registers.index', 'icon' => 'monitor', 'permission' => 'pos_registers.view'],
                ['key' => 'menu.pos_sessions', 'route' => 'admin.pos-sessions.index', 'icon' => 'clock', 'permission' => 'pos_sessions.view'],
                ['key' => 'menu.sales', 'route' => 'admin.sales.index', 'icon' => 'shopping-bag', 'permission' => 'sales.view'],
                ['key' => 'menu.stock_adjustments', 'route' => 'admin.stock-adjustments.index', 'icon' => 'sliders', 'permission' => 'stock_adjustments.view'],
                ['key' => 'menu.stock_transfers', 'route' => 'admin.stock-transfers.index', 'icon' => 'repeat', 'permission' => 'stock_transfers.view'],
                ['key' => 'menu.expense_categories', 'route' => 'admin.expense-categories.index', 'icon' => 'folder', 'permission' => 'expense_categories.view'],
                ['key' => 'menu.expenses', 'route' => 'admin.expenses.index', 'icon' => 'dollar-sign', 'permission' => 'expenses.view'],
            ],
        ],
        [
            'key' => 'menu.reports',
            'items' => [
                ['key' => 'menu.sales_summary', 'route' => 'admin.reports.sales-summary', 'icon' => 'bar-chart', 'permission' => 'reports.view'],
                ['key' => 'menu.stock_by_branch', 'route' => 'admin.reports.stock-by-branch', 'icon' => 'database', 'permission' => 'reports.view'],
                ['key' => 'menu.profit', 'route' => 'admin.reports.profit', 'icon' => 'trending-up', 'permission' => 'reports.view'],
                ['key' => 'menu.expense_report', 'route' => 'admin.reports.expenses', 'icon' => 'pie-chart', 'permission' => 'reports.view'],
            ],
        ],
        [
            'key' => 'menu.administration',
            'items' => [
                ['key' => 'menu.branches', 'route' => 'admin.branches.index', 'icon' => 'git-branch', 'permission' => 'branches.view'],
                ['key' => 'menu.users', 'route' => 'admin.users.index', 'icon' => 'user', 'permission' => 'users.view'],
                ['key' => 'menu.roles', 'route' => 'admin.roles.index', 'icon' => 'shield', 'permission' => 'roles.view'],
                ['key' => 'menu.permissions', 'route' => 'admin.permissions.index', 'icon' => 'key', 'permission' => 'permissions.view'],
                ['key' => 'menu.languages', 'route' => 'admin.languages.index', 'icon' => 'globe', 'permission' => 'languages.view'],
                ['key' => 'menu.translations', 'route' => 'admin.translations.index', 'icon' => 'message-square', 'permission' => 'translations.view'],
                ['key' => 'menu.activity_logs', 'route' => 'admin.activity-logs.index', 'icon' => 'list', 'permission' => 'activity_logs.view'],
                ['key' => 'menu.settings', 'route' => 'admin.settings.edit', 'icon' => 'settings', 'permission' => 'settings.view'],
            ],
        ],
    ];
@endphp

<aside class="smk-sidebar" id="smkSidebar">
    <a href="{{ route('admin.dashboard') }}" class="smk-brand">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;background:#2563eb;color:#fff;font-weight:700;margin-right:0.5rem;">S</span>
        Super Market ERP
    </a>

    <ul class="smk-menu">
        @foreach ($sections as $section)
            @php
                $visibleItems = array_filter($section['items'], fn ($it) => empty($it['permission']) || $can($it['permission']));
            @endphp
            @if (count($visibleItems))
                <li class="smk-section" data-i18n="{{ $section['key'] }}">{{ __('messages.'.$section['key']) }}</li>
                @foreach ($visibleItems as $item)
                    <li>
                        <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                           class="{{ $is($item['route']) ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @switch($item['icon'])
                                    @case('home')<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>@break
                                    @case('tag')<path d="M20.59 13.41L13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line>@break
                                    @case('award')<circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>@break
                                    @case('package')<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>@break
                                    @case('percent')<line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle>@break
                                    @case('box')<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>@break
                                    @case('truck')<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>@break
                                    @case('users')<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>@break
                                    @case('user-check')<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline>@break
                                    @case('git-branch')<line x1="6" y1="3" x2="6" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path>@break
                                    @case('user')<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>@break
                                    @case('shield')<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>@break
                                    @case('key')<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>@break
                                    @case('globe')<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>@break
                                    @case('message-square')<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>@break
                                    @case('settings')<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>@break
                                    @case('shopping-cart')<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>@break
                                    @case('shopping-bag')<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path>@break
                                    @case('monitor')<rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line>@break
                                    @case('clock')<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>@break
                                    @case('sliders')<line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>@break
                                    @case('repeat')<polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path>@break
                                    @case('folder')<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>@break
                                    @case('dollar-sign')<line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>@break
                                    @case('bar-chart')<line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line>@break
                                    @case('database')<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>@break
                                    @case('trending-up')<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>@break
                                    @case('pie-chart')<path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path>@break
                                    @case('list')<line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line>@break
                                    @default <circle cx="12" cy="12" r="9"></circle>
                                @endswitch
                            </svg>
                            <span data-i18n="{{ $item['key'] }}">{{ __('messages.'.$item['key']) }}</span>
                        </a>
                    </li>
                @endforeach
            @endif
        @endforeach
    </ul>
</aside>
