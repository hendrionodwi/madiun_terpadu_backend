<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head', ['title' => $title ?? __('portal.meta.public_portal')])
        @stack('head')
    </head>
    <body class="civic-public-portal min-h-screen bg-civic-canvas text-civic-ink antialiased">
        <x-public.header :portalSettings="$portalSettings ?? []" />

        <main>
            @yield('content')
        </main>

        <x-public.footer :portalSettings="$portalSettings ?? []" />
        @stack('scripts')
        @fluxScripts
    </body>
</html>
