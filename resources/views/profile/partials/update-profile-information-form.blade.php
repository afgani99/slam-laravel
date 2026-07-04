<section>
    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">badge</span>
        </div>
        <div>
            <p class="text-sm font-medium text-white">Profile Information</p>
            <p class="text-xs text-neutral-500">Ubah nama dan email akun login.</p>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-neutral-400">
                        Email belum diverifikasi.
                        <button form="send-verification" class="rounded-md text-sm text-neutral-500 underline hover:text-white">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-400">
                            Link verifikasi baru sudah dikirim ke email.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 border-t border-white/5 pt-4">
            <button type="submit" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                <span class="material-symbols-outlined text-[16px]">save</span>
                Simpan Profile
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-neutral-400">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
