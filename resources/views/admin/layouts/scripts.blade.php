{{--
    Scripts partial: Bootstrap 5 JS bundle ships inside resources/js/app.jsx via
    Vite. Here we register the lightweight, NON-React glue that runs in the
    Blade chrome — sidebar toggle, language switcher, and the global PHPFlasher
    auto-render hook.
--}}
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('smkSidebarToggle');
            const sidebar = document.getElementById('smkSidebar');
            if (toggle && sidebar) {
                toggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });
            }

            // Language switcher posts to /admin/locale and updates UI strings
            // in-place. React side listens for 'smk:locale-changed'.
            document.querySelectorAll('.smk-lang-item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const locale = this.getAttribute('data-locale');
                    window.smkSwitchLocale && window.smkSwitchLocale(locale);
                });
            });

            window.smkSwitchLocale = async function (locale) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                try {
                    const res = await fetch('/admin/locale', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ locale }),
                    });
                    if (!res.ok) throw new Error('Locale switch failed');
                    const data = await res.json();
                    document.documentElement.setAttribute('lang', locale);
                    const label = document.getElementById('smkLangLabel');
                    if (label) label.textContent = locale.toUpperCase();
                    const flag = document.querySelector('#smkLangBtn .lang-flag');
                    if (flag) flag.className = 'lang-flag ' + locale;
                    // Update every Blade-rendered i18n element in place. The
                    // server returns the flattened translations bag, so any key
                    // (menu.*, my_profile, logout, …) can be swapped without a
                    // page reload.
                    const bag = (data && data.translations) || {};
                    document.querySelectorAll('[data-i18n]').forEach(function (el) {
                        const key = el.getAttribute('data-i18n');
                        if (bag[key]) el.textContent = bag[key];
                    });
                    window.dispatchEvent(new CustomEvent('smk:locale-changed', { detail: { locale, translations: data.translations } }));
                } catch (e) {
                    console.error(e);
                }
            };
        });
    })();
</script>

@flasher_render
