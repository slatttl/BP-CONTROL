@php
    $sections = [
        'Aktivita studenta' => [
            'activity_independence' => 'Samostatnost',
            'activity_creativity' => 'Tvorivost',
        ],
        'Kvalita riesenia' => [
            'quality_overall_concept' => 'Celkova koncepcia prace',
            'quality_topic_completeness' => 'Uplnost spracovania temy',
            'quality_topic_quality' => 'Kvalita spracovania temy',
            'quality_methods' => 'Pouzite metody riesenia',
            'quality_complexity' => 'Algoritmicka narocnost a pracnost riesenia',
            'quality_practicality' => 'Prakticka aplikovatelnost prace',
        ],
        'Praca s literaturou' => [
            'literature_sorting' => 'Triedenie a hodnotenie pramenov',
            'literature_usage' => 'Vyuzitie poznatkov z literatury a praxe',
            'literature_conclusions' => 'Vyvodzovanie vlastnych zaverov z literarnych pramenov',
        ],
        'Formalna uroven prace' => [
            'formal_logic' => 'Logika usporiadania prace',
            'formal_style' => 'Stylizacia textu',
            'formal_terminology' => 'Pouzita terminologia',
            'formal_graphics' => 'Graficka realizacia',
        ],
    ];

    $sectionComments = [
        'Aktivita studenta' => 'activity_comment',
        'Kvalita riesenia' => 'quality_comment',
        'Praca s literaturou' => 'literature_comment',
        'Formalna uroven prace' => 'formal_comment',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">Detail posudku</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-900">{{ $review->student_name }}</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ $review->thesis_title }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('reviews.edit', $review) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">Upravit</a>
                <form method="POST" action="{{ route('reviews.duplicate', $review) }}">
                    @csrf
                    <button type="submit" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Vytvorit kopiu</button>
                </form>
                <a href="{{ route('reviews.pdf', $review) }}" class="rounded-2xl bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800">Stiahnut PDF</a>
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

            <section class="grid gap-6 xl:grid-cols-[1.25fr,0.75fr]">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Zakladne udaje</h3>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Akademicky rok</dt><dd class="mt-1 font-semibold text-slate-900">{{ $review->academic_year }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Studijny program</dt><dd class="mt-1 font-semibold text-slate-900">{{ $review->study_program }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Veduci</dt><dd class="mt-1 font-semibold text-slate-900">{{ $review->supervisor_name }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Miesto a datum</dt><dd class="mt-1 font-semibold text-slate-900">{{ $review->place }}, {{ $review->review_date?->format('d.m.Y') }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Zaver</h3>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">Odporucenie</p><p class="mt-1 text-lg font-semibold text-slate-900">{{ $recommendations[$review->final_recommendation] ?? $review->final_recommendation }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">Vysledna znamka</p><p class="mt-1 text-lg font-semibold text-slate-900">{{ $review->final_grade }} - {{ $grades[$review->final_grade] ?? '' }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm text-slate-500">Originalita CRZP</p><p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $review->originality_percentage, 2, ',', ' ') }} % / {{ $originalityStatuses[$review->originality_status] ?? $review->originality_status }}</p></div>
                    </div>
                </div>
            </section>

            @foreach ($sections as $sectionTitle => $fields)
                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $sectionTitle }}</h3>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($fields) }} kriterii</span>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($fields as $field => $label)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-sm text-slate-500">{{ $label }}</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $review->{$field} }} - {{ $grades[$review->{$field}] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-slate-900">Komentar</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $review->{$sectionComments[$sectionTitle]} ?: 'Bez komentara.' }}</p>
                    </div>
                </section>
            @endforeach

            <section class="grid gap-6 xl:grid-cols-[1fr,0.65fr]">
                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Otazky a pripomienky</h3>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $review->questions ?: 'Bez otazok a pripomienok.' }}</p>
                </div>
                <div class="rounded-3xl border border-rose-100 bg-white/90 p-6 shadow-sm ring-1 ring-rose-50">
                    <h3 class="text-lg font-semibold text-rose-900">Sprava zaznamu</h3>
                    <p class="mt-2 text-sm text-slate-600">Mazanie je nevratna operacia. Pred odstraneniam si mozes vytvorit kopiu alebo stiahnut PDF.</p>
                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-6">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-2xl border border-rose-300 px-4 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50" onclick="return confirm('Naozaj chces zmazat tento posudok? Operacia sa neda vratit.')">Zmazat posudok</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
