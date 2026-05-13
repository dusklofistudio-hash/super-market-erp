{{--
    Left sidebar partial. Menu items are permission-aware via the manual RBAC
    system (auth()->user()?->hasPermission()). The active state is computed from
    the current request route name.
--}}
@php
    $user = auth()->user();
    $can = fn ($perm) => $user && $user->hasPermission($perm);
    $is = fn ($prefix) => request()->routeIs($prefix);
    $sections = [
        [
            'label' => __('messages.menu.main'),
            'items' => [
                ['label' => __('messages.menu.dashboard'), 'route' => 'admin.dashboard', 'icon' => 'home', 'permission' => null],
            ],
        ],
        [
            'label' => __('messages.menu.catalog'),
            'items' => [
                ['label' => __('messages.menu.categories'), 'route' => 'admin.categories.index', 'icon' => 'tag', 'permission' => 'categories.view'],
                ['label' => __('messages.menu.brands'), 'route' => 'admin.brands.index', 'icon' => 'award', 'permission' => 'brands.view'],
                ['label' => __('messages.menu.units'), 'route' => 'admin.units.index', 'icon' => 'package', 'permission' => 'units.view'],
                ['label' => __('messages.menu.tax_rates'), 'route' => 'admin.tax-rates.index', 'icon' => 'percent', 'permission' => 'tax_rates.view'],
                ['label' => __('messages.menu.products'), 'route' => 'admin.products.index', 'icon' => 'box', 'permission' => 'products.view'],
            ],
        ],
        [
            'label' => __('messages.menu.parties'),
            'items' => [
                ['label' => __('messages.menu.suppliers'), 'route' => 'admin.suppliers.index', 'icon' => 'truck', 'permission' => 'suppliers.view'],
                ['label' => __('messages.menu.customers'), 'route' => 'admin.customers.index', 'icon' => 'users', 'permission' => 'customers.view'],
                ['label' => __('messages.menu.customer_groups'), 'route' => 'admin.customer-groups.index', 'icon' => 'user-check', 'permission' => 'customer_groups.view'],
            ],
        ],
        [
            'label' => __('messages.menu.administration'),
            'items' => [
                ['label' => __('messages.menu.branches'), 'route' => 'admin.branches.index', 'icon' => 'git-branch', 'permission' => 'branches.view'],
                ['label' => __('messages.menu.users'), 'route' => 'admin.users.index', 'icon' => 'user', 'permission' => 'users.view'],
                ['label' => __('messages.menu.roles'), 'route' => 'admin.roles.index', 'icon' => 'shield', 'permission' => 'roles.view'],
                ['label' => __('messages.menu.permissions'), 'route' => 'admin.permissions.index', 'icon' => 'key', 'permission' => 'permissions.view'],
                ['label' => __('messages.menu.languages'), 'route' => 'admin.languages.index', 'icon' => 'globe', 'permission' => 'languages.view'],
                ['label' => __('messages.menu.translations'), 'route' => 'admin.translations.index', 'icon' => 'message-square', 'permission' => 'translations.view'],
                ['label' => __('messages.menu.settings'), 'route' => 'admin.settings.edit', 'icon' => 'settings', 'permission' => 'settings.view'],
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
                <li class="smk-section">{{ $section['label'] }}</li>
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
                                @endswitch
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endif
        @endforeach
    </ul>
</aside>
