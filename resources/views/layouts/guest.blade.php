<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a0a0a">

        <title>{{ config('app.name', 'SLAM') }} - SLA Monitoring</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..200&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-neutral-950 text-neutral-100">
        <div class="flex min-h-screen flex-col items-center justify-center bg-neutral-950 px-4">
            <div class="w-full max-w-sm">
                <!-- Logo -->
                <div class="mb-10 flex flex-col items-center justify-center text-center">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#f27e5d] to-[#a83c22] shadow-md">
                            <span class="material-symbols-outlined text-[24px] text-white">hub</span>
                        </div>
                        <h1 class="text-3xl font-black tracking-tight text-white leading-none">SLAM</h1>
                    </div>
                    <p class="text-sm font-medium text-neutral-400 tracking-wide opacity-90">SLA Monitoring System</p>
                </div>

                <!-- Card -->
                <div class="rounded-xl border border-neutral-800 bg-neutral-900 p-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
