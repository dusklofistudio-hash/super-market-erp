{{--
    Master admin layout. This is the Inertia "root view" for every authenticated
    admin page. The React app mounts inside <div id="app"> via @inertia, while
    the Blade chrome (head/header/sidebar/scripts) wraps it.

    Set as the root view in App\Http\Middleware\HandleInertiaRequests::rootView().
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('admin.layouts.head')
</head>
<body>
    <div class="smk-shell">
        @include('admin.layouts.left_sidebar')

        <div class="smk-main">
            @include('admin.layouts.header')

            <main class="smk-content">
                @inertia
            </main>
        </div>
    </div>

    @include('admin.layouts.scripts')
</body>
</html>
