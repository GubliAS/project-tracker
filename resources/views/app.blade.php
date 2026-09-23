<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script>
        (function () {
            try {
                var isDark = localStorage.getItem('pm-theme') === 'dark';
                var theme = isDark ? 'dark' : 'light';
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-header-styles', theme);
                document.documentElement.setAttribute('data-menu-styles', theme);
            } catch (e) {}
        })();
    </script>
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
