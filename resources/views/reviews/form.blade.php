@php
    $sections = [
        'Aktivita študenta' => [
            'activity_independence' => 'Samostatnosť',
            'activity_creativity' => 'Tvorivosť',
        ],
        'Kvalita riešenia' => [
            'quality_overall_concept' => 'Celková koncepcia práce',
            'quality_topic_completeness' => 'Úplnosť spracovania témy',
            'quality_topic_quality' => 'Kvalita spracovania témy',
            'quality_methods' => 'Použité metódy riešenia',
            'quality_complexity' => 'Algoritmická náročnosť a pracnosť riešenia',
            'quality_practicality' => 'Praktická aplikovateľnosť práce',
        ],
        'Práca s literatúrou' => [
            'literature_sorting' => 'Triedenie a hodnotenie prameňov',
            'literature_usage' => 'Využitie poznatkov z literatúry a praxe',
            'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov',
        ],
        'Formálna úroveň práce' => [
            'formal_logic' => 'Logika usporiadania práce',
            'formal_style' => 'Štylizácia textu',
            'formal_terminology' => 'Použitá terminológia',
            'formal_graphics' => 'Grafická realizácia',
        ],
    ];

    $sectionComments = [
        'Aktivita študenta' => 'activity_comment',
        'Kvalita riešenia' => 'quality_comment',
        'Práca s literatúrou' => 'literature_comment',
        'Formálna úroveň práce' => 'formal_comment',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">{{ $review->exists ? 'Úprava posudku' : 'Nový posudok' }}</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-900">{{ $review->exists ? 'Aktualizácia hodnotenia bakalárskej práce' : 'Nový posudok vedúceho bakalárskej práce' }}</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Formulár zodpovedá dodanému vzoru, validuje hodnotenia a pripraví dáta pre PDF export.</p>
            </div>
            <a href="{{ route('reviews.index') }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">Späť na zoznam</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                @php
                    $statusType = session('status_type', 'success');
                    $statusClasses = [
                        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'info' => 'border-rose-200 bg-rose-50 text-rose-700',
                    ];
                @endphp
                <div class="mb-6 rounded-2xl border px-4 py-4 text-sm shadow-sm {{ $statusClasses[$statusType] ?? $statusClasses['success'] }}">
                    @if (session('status_title'))
                        <p class="font-semibold">{{ session('status_title') }}</p>
                    @endif
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Formulár obsahuje chyby:</p>
                    <ul class="mt-2 list-disc ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $review->exists ? route('reviews.update', $review) : route('reviews.store') }}" class="space-y-8">
                @csrf
                @if ($review->exists)
                    @method('PUT')
                @endif

                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Základné údaje</h3>
                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Akademický rok</span>
                            <input name="academic_year" value="{{ old('academic_year', $review->academic_year ?? '2025/2026') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Dátum vyplnenia</span>
                            <input type="date" name="review_date" value="{{ old('review_date', $review->review_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Meno študenta</span>
                            <input name="student_name" value="{{ old('student_name', $review->student_name) }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Študijný program</span>
                            <input name="study_program" value="{{ old('study_program', $review->study_program) }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-sm font-medium text-gray-700">Názov práce</span>
                            <textarea name="thesis_title" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>{{ old('thesis_title', $review->thesis_title) }}</textarea>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Meno vedúceho</span>
                            <input name="supervisor_name" value="{{ old('supervisor_name', $review->supervisor_name) }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Miesto</span>
                            <input name="place" value="{{ old('place', $review->place ?? 'Trnava') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                    </div>
                </section>

                @foreach ($sections as $sectionTitle => $fields)
                    <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $sectionTitle }}</h3>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($fields) }} kriterii</span>
                        </div>
                        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($fields as $field => $label)
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                    <select name="{{ $field }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                        <option value="">Vyber známku</option>
                                        @foreach ($grades as $value => $labelValue)
                                            <option value="{{ $value }}" @selected(old($field, $review->{$field}) === $value)>
                                                {{ $value }} - {{ $labelValue }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                        <label class="mt-6 block">
                            <span class="text-sm font-medium text-gray-700">Komentár</span>
                            <textarea name="{{ $sectionComments[$sectionTitle] }}" rows="5" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old($sectionComments[$sectionTitle], $review->{$sectionComments[$sectionTitle]}) }}</textarea>
                        </label>
                    </section>
                @endforeach

                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Záver a originalita</h3>
                    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Celkové zhodnotenie</span>
                            <select name="final_recommendation" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                @foreach ($recommendations as $value => $label)
                                    <option value="{{ $value }}" @selected(old('final_recommendation', $review->final_recommendation ?? 'recommend') === $value)>
                                        {{ ucfirst($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Výsledná známka</span>
                            <select name="final_grade" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                <option value="">Vyber známku</option>
                                @foreach ($grades as $value => $label)
                                    <option value="{{ $value }}" @selected(old('final_grade', $review->final_grade) === $value)>
                                        {{ $value }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Miera originality z CRZP v %</span>
                            <input type="number" step="0.01" min="0" max="100" name="originality_percentage" value="{{ old('originality_percentage', $review->originality_percentage ?? '4.00') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Stav originality</span>
                            <select name="originality_status" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                                @foreach ($originalityStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('originality_status', $review->originality_status ?? 'vyhovujuca') === $value)>
                                        {{ ucfirst($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="mt-6 block">
                        <span class="text-sm font-medium text-gray-700">Otázky a pripomienky k práci</span>
                        <textarea name="questions" rows="6" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('questions', $review->questions) }}</textarea>
                    </label>
                    <label class="mt-6 block">
                        <span class="text-sm font-medium text-gray-700">Komentár k protokolu o kontrole originality</span>
                        <textarea name="originality_comment" rows="4" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('originality_comment', $review->originality_comment ?? 'Práca je autorská.') }}</textarea>
                    </label>
                    <label class="mt-6 block">
                        <span class="text-sm font-medium text-gray-700">Vyjadrenie autora</span>
                        <textarea name="author_statement" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500" required>{{ old('author_statement', $review->author_statement ?? 'Práca je autorská.') }}</textarea>
                    </label>
                </section>

                <div class="flex items-center justify-end gap-3">
                    @if ($review->exists)
                        <a href="{{ route('reviews.show', $review) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">Náhľad</a>
                    @endif
                    <button type="submit" class="rounded-2xl bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800">
                        {{ $review->exists ? 'Uložiť zmeny' : 'Vytvoriť posudok' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
