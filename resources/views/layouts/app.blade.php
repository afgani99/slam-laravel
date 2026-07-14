<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a0a0a">

        <title>{{ config('app.name', 'SLAM') }} - {{ trim($__env->yieldContent('title', 'SLA Monitoring')) }}</title>

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..200&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0d0d0d] text-neutral-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen w-full bg-[#0d0d0d]">
            @include('layouts.navigation')

            <div class="flex min-h-screen min-w-0 flex-1 flex-col md:pl-[280px]">
                @isset($header)
                    <header class="sticky top-0 z-30 border-b border-white/5 backdrop-blur">
                        <div class="flex items-center justify-between gap-4 px-6 py-3 sm:py-2">
                            <div class="flex items-center gap-3">
                            <button type="button" @click="sidebarOpen = true" class="md:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#262626] text-neutral-400 transition hover:bg-[#2f2f2f] hover:text-white">
                                <span class="material-symbols-outlined text-[20px]">menu</span>
                            </button>
                            {{ $header }}
                        </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('locale.update') }}" class="m-0">
                                    @csrf
                                    @if(app()->getLocale() === 'en')
                                        <input type="hidden" name="locale" value="id">
                                        <button type="submit" class="slam-header-btn text-[16px] font-medium leading-none" title="Ubah ke Bahasa Indonesia">
                                            ID
                                        </button>
                                    @else
                                        <input type="hidden" name="locale" value="en">
                                        <button type="submit" class="slam-header-btn text-[16px] font-medium leading-none" title="Switch to English">
                                            EN
                                        </button>
                                    @endif
                                </form>

                                <x-dropdown align="right" width="56">
                                    <x-slot name="trigger">
                                        <button class="slam-header-btn">
                                            <span class="material-symbols-outlined text-[22px]">grid_view</span>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="px-4 py-3 border-b border-white/5">
                                            <div class="flex items-center gap-3">
                                                <div class="h-9 w-9 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400 font-semibold text-sm">
                                                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'User' }}</p>
                                                    <p class="text-xs text-neutral-500">{{ Auth::user()->email ?? 'user@example.com' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <x-dropdown-link :href="route('profile.edit')">
                                            <div class="flex items-center gap-2">
                                                <span class="material-symbols-outlined text-sm">settings</span>
                                                {{ __('header.profile_settings') }}
                                            </div>
                                        </x-dropdown-link>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-400 hover:text-red-300">
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-sm">logout</span>
                                                    {{ __('header.logout') }}
                                                </div>
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>
                    </header>
                @endisset

                <main class="min-w-0 flex-1 overflow-y-auto px-6 py-4">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-4 opacity-0"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl border border-green-500/20 bg-green-900/90 px-5 py-3 text-sm text-green-200 shadow-2xl backdrop-blur">
                <span class="material-symbols-outlined text-[18px] text-green-400">check_circle</span>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-2 text-green-400/60 hover:text-green-200">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-4 opacity-0"
                 class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl border border-red-500/20 bg-red-900/90 px-5 py-3 text-sm text-red-200 shadow-2xl backdrop-blur">
                <span class="material-symbols-outlined text-[18px] text-red-400">error</span>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-2 text-red-400/60 hover:text-red-200">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @stack('scripts')
    </body>
</html>
