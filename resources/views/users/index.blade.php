<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">Admin</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-900">Sprava pouzivatelov</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Vytvaranie uctov, uprava roli a zakladnych prihlasovacich udajov. Heslo mozes zmenit priamo v riadku pouzivatela.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                @php
                    $statusType = session('status_type', 'success');
                    $statusClasses = [
                        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'info' => 'border-rose-200 bg-rose-50 text-rose-700',
                    ];
                @endphp
                <div class="rounded-2xl border px-4 py-4 text-sm shadow-sm {{ $statusClasses[$statusType] ?? $statusClasses['success'] }}">
                    @if (session('status_title'))
                        <p class="font-semibold">{{ session('status_title') }}</p>
                    @endif
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700 shadow-sm">
                    <p class="font-semibold">Formular obsahuje chyby:</p>
                    <ul class="mt-2 list-disc ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Novy pouzivatel</h3>
                        <p class="mt-1 text-sm text-slate-500">Vytvorenie noveho uctu s volitelnou admin rolou.</p>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $users->count() }} uctov</span>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Meno</span>
                        <input name="name" value="{{ old('name') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Heslo</span>
                        <input type="text" name="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    </label>
                    <label class="flex items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin')) class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-500">
                        <span class="text-sm font-medium text-slate-700">Admin prava</span>
                    </label>
                    <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                        <button type="submit" class="rounded-2xl bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800">Vytvorit pouzivatela</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-sm ring-1 ring-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/90 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Pouzivatel</th>
                                <th class="px-4 py-3 text-left font-semibold">Rola</th>
                                <th class="px-4 py-3 text-left font-semibold">Overeny email</th>
                                <th class="px-4 py-3 text-left font-semibold">Uprava</th>
                                <th class="px-4 py-3 text-right font-semibold">Akcia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($users as $managedUser)
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">{{ $managedUser->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $managedUser->email }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $managedUser->isAdmin() ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">{{ $managedUser->isAdmin() ? 'Admin' : 'Pouzivatel' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">{{ $managedUser->email_verified_at ? $managedUser->email_verified_at->format('d.m.Y H:i') : 'Nie' }}</td>
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('users.update', $managedUser) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                            @csrf
                                            @method('PATCH')
                                            <input name="name" value="{{ $managedUser->name }}" class="rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                            <input type="email" name="email" value="{{ $managedUser->email }}" class="rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                            <input type="text" name="password" placeholder="Nove heslo (volitelne)" class="rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2">
                                                <input type="checkbox" name="is_admin" value="1" @checked($managedUser->isAdmin()) @disabled(auth()->user()->is($managedUser)) class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-500">
                                                <span class="text-sm text-slate-700">Admin</span>
                                            </label>
                                            <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                                                <button type="submit" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">Ulozit</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        @if (auth()->user()->is($managedUser))
                                            <span class="text-xs font-medium text-slate-400">Aktualny ucet</span>
                                        @else
                                            <form method="POST" action="{{ route('users.destroy', $managedUser) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-2xl border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50" onclick="return confirm('Naozaj chces zmazat tohto pouzivatela?')">Zmazat</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>