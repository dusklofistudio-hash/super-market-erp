# Chapter 07 — Frontend libraries (SweetAlert2, PHPFlasher, flatpickr, Tom Select)

The user specified four UX libraries by name. This chapter integrates
all four in a single `app.jsx` entry point so every page gets them for
free.

| Library      | Purpose                          | Where it shows up                                 |
|--------------|----------------------------------|---------------------------------------------------|
| SweetAlert2  | Modal confirms (delete, void)    | All "Delete" buttons on list tables.              |
| PHPFlasher   | Toast notifications              | After every successful create/update/delete.      |
| flatpickr    | Date/time picker                 | Any `<input data-flatpickr>` element.             |
| Tom Select   | Searchable select with chips     | Any `<select data-tom-select>` element.           |

## 1. The CSS entry point

`resources/sass/app.scss`:

```scss
@import "bootstrap/scss/bootstrap";
@import "bootstrap-icons/font/bootstrap-icons.css";

@import "sweetalert2/src/sweetalert2.scss";
@import "flatpickr/dist/flatpickr.css";
@import "tom-select/dist/scss/tom-select.bootstrap5.scss";
@import "datatables.net-bs5/css/dataTables.bootstrap5.css";
@import "datatables.net-responsive-bs5/css/responsive.bootstrap5.css";

body { font-family: var(--bs-font-sans-serif); background: #f6f8fb; }
```

Importing each library's stylesheet from `node_modules` lets Vite tree-
shake unused rules and produces a single hashed `app-<hash>.css` file.

## 2. The JS entry point

`resources/js/app.jsx`:

```jsx
import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

import { I18nProvider } from './lib/I18nProvider';
import { PermissionProvider } from './lib/PermissionProvider';
import './lib/flasher';        // PHPFlasher toast bridge
import './Components/RowActions'; // side-effect: register delete handlers

createInertiaApp({
    resolve: (name) =>
        import.meta.glob('./Pages/**/*.jsx', { eager: true })[`./Pages/${name}.jsx`],
    setup({ el, App, props }) {
        createRoot(el).render(
            <I18nProvider>
                <PermissionProvider>
                    <App {...props} />
                </PermissionProvider>
            </I18nProvider>
        );
    },
});
```

The two side-effect imports (`flasher`, `RowActions`) wire global jQuery
handlers and toast bridges that we need everywhere.

## 3. SweetAlert2 — localized delete confirms

The shared handler lives in `resources/js/Components/RowActions.jsx`:

```jsx
import $ from 'jquery';
import Swal from 'sweetalert2';

function strings() {
    // Pull current translations off window if i18n has hydrated them.
    const dict = window.__SMK_TRANSLATIONS__ ?? {};
    return {
        title:   dict['messages.confirm.delete_title']   ?? 'Delete confirmation',
        text:    dict['messages.confirm.delete_text']    ?? 'This cannot be undone.',
        confirm: dict['messages.confirm.delete_yes']     ?? 'Yes, delete',
        cancel:  dict['messages.confirm.delete_cancel']  ?? 'Cancel',
    };
}

window.smkBindRowActions = function () {
    $(document).off('click', '[data-smk-delete]')
               .on('click', '[data-smk-delete]', async function () {
        const url = $(this).data('smk-delete');
        const s = strings();
        const result = await Swal.fire({
            icon: 'warning',
            title: s.title,
            text:  s.text,
            showCancelButton: true,
            confirmButtonText: s.confirm,
            cancelButtonText:  s.cancel,
            confirmButtonColor: '#dc3545',
        });
        if (!result.isConfirmed) return;

        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: new URLSearchParams({ _method: 'DELETE' }),
        });
        if (res.ok) {
            // Reload any DataTable on the page without re-mounting React.
            $('.dataTable').each(function () {
                $(this).DataTable().ajax.reload(null, false);
            });
            // Show success toast via the same flasher bridge.
            window.dispatchEvent(new CustomEvent('smk:flash', {
                detail: { type: 'success', message: 'Deleted.' },
            }));
        }
    });
};

window.smkBindRowActions();
window.addEventListener('smk:locale-changed', (e) => {
    window.__SMK_TRANSLATIONS__ = e.detail.translations;
});
```

Three important properties:

1. **Idempotent re-binding** — `off().on()` ensures we never stack
   handlers if React remounts.
2. **i18n-aware strings** — the function reads the current dictionary
   from `window.__SMK_TRANSLATIONS__`, which the i18n switcher updates
   via the `smk:locale-changed` event. The dialog text follows the
   active locale automatically (Chapter 05).
3. **DataTable refresh** — instead of doing a full Inertia visit, the
   handler tells any DataTables on the page to re-fetch their rows.

The `kh` translation file adds the dialog keys:

```php
'confirm' => [
    'delete_title'  => 'បញ្ជាក់ការលុប',
    'delete_text'   => 'សកម្មភាពនេះមិនអាចត្រឡប់ក្រោយវិញបានទេ។',
    'delete_yes'    => 'យល់ព្រម លុប',
    'delete_cancel' => 'បោះបង់',
],
```

## 4. PHPFlasher — success toasts

PHPFlasher is the Laravel package that emits toast notifications based
on the controller's redirect data. Install it once:

```bash
composer require php-flasher/flasher-sweetalert-laravel
```

In `scripts.blade.php`:

```blade
@flasher_render
```

That directive emits the JS that reads `session('_flasher_*')` and
calls `Swal.fire({ toast: true, … })`.

In any controller after a successful write:

```php
public function store(StoreProductRequest $request)
{
    Product::create($request->validated());
    return redirect()->route('admin.products.index')
        ->with('success', __('messages.flash.created'));
}
```

The redirect carries the flash message; PHPFlasher renders it as a
toast at the top-right.

To trigger the **same** toast from JavaScript (e.g. after a SweetAlert
delete that does not redirect), the `flasher` bridge in `app.jsx`:

```jsx
// resources/js/lib/flasher.js
import Swal from 'sweetalert2';

window.addEventListener('smk:flash', (e) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: e.detail.type ?? 'success',
        title: e.detail.message,
        showConfirmButton: false,
        timer: 2500,
    });
});
```

## 5. flatpickr — date/time inputs

Make every `<input data-flatpickr>` a flatpickr field automatically.
Create `resources/js/lib/flatpickr-init.js`:

```js
import flatpickr from 'flatpickr';

function initAll(root = document) {
    root.querySelectorAll('input[data-flatpickr]').forEach((el) => {
        if (el._flatpickr) return;     // already initialized
        const cfg = el.dataset.flatpickr ? JSON.parse(el.dataset.flatpickr) : {};
        flatpickr(el, cfg);
    });
}

// Initial mount + each Inertia navigation
document.addEventListener('DOMContentLoaded', () => initAll());
document.addEventListener('inertia:navigate', () => requestAnimationFrame(initAll));
```

Import once in `app.jsx`:

```jsx
import './lib/flatpickr-init';
```

Page usage:

```jsx
<input type="text" name="purchased_at"
       data-flatpickr='{"enableTime":true,"dateFormat":"Y-m-d H:i"}'
       className="form-control" />
```

## 6. Tom Select — searchable selects

Same pattern as flatpickr. `resources/js/lib/tom-init.js`:

```js
import TomSelect from 'tom-select';

function initAll(root = document) {
    root.querySelectorAll('select[data-tom-select]').forEach((el) => {
        if (el.tomselect) return;
        const cfg = el.dataset.tomSelect ? JSON.parse(el.dataset.tomSelect) : {};
        new TomSelect(el, { create: false, ...cfg });
    });
}
document.addEventListener('DOMContentLoaded', () => initAll());
document.addEventListener('inertia:navigate', () => requestAnimationFrame(initAll));
```

In a form:

```jsx
<select name="category_id" data-tom-select className="form-select">
    <option value="">-- Choose --</option>
    {categories.map((c) => (
        <option key={c.id} value={c.id}>{c.name}</option>
    ))}
</select>
```

## 7. Verify

After the page loads, run in DevTools console:

```js
typeof window.smkBindRowActions   // 'function'
typeof Swal                       // 'function'
$('input[data-flatpickr]').length // > 0 means flatpickr inputs detected
$('select[data-tom-select]').length
```

UI checks:

- Click any Delete button → SweetAlert2 modal opens (NOT browser native
  `confirm()`).
- After saving a form → a success toast slides in from top-right.
- Click any date input → flatpickr calendar opens.
- Click any decorated select → it becomes a searchable picker.

If a date input renders as a plain text box, the `inertia:navigate`
listener never fired — confirm `flatpickr-init.js` is imported in
`app.jsx`.
