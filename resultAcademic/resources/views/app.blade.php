<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        {{-- Early theme application script: force LIGHT theme consistently across browsers and domains --}}
        <script>
            (function() {
                try {
                    // Force light: remove any dark class immediately
                    document.documentElement.classList.remove('dark');

                    // Persist explicit light preference for SSR and future loads (across domains)
                    try { localStorage.setItem('appearance', 'light'); } catch (e) {}
                    // Cookie valid for 1 year, SameSite=Lax
                    (function(){
                        var maxAge = 365 * 24 * 60 * 60;
                        var isSecure = window.location && window.location.protocol === 'https:';
                        document.cookie = 'appearance=light;path=/;max-age=' + maxAge + ';SameSite=Lax' + (isSecure ? ';Secure' : '');
                    })();
                } catch (e) { /* noop */ }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
