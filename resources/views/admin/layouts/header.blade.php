{{--
    Header partial: top bar with mobile toggle, branch switcher placeholder,
    language switcher (Khmer/English, no refresh) and the auth user menu.
    The language switcher posts to /admin/locale via window.smkSwitchLocale.
--}}
@php
    $user = auth()->user();
    $current = app()->getLocale();
    $available = \App\Models\Language::active()->orderByDesc('is_default')->orderBy('name')->get();
@endphp

<header class="smk-header">
    <button id="smkSidebarToggle" type="button" class="btn btn-link d-lg-none p-0 me-3" aria-label="Toggle menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <div class="d-flex align-items-center flex-grow-1">
        <span class="text-muted small d-none d-md-inline">{{ config('app.name') }}</span>
    </div>

    <div class="dropdown me-2">
        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center" type="button"
                id="smkLangBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="lang-flag {{ $current }}"></span>
            <span id="smkLangLabel">{{ strtoupper($current) }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="smkLangBtn">
            @foreach ($available as $lang)
                <li>
                    <button type="button"
                            class="dropdown-item d-flex align-items-center smk-lang-item"
                            data-locale="{{ $lang->code }}">
                        <span class="lang-flag {{ $lang->code }}"></span>
                        {{ $lang->native_name ?: $lang->name }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="dropdown">
        <button class="btn btn-light btn-sm dropdown-toggle" type="button"
                id="smkUserBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <strong>{{ $user?->name ?? 'Guest' }}</strong>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="smkUserBtn">
            <li>
                <a class="dropdown-item" href="{{ route('admin.profile.edit') }}" data-i18n="my_profile">
                    {{ __('messages.my_profile') }}
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger" data-i18n="logout">
                        {{ __('messages.logout') }}
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>
