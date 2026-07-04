<x-app-layout>
    <x-slot name="header">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">settings</span>
        </div>
        <div>
            <h2 class="text-[22px] font-semibold tracking-tight text-white">Pengaturan</h2>
            <p class="mt-1 text-sm text-neutral-500">Kelola user dan konfigurasi sistem SLAM.</p>
        </div>
    </x-slot>

    <div x-data="settingsUserModal()" class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-3">
            <section class="slam-panel p-5 lg:col-span-2">
                <div class="flex flex-col gap-4 border-b border-white/5 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e66a4a]/10 ring-1 ring-[#e66a4a]/20">
                            <span class="material-symbols-outlined text-[18px] text-[#e66a4a]">group</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">User Management</p>
                            <p class="text-xs text-neutral-500">Tambah, edit, dan hapus user aplikasi.</p>
                        </div>
                    </div>
                    <button type="button" @click="openCreateModal()" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-[#e66a4a] px-4 text-sm font-medium text-white shadow-sm shadow-[#e66a4a]/20 transition hover:bg-[#ff7b5c] active:scale-[0.97]">
                        <span class="material-symbols-outlined text-[16px]">person_add</span>
                        Tambah User
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-white/5 bg-[#2a2a2a] text-[11px] uppercase tracking-[0.18em] text-neutral-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nama</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 text-center font-medium">Aksi</th>
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
                                            <button type="button" @click="openEditModal({{ json_encode($user) }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-[#262626] text-neutral-400 transition hover:border-[#e66a4a]/40 hover:text-[#e66a4a]" title="Edit User">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-[#262626] text-neutral-400 transition hover:border-red-500/40 hover:text-red-400 disabled:cursor-not-allowed disabled:opacity-40" title="Hapus User" @disabled(auth()->id() === $user->id)>
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center text-sm text-neutral-500">Belum ada user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="space-y-4">
                @foreach ([
                    ['icon' => 'translate', 'title' => 'Setting Bahasa', 'description' => 'Pilihan bahasa aplikasi.', 'value' => 'Coming Soon'],
                    ['icon' => 'routine', 'title' => 'Tema Dark / Light', 'description' => 'Pengaturan tema tampilan.', 'value' => 'Coming Soon'],
                    ['icon' => 'database', 'title' => 'Backup & Import Database', 'description' => 'Download backup dan import database.', 'value' => 'Coming Soon'],
                ] as $item)
                    <div class="slam-panel p-5 opacity-80">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 ring-1 ring-white/10">
                                <span class="material-symbols-outlined text-[18px] text-neutral-400">{{ $item['icon'] }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-white">{{ $item['title'] }}</p>
                                    <span class="rounded-full border border-white/10 px-2 py-0.5 text-[10px] uppercase tracking-[0.16em] text-neutral-500">{{ $item['value'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-neutral-500">{{ $item['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>

        <template x-teleport="body">
            <div x-show="showCreateUser" x-cloak class="fixed left-0 top-0 right-0 bottom-0 z-[9999] flex h-screen w-screen items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @keydown.escape.window="closeCreateModal()" @click.self="closeCreateModal()">
                <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-2xl">
                    <div class="flex items-center border-b border-white/5 bg-[#242424] px-5 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeCreateModal()" class="h-3 w-3 rounded-full bg-[#ff5f57]" aria-label="Tutup"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
                        </div>
                        <p class="flex-1 pr-[52px] text-center text-[13px] font-semibold text-neutral-200">Tambah User</p>
                    </div>

                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4 p-5" @submit="if (password.length < 8 || password !== passwordConfirmation) { $event.preventDefault(); }">
                        @csrf
                        <div>
                            <x-input-label for="create_name" value="Nama" />
                            <x-text-input id="create_name" name="name" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="create_email" value="Email" />
                            <x-text-input id="create_email" name="email" type="email" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="create_password" value="Password" />
                            <x-text-input id="create_password" name="password" type="password" x-model="password" class="mt-1 block w-full" required />
                            <p class="mt-2 text-xs text-neutral-500">Password minimal 8 karakter.</p>
                            <p x-show="password && password.length < 8" x-cloak class="mt-1 text-xs text-red-400">Password masih kurang dari 8 karakter.</p>
                        </div>
                        <div>
                            <x-input-label for="create_password_confirmation" value="Konfirmasi Password" />
                            <x-text-input id="create_password_confirmation" name="password_confirmation" type="password" x-model="passwordConfirmation" class="mt-1 block w-full" required />
                            <p x-show="passwordConfirmation && password !== passwordConfirmation" x-cloak class="mt-2 text-xs text-red-400">Konfirmasi password tidak sama dengan password.</p>
                        </div>
                        <div>
                            <x-input-label for="create_role" value="Role" />
                            <select id="create_role" name="role" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-white/5 pt-4">
                            <button type="button" @click="closeCreateModal()" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-neutral-400 transition hover:text-white">Batal</button>
                            <button type="submit" :disabled="password.length < 8 || password !== passwordConfirmation" class="rounded-lg bg-[#e66a4a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#ff7b5c] disabled:cursor-not-allowed disabled:opacity-50">Simpan</button>
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
                            <button type="button" @click="closeEditModal()" class="h-3 w-3 rounded-full bg-[#ff5f57]" aria-label="Tutup"></button>
                            <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
                            <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
                        </div>
                        <p class="flex-1 pr-[52px] text-center text-[13px] font-semibold text-neutral-200">Edit User</p>
                    </div>

                    <form method="POST" :action="`/users/${editUser.id}`" class="space-y-4 p-5" @submit="if ((editPassword || editPasswordConfirmation) && (editPassword.length < 8 || editPassword !== editPasswordConfirmation)) { $event.preventDefault(); }">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="edit_name" value="Nama" />
                            <x-text-input id="edit_name" name="name" x-model="editUser.name" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="edit_email" value="Email" />
                            <x-text-input id="edit_email" name="email" type="email" x-model="editUser.email" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="edit_role" value="Role" />
                            <select id="edit_role" name="role" x-model="editUser.role" class="mt-1 block w-full rounded-xl border border-white/10 bg-[#262626] px-4 py-2.5 text-sm text-white focus:border-[#e66a4a] focus:ring-[#e66a4a]/20" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="edit_password" value="Password Baru (Opsional)" />
                            <x-text-input id="edit_password" name="password" type="password" x-model="editPassword" class="mt-1 block w-full" />
                            <p class="mt-2 text-xs text-neutral-500">Minimal 8 karakter jika ingin diubah.</p>
                            <p x-show="editPassword && editPassword.length < 8" x-cloak class="mt-1 text-xs text-red-400">Password minimal 8 karakter.</p>
                        </div>
                        <div>
                            <x-input-label for="edit_password_confirmation" value="Konfirmasi Password Baru" />
                            <x-text-input id="edit_password_confirmation" name="password_confirmation" type="password" x-model="editPasswordConfirmation" class="mt-1 block w-full" />
                            <p x-show="editPasswordConfirmation && editPassword !== editPasswordConfirmation" x-cloak class="mt-2 text-xs text-red-400">Konfirmasi password tidak sama.</p>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-white/5 pt-4">
                            <button type="button" @click="closeEditModal()" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-neutral-400 transition hover:text-white">Batal</button>
                            <button type="submit" :disabled="(editPassword.length > 0 && editPassword.length < 8) || (editPassword !== editPasswordConfirmation)" class="rounded-lg bg-[#e66a4a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#ff7b5c] disabled:cursor-not-allowed disabled:opacity-50">Simpan</button>
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
