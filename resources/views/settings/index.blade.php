<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">settings</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">{{ __('settings.title') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('settings.subtitle') }}</p>
        </div>
    </x-slot>

    <div x-data="Object.assign(settingsUserModal(), { showImportModal: false })" class="space-y-6">
        <section class="slam-panel p-5">
            <div class="flex flex-col gap-4 border-b border-white/5 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">group</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ __('settings.user_management') }}</p>
                        <p class="text-xs text-neutral-500">{{ __('settings.user_management_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="openCreateModal()" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    {{ __('settings.add_user') }}
                </button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('settings.name') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('settings.email') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('settings.role') }}</th>
                            <th class="px-4 py-3 text-center font-medium">{{ __('settings.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($users as $user)
                            <tr class="transition hover:bg-white/[0.03]">
                                <td class="px-4 py-3 font-medium text-white">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-neutral-400">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-[#e66a4a]/10 px-3 py-1 text-xs text-[#e66a4a]">{{ ucfirst($user->role ?? 'operator') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal({{ json_encode($user) }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-[#262626] text-neutral-400 transition hover:border-[#e66a4a]/40 hover:text-[#e66a4a]" title="{{ __('settings.edit_user') }}">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('{{ __('settings.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-[#262626] text-neutral-400 transition hover:border-red-500/40 hover:text-red-400 disabled:cursor-not-allowed disabled:opacity-40" title="{{ __('settings.delete_user') }}" @disabled(auth()->id() === $user->id)>
                                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-sm text-neutral-500">{{ __('settings.no_users') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="slam-panel p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 ring-1 ring-white/10">
                        <span class="material-symbols-outlined text-[18px] text-neutral-400">language</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white">{{ __('settings.lang_setting') }}</p>
                            <form method="POST" action="{{ route('locale.update') }}">
                                @csrf
                                <select name="locale" onchange="this.form.submit()" class="block w-20 rounded-full border border-white/10 bg-[#262626] px-2 py-0.5 text-[10px] uppercase tracking-[0.16em] text-neutral-300 focus:border-[#e66a4a] focus:ring-[#e66a4a]/20">
                                    <option value="id" @selected(app()->getLocale() === 'id')>ID</option>
                                    <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
                                </select>
                            </form>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">{{ __('settings.lang_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="slam-panel p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                        <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">database</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white">{{ __('settings.backup_setting') }}</p>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('settings.backup.export') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#e66a4a] px-3 py-1.5 text-[11px] font-medium text-white transition hover:bg-[#ff7b5c]">
                                    <span class="material-symbols-outlined text-[14px]">download</span>
                                    {{ __('settings.export') }}
                                </a>
                                <button @click="showImportModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-[#262626] px-3 py-1.5 text-[11px] font-medium text-neutral-300 transition hover:bg-[#2f2f2f] hover:text-white">
                                    <span class="material-symbols-outlined text-[14px]">upload</span>
                                    {{ __('settings.import') }}
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">{{ __('settings.backup_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>


        <template x-teleport="body">
            <div x-show="showCreateUser" x-cloak class="fixed left-0 top-0 right-0 bottom-0 z-[9999] flex h-screen w-screen items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @keydown.escape.window="closeCreateModal()" @click.self="closeCreateModal()">
                <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl">
                    <div class="flex items-center border-b border-white/5 bg-[#242424] px-5 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeCreateModal()" class="h-3 w-3 rounded-full bg-[#ff5f57]" :aria-label="__('settings.cancel')"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
                        </div>
                        <p class="flex-1 pr-[52px] text-center text-[13px] font-semibold text-neutral-200">{{ __('settings.add_user') }}</p>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4 p-5" @submit="if (password.length < 8 || password !== passwordConfirmation) { $event.preventDefault(); }">
                        @csrf
                        <div>
                            <x-input-label for="create_name" :value="__('settings.name')" />
                            <x-text-input id="create_name" name="name" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="create_email" :value="__('settings.email')" />
                            <x-text-input id="create_email" name="email" type="email" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="create_password" :value="__('settings.password')" />
                            <x-text-input id="create_password" name="password" type="password" x-model="password" class="mt-1 block w-full" required />
                            <p class="mt-2 text-xs text-neutral-500">{{ __('settings.password_min') }}</p>
                            <p x-show="password && password.length < 8" x-cloak class="mt-1 text-xs text-red-400">{{ __('settings.password_too_short') }}</p>
                        </div>
                        <div>
                            <x-input-label for="create_password_confirmation" :value="__('settings.password_confirmation')" />
                            <x-text-input id="create_password_confirmation" name="password_confirmation" type="password" x-model="passwordConfirmation" class="mt-1 block w-full" required />
                            <p x-show="passwordConfirmation && password !== passwordConfirmation" x-cloak class="mt-2 text-xs text-red-400">{{ __('settings.password_mismatch') }}</p>
                        </div>
                        <div>
                            <x-input-label for="create_role" :value="__('settings.role')" />
                            <select id="create_role" name="role" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-white/5 pt-4">
                            <button type="button" @click="closeCreateModal()" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-neutral-400 transition hover:text-white">{{ __('settings.cancel') }}</button>
                            <button type="submit" :disabled="password.length < 8 || password !== passwordConfirmation" class="rounded-lg bg-[#e66a4a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#ff7b5c] disabled:cursor-not-allowed disabled:opacity-50">{{ __('settings.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="showEditUser" x-cloak class="fixed left-0 top-0 right-0 bottom-0 z-[9999] flex h-screen w-screen items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @keydown.escape.window="closeEditModal()" @click.self="closeEditModal()">
                <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl">
                    <div class="flex items-center border-b border-white/5 bg-[#242424] px-5 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeEditModal()" class="h-3 w-3 rounded-full bg-[#ff5f57]" :aria-label="__('settings.cancel')"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
                        </div>
                        <p class="flex-1 pr-[52px] text-center text-[13px] font-semibold text-neutral-200">{{ __('settings.edit_user') }}</p>
                    </div>

                    <form method="POST" :action="`/users/${editUser.id}`" class="space-y-4 p-5" @submit="if ((editPassword || editPasswordConfirmation) && (editPassword.length < 8 || editPassword !== editPasswordConfirmation)) { $event.preventDefault(); }">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="edit_name" :value="__('settings.name')" />
                            <x-text-input id="edit_name" name="name" x-model="editUser.name" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="edit_email" :value="__('settings.email')" />
                            <x-text-input id="edit_email" name="email" type="email" x-model="editUser.email" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="edit_role" :value="__('settings.role')" />
                            <select id="edit_role" name="role" x-model="editUser.role" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="edit_password" :value="__('settings.optional_password')" />
                            <x-text-input id="edit_password" name="password" type="password" x-model="editPassword" class="mt-1 block w-full" />
                            <p class="mt-2 text-xs text-neutral-500">{{ __('settings.optional_password_help') }}</p>
                            <p x-show="editPassword && editPassword.length < 8" x-cloak class="mt-1 text-xs text-red-400">{{ __('settings.password_too_short') }}</p>
                        </div>
                        <div>
                            <x-input-label for="edit_password_confirmation" :value="__('settings.confirm_new_password')" />
                            <x-text-input id="edit_password_confirmation" name="password_confirmation" type="password" x-model="editPasswordConfirmation" class="mt-1 block w-full" />
                            <p x-show="editPasswordConfirmation && editPassword !== editPasswordConfirmation" x-cloak class="mt-2 text-xs text-red-400">{{ __('settings.password_mismatch') }}</p>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-white/5 pt-4">
                            <button type="button" @click="closeEditModal()" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-neutral-400 transition hover:text-white">{{ __('settings.cancel') }}</button>
                            <button type="submit" :disabled="(editPassword.length > 0 && editPassword.length < 8) || (editPassword !== editPasswordConfirmation)" class="rounded-lg bg-[#e66a4a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#ff7b5c] disabled:cursor-not-allowed disabled:opacity-50">{{ __('settings.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="showImportModal" x-cloak class="fixed left-0 top-0 right-0 bottom-0 z-[9999] flex h-screen w-screen items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @keydown.escape.window="showImportModal = false" @click.self="showImportModal = false">
                <div class="w-full max-w-sm overflow-hidden rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl">
                    <div class="flex items-center border-b border-white/5 bg-[#242424] px-5 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showImportModal = false" class="h-3 w-3 rounded-full bg-[#ff5f57]"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
                        </div>
                        <p class="flex-1 pr-[52px] text-center text-[13px] font-semibold text-neutral-200">{{ __('settings.import_db') ?? 'Import Database' }}</p>
                    </div>
                    <form method="POST" action="{{ route('settings.backup.import') }}" enctype="multipart/form-data" class="p-5" onsubmit="return confirm('{{ __('settings.confirm_restore') }}');">
                        @csrf
                        <div class="mb-5">
                            <label class="mb-2 block text-xs font-medium text-neutral-400">{{ __('settings.select_file') ?? 'Pilih File Backup (.sql / .sqlite)' }}</label>
                            <input type="file" name="backup_file" accept=".sql,.sqlite,.db" class="w-full text-sm text-neutral-400 file:mr-4 file:rounded-lg file:border-0 file:bg-[#e66a4a]/10 file:px-4 file:py-2 file:text-[11px] file:font-semibold file:text-[#e66a4a] hover:file:bg-[#e66a4a]/20 cursor-pointer" required>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-white/5 pt-4">
                            <button type="button" @click="showImportModal = false" class="rounded-lg border border-white/10 bg-[#262626] px-4 py-2 text-sm text-neutral-400 transition hover:bg-[#2f2f2f] hover:text-white">{{ __('settings.cancel') ?? 'Batal' }}</button>
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-500">{{ __('settings.import_button') ?? 'Restore Database' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>

    @push('scripts')
        <script>
            function settingsUserModal() {
                return {
                    showCreateUser: false,
                    showEditUser: false,
                    password: '',
                    passwordConfirmation: '',
                    editUser: { id: null, name: '', email: '', role: 'operator' },
                    editPassword: '',
                    editPasswordConfirmation: '',
                    openCreateModal() {
                        this.password = '';
                        this.passwordConfirmation = '';
                        this.showCreateUser = true;
                        document.documentElement.classList.add('overflow-hidden');
                        document.body.classList.add('overflow-hidden');
                    },
                    closeCreateModal() {
                        this.showCreateUser = false;
                        document.documentElement.classList.remove('overflow-hidden');
                        document.body.classList.remove('overflow-hidden');
                    },
                    openEditModal(user) {
                        this.editUser = { id: user.id, name: user.name, email: user.email, role: user.role };
                        this.editPassword = '';
                        this.editPasswordConfirmation = '';
                        this.showEditUser = true;
                        document.documentElement.classList.add('overflow-hidden');
                        document.body.classList.add('overflow-hidden');
                    },
                    closeEditModal() {
                        this.showEditUser = false;
                        document.documentElement.classList.remove('overflow-hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
