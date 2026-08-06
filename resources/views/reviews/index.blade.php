<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">BP Control</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-900">Evidencia posudkov bakalárskej práce</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Správa hodnotení, filtrovanie, export do PDF a rýchle vytváranie šablón z existujúcich posudkov.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($isAdmin)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-800">Režim: {{ $selectedScope === 'all' ? 'všetky posudky' : 'moje posudky' }}</div>
                @endif
                <a href="{{ route('reviews.create') }}" class="inline-flex items-center rounded-2xl bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800">Nový posudok</a>
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

            <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-4">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm text-slate-500">Záznamov v aktuálnom scope</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalReviews }}</p>
                </div>
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm text-slate-500">Po filtroch</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $filteredReviews }}</p>
                </div>
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm text-slate-500">Posledná úprava</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ optional($reviews->first()?->updated_at)->format('d.m.Y H:i') ?? 'Zatiaľ žiadna' }}</p>
                </div>
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm text-slate-500">Prihlásený používateľ</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                </div>
            </div>

            <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm ring-1 ring-slate-100">
                @if ($isAdmin)
                    <div class="mb-5 flex flex-wrap gap-2">
                        <a href="{{ route('reviews.index', array_merge(request()->except('page'), ['scope' => 'mine'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $selectedScope === 'mine' ? 'bg-rose-700 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">Moje posudky</a>
                        <a href="{{ route('reviews.index', array_merge(request()->except('page'), ['scope' => 'all'])) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $selectedScope === 'all' ? 'bg-rose-700 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">Všetky posudky</a>
                    </div>
                @endif

                <form method="GET" action="{{ route('reviews.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @if ($isAdmin)
                        <input type="hidden" name="scope" value="{{ $selectedScope }}">
                    @endif

                    <div class="xl:col-span-2">
                        <label for="q" class="text-sm font-medium text-slate-700">Hľadať podľa študenta alebo názvu práce</label>
                        <input id="q" name="q" value="{{ $search }}"  class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="grade" class="text-sm font-medium text-slate-700">Finálna známka</label>
                        <select id="grade" name="grade" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">Všetky známky</option>
                            @foreach ($grades as $value => $label)
                                <option value="{{ $value }}" @selected($selectedGrade === $value)>{{ $value }} - {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="academic_year" class="text-sm font-medium text-slate-700">Akademický rok</label>
                        <select id="academic_year" name="academic_year" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">Všetky roky</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected($selectedAcademicYear === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="study_program" class="text-sm font-medium text-slate-700">Študijný program</label>
                        <select id="study_program" name="study_program" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">Všetky programy</option>
                            @foreach ($programOptions as $program)
                                <option value="{{ $program }}" @selected($selectedStudyProgram === $program)>{{ $program }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="text-sm font-medium text-slate-700">Dátum od</label>
                        <input type="date" id="date_from" name="date_from" value="{{ $selectedDateFrom }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="date_to" class="text-sm font-medium text-slate-700">Dátum do</label>
                        <input type="date" id="date_to" name="date_to" value="{{ $selectedDateTo }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div class="flex items-end gap-2 xl:col-span-3">
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800">Filtrovať</button>
                        <a href="{{ route('reviews.index', $isAdmin ? ['scope' => $selectedScope] : []) }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Reset</a>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-sm ring-1 ring-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/90 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Student</th>
                                @if ($isAdmin && $selectedScope === 'all')
                                    <th class="px-4 py-3 text-left font-semibold">Vlastník</th>
                                @endif
                                <th class="px-4 py-3 text-left font-semibold">Študijný program</th>
                                <th class="px-4 py-3 text-left font-semibold">Rok</th>
                                <th class="px-4 py-3 text-left font-semibold">Známka</th>
                                <th class="px-4 py-3 text-left font-semibold">Dátum</th>
                                <th class="px-4 py-3 text-right font-semibold">Akcie</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($reviews as $review)
                                <tr class="align-top transition hover:bg-slate-50/60">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">{{ $review->student_name }}</div>
                                        <div class="mt-1 max-w-md text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($review->thesis_title, 82) }}</div>
                                    </td>
                                    @if ($isAdmin && $selectedScope === 'all')
                                        <td class="px-4 py-4 text-slate-600">
                                            <div class="font-medium text-slate-800">{{ $review->user?->name ?? 'Neznámy používateľ' }}</div>
                                            <div class="text-xs text-slate-500">{{ $review->user?->email }}</div>
                                        </td>
                                    @endif
                                    <td class="px-4 py-4 text-slate-600">{{ $review->study_program }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $review->academic_year }}</td>
                                    <td class="px-4 py-4"><span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">{{ $review->final_grade }}</span></td>
                                    <td class="px-4 py-4 text-slate-600">{{ $review->review_date?->format('d.m.Y') }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-3 text-sm">
                                            <a href="{{ route('reviews.show', $review) }}" class="font-medium text-slate-700 hover:text-slate-900">Detail</a>
                                            <a href="{{ route('reviews.edit', $review) }}" class="font-medium text-rose-700 hover:text-rose-900">Upravit</a>
                                            <form method="POST" action="{{ route('reviews.duplicate', $review) }}">
                                                @csrf
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Kópia</button>
                                            </form>
                                            <a href="{{ route('reviews.pdf', $review) }}" class="font-medium text-rose-700 hover:text-rose-900">PDF</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin && $selectedScope === 'all' ? 7 : 6 }}" class="px-4 py-12 text-center text-slate-500">Zatiaľ neexistuje žiadny posudok pre zvolený filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-4 py-4">{{ $reviews->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
