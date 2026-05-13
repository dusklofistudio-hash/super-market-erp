{{--
    Head partial: meta, favicon, fonts, app CSS bundle.
    Bootstrap 5, flatpickr, Tom Select, SweetAlert2 and DataTables CSS are all
    bundled into resources/sass/app.scss and emitted by Vite.
--}}
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title inertia>{{ config('app.name', 'Super Market ERP') }}</title>

<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='12' fill='%232563eb'/><text x='50%' y='58%' text-anchor='middle' font-size='34' font-family='Arial' fill='white' font-weight='700'>S</text></svg>">

<link rel="preconnect" href="https://fonts.bunny.net">
<link
    href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700|noto-sans-khmer:400,500,600,700&display=swap"
    rel="stylesheet">

@routes
@viteReactRefresh
@vite(['resources/sass/app.scss', 'resources/js/app.jsx'])
@inertiaHead
