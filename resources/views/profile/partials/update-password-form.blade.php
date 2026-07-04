<section>
    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">lock</span>
        </div>
        <div>
            <p class="text-sm font-medium text-white">Ganti Password</p>
            <p class="text-xs text-neutral-500">Pastikan password baru kuat dan aman.</p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Password Saat Ini" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password Baru" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 border-t border-white/5 pt-4">
            <button type="submit" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                <span class="material-symbols-outlined text-[16px]">key</span>
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-neutral-400">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
