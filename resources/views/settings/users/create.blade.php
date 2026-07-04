<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">Tambah User</h2>
    </x-slot>

    <div class="slam-panel max-w-lg p-6 mx-auto mt-6">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf
            <div><x-input-label for="name" value="Nama" /><x-text-input id="name" name="name" class="block w-full" required /></div>
            <div><x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" class="block w-full" required /></div>
            <div><x-input-label for="password" value="Password" /><x-text-input id="password" name="password" type="password" class="block w-full" required /></div>
            <div><x-input-label for="password_confirmation" value="Konfirmasi Password" /><x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block w-full" required /></div>
            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white">
                    @foreach ($roles as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('settings.index') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-neutral-400">Batal</a>
                <button type="submit" class="rounded-lg bg-[#e66a4a] px-4 py-2 text-sm text-white">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>