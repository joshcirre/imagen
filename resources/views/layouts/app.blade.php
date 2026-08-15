<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>Imagen</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>

    <body class="h-dvh overflow-hidden bg-white dark:bg-zinc-800">
        {{ $slot }}

        @persist('toast')
            <flux:toast />
        @endpersist

        @fluxScripts()
    </body>
</html>
