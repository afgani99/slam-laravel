<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">person</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Profile</h2>
            <p class="mt-0.5 hidden text-sm text-neutral-500 sm:block">Manage your account settings.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-2">
            <section class="slam-panel p-5">
                @include('profile.partials.update-profile-information-form')
            </section>
            <section class="slam-panel p-5">
                @include('profile.partials.update-password-form')
            </section>
        </div>
    </div>
</x-app-layout>
