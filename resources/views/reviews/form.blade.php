@php
    $type = old('thesis_type', $review->thesis_type ?? 'bachelor');
    $role = old('review_role', $review->review_role ?? 'supervisor');
    $type = array_key_exists($type, $thesisTypes) ? $type : 'bachelor';
    $role = array_key_exists($role, $roles) ? $role : 'supervisor';
    $isOpponentRole = $role === 'opponent';
    $selectClasses = 'mt-2 block w-full rounded-2xl border-slate-200 bg-white shadow-sm focus:border-slate-500 focus:ring-slate-500';
    $academicYear = $review->review_date
        ? \App\Models\Review::academicYearFor($review->review_date)
        : \App\Models\Review::academicYearFor(now());
    $reviewDateValue = old('review_date', $review->review_date?->format('Y-m-d') ?? now()->format('Y-m-d'));
    try {
        $academicYear = \App\Models\Review::academicYearFor(\Illuminate\Support\Carbon::parse($reviewDateValue));
    } catch (\Throwable $e) {
    }
    $calculatorConfig = [
        'points' => config('review.grade_points'),
        'roleBlocks' => $roleBlocks,
        'blocks' => collect($blocks)->map(fn ($block) => [
            'criteria' => array_keys($block['criteria']),
            'critical' => $block['critical'] ?? [],
        ])->all(),
        'startMonth' => config('review.academic_year_start_month'),
        'programs' => collect($thesisTypes)->map(fn ($t) => $t['programs'])->all(),
        'authorGenitive' => collect($thesisTypes)->map(fn ($t) => $t['author_genitive'])->all(),
        'authorNameLabel' => collect($thesisTypes)->map(fn ($t) => $t['author_name_label'])->all(),
        'ownerName' => $ownerName,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">{{ $review->exists ? 'Úprava posudku' : 'Nový posudok' }}</p>
                <h2 class="mt-1 text-2xl font-semibold leading-tight text-slate-900">{{ $review->exists ? 'Aktualizácia posudku záverečnej práce' : 'Nový posudok záverečnej práce' }}</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Vyberte typ práce a rolu. Formulár sa prispôsobí a výsledná známka sa vypočíta automaticky podľa hodnotiacich vzorcov.</p>
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

            <form id="review-form" method="POST" action="{{ $review->exists ? route('reviews.update', $review) : route('reviews.store') }}" data-draft-url="{{ $draftSaveUrl }}" data-duplicate-url="{{ $duplicateCheckUrl }}" data-review-id="{{ $review->exists ? $review->id : '' }}" class="space-y-8">
                @csrf
                @if ($review->exists)
                    @method('PUT')
                @endif

                <div id="duplicate-warning" class="hidden rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status"></div>

                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Základné údaje</h3>
                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Typ práce</span>
                            <select id="thesis_type" name="thesis_type" class="{{ $selectClasses }}" required>
                                @foreach ($thesisTypes as $value => $config)
                                    <option value="{{ $value }}" @selected($type === $value)>{{ $config['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Rola</span>
                            <select id="review_role" name="review_role" class="{{ $selectClasses }}" required>
                                @foreach ($roles as $value => $config)
                                    <option value="{{ $value }}" @selected($role === $value)>{{ $config['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Dátum vyplnenia</span>
                            <input id="review_date" type="date" name="review_date" value="{{ $reviewDateValue }}" class="{{ $selectClasses }}" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Akademický rok (automaticky)</span>
                            <input id="academic_year" name="academic_year" value="{{ $academicYear }}" class="{{ $selectClasses }} bg-slate-50 text-slate-600" readonly tabindex="-1">
                        </label>
                        <label class="block">
                            <span id="student_label" class="text-sm font-medium text-gray-700">{{ $thesisTypes[$type]['author_name_label'] }}</span>
                            <input id="student_name" name="student_name" value="{{ old('student_name', $review->student_name) }}" class="{{ $selectClasses }}" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Študijný program</span>
                            <select id="study_program" name="study_program" class="{{ $selectClasses }}" required>
                                <option value="">Vyberte študijný program</option>
                                @foreach ($thesisTypes[$type]['programs'] as $program)
                                    <option value="{{ $program }}" @selected(old('study_program', $review->study_program) === $program)>{{ $program }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-sm font-medium text-gray-700">Názov práce</span>
                            <textarea name="thesis_title" rows="3" class="{{ $selectClasses }}" required>{{ old('thesis_title', $review->thesis_title) }}</textarea>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Meno vedúceho</span>
                            <input id="supervisor_name" name="supervisor_name" value="{{ $isOpponentRole ? old('supervisor_name', $review->supervisor_name) : $ownerName }}" class="{{ $selectClasses }} {{ $isOpponentRole ? '' : 'bg-slate-50 text-slate-600' }}" @readonly(! $isOpponentRole) required>
                            <span id="supervisor_hint" class="mt-1 block text-xs text-slate-500 {{ $isOpponentRole ? 'hidden' : '' }}">Vyplnené podľa prihláseného používateľa.</span>
                        </label>
                        <label id="opponent_field" class="block {{ $isOpponentRole ? '' : 'hidden' }}">
                            <span class="text-sm font-medium text-gray-700">Meno oponenta</span>
                            <input value="{{ $ownerName }}" class="{{ $selectClasses }} bg-slate-50 text-slate-600" readonly tabindex="-1">
                            <span class="mt-1 block text-xs text-slate-500">Vyplnené podľa prihláseného používateľa.</span>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Miesto</span>
                            <input name="place" value="{{ old('place', $review->place ?? 'Trnava') }}" class="{{ $selectClasses }}" required>
                        </label>
                    </div>
                </section>

                @foreach ($blocks as $blockName => $block)
                    @php $active = array_key_exists($blockName, $roleBlocks[$role]); @endphp
                    <section data-block="{{ $blockName }}" class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100 {{ $active ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-slate-900" @if ($blockName === 'activity') id="activity_title" @endif>{{ str_replace('{author}', $thesisTypes[$type]['author_genitive'], $block['title']) }}</h3>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($block['criteria']) }} kritérií</span>
                        </div>
                        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($block['criteria'] as $field => $label)
                                <label class="block">
                                    <span class="text-sm font-medium text-gray-700" @if (is_array($label)) data-labels="{{ json_encode(\App\Support\CriterionLabel::variants($label)) }}" @endif>{{ \App\Support\CriterionLabel::resolve($label, $type, $role) }}</span>
                                    <select name="{{ $field }}" data-grade class="{{ $selectClasses }}" required @disabled(! $active)>
                                        <option value="">Vyberte známku</option>
                                        @foreach ($grades as $value => $labelValue)
                                            <option value="{{ $value }}" @selected(old($field, $review->{$field}) === $value)>{{ $value }} - {{ $labelValue }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                        <label class="mt-6 block">
                            <span class="text-sm font-medium text-gray-700">Komentár</span>
                            <textarea name="{{ $block['comment'] }}" rows="5" class="{{ $selectClasses }}" required @disabled(! $active)>{{ old($block['comment'], $review->{$block['comment']}) }}</textarea>
                        </label>
                    </section>
                @endforeach

                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm ring-1 ring-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Záver a originalita</h3>
                    <div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50/60 p-4" aria-live="polite">
                        <p class="text-sm text-slate-600">Výsledná známka (vypočíta sa automaticky)</p>
                        <p id="grade-preview" class="mt-1 text-lg font-semibold text-slate-900">Vyplňte všetky kritériá.</p>
                        <p id="recommendation-preview" class="mt-1 text-sm text-slate-600"></p>
                    </div>
                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Miera originality z CRZP v %</span>
                            <input type="number" step="0.01" min="0" max="100" name="originality_percentage" value="{{ old('originality_percentage', $review->originality_percentage) }}" class="{{ $selectClasses }}" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Stav originality</span>
                            <select name="originality_status" class="{{ $selectClasses }}" required>
                                @foreach ($originalityStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('originality_status', $review->originality_status ?? 'vyhovujuca') === $value)>{{ ucfirst($label) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="mt-6 block">
                        <span class="text-sm font-medium text-gray-700">Otázky a pripomienky k práci</span>
                        <textarea name="questions" rows="6" class="{{ $selectClasses }}" required>{{ old('questions', $review->questions) }}</textarea>
                    </label>
                    <label class="mt-6 block">
                        <span class="text-sm font-medium text-gray-700">Komentár k protokolu o kontrole originality</span>
                        <textarea name="originality_comment" rows="4" class="{{ $selectClasses }}" required>{{ old('originality_comment', $review->originality_comment) }}</textarea>
                    </label>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <p id="draft-status" class="mr-auto text-sm text-slate-500" aria-live="polite">
                        {{ $draft ? 'Rozpracované zmeny sú uložené.' : 'Zmeny sa ukladajú automaticky.' }}
                    </p>
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
    <script>
        const reviewConfig = @json($calculatorConfig);

        const reviewForm = document.getElementById('review-form');
        const draftStatus = document.getElementById('draft-status');
        const duplicateWarning = document.getElementById('duplicate-warning');
        let draftTimer = null;
        let duplicateTimer;
        let draftSaveQueue = Promise.resolve();
        let duplicateRequest;

        const saveReviewDraft = () => {
            const draftData = new FormData(reviewForm);
            draftStatus.textContent = 'Ukladám rozpracované zmeny…';
            draftStatus.classList.remove('text-rose-700');

            draftSaveQueue = draftSaveQueue.then(async () => {
                try {
                    const response = await fetch(reviewForm.dataset.draftUrl, {
                        method: 'POST',
                        body: draftData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Draft save failed');
                    }

                    draftStatus.textContent = `Uložené ${new Intl.DateTimeFormat('sk-SK', { hour: '2-digit', minute: '2-digit' }).format(new Date())}`;
                } catch (error) {
                    draftStatus.textContent = 'Automatické uloženie zlyhalo. Skontrolujte pripojenie a skúste znova.';
                    draftStatus.classList.add('text-rose-700');
                }
            });

            return draftSaveQueue;
        };

        const queueDraftSave = () => {
            window.clearTimeout(draftTimer);
            draftTimer = window.setTimeout(() => {
                draftTimer = null;
                saveReviewDraft();
            }, 900);
        };

        const checkForDuplicateReview = async () => {
            if (duplicateRequest) {
                duplicateRequest.abort();
            }

            duplicateRequest = new AbortController();
            const studentName = document.getElementById('student_name').value.trim();
            const academicYear = document.getElementById('academic_year').value.trim();

            if (!studentName || !academicYear) {
                duplicateWarning.classList.add('hidden');
                return;
            }

            const params = new URLSearchParams({
                student_name: studentName,
                academic_year: academicYear,
            });

            if (reviewForm.dataset.reviewId) {
                params.set('review_id', reviewForm.dataset.reviewId);
            }

            try {
                const response = await fetch(`${reviewForm.dataset.duplicateUrl}?${params}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: duplicateRequest.signal,
                });

                if (!response.ok) {
                    throw new Error('Duplicate check failed');
                }

                const result = await response.json();
                duplicateWarning.textContent = result.duplicate
                    ? 'Už existuje posudok pre tohto študenta v zadanom akademickom roku. Skontrolujte, či nevytvárate duplicitný záznam.'
                    : '';
                duplicateWarning.classList.toggle('hidden', !result.duplicate);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                duplicateWarning.textContent = 'Kontrolu duplicity sa nepodarilo vykonať.';
                duplicateWarning.classList.remove('hidden');
            }
        };

        reviewForm.addEventListener('input', (event) => {
            queueDraftSave();

            if (event.target.name === 'student_name' || event.target.name === 'academic_year') {
                window.clearTimeout(duplicateTimer);
                duplicateTimer = window.setTimeout(checkForDuplicateReview, 350);
            }
        });
        reviewForm.addEventListener('change', queueDraftSave);
        reviewForm.addEventListener('submit', (event) => {
            event.preventDefault();
            if (draftTimer !== null) {
                window.clearTimeout(draftTimer);
                draftTimer = null;
            }
            saveReviewDraft().finally(() => reviewForm.submit());
        });

        checkForDuplicateReview();

        const gradeAdverbs = @json($gradeAdverbs);
        const letters = Object.fromEntries(Object.entries(reviewConfig.points).map(([letter, points]) => [points, letter]));
        const field = (id) => document.getElementById(id);

        const academicYearFor = (value) => {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }
            const start = date.getMonth() + 1 >= reviewConfig.startMonth ? date.getFullYear() : date.getFullYear() - 1;
            return `${start}/${start + 1}`;
        };

        const calculateGrade = (role) => {
            const weights = reviewConfig.roleBlocks[role];
            let score = 0;
            const critical = [];

            for (const [block, weight] of Object.entries(weights)) {
                const values = reviewConfig.blocks[block].criteria.map((name) => reviewConfig.points[reviewForm.elements[name]?.value]);
                if (values.some((value) => value === undefined)) {
                    return null;
                }
                score += weight * (values.reduce((sum, value) => sum + value, 0) / values.length);
                reviewConfig.blocks[block].critical.forEach((name) => critical.push(reviewConfig.points[reviewForm.elements[name].value]));
            }

            const rounded = Math.round(Math.round(score * 1e10) / 1e10);
            const points = critical.includes(6) ? 6 : (critical.includes(5) && rounded !== 6 ? 5 : rounded);

            return { score, letter: letters[Math.min(6, Math.max(1, points))], points };
        };

        const updateGradePreview = () => {
            const result = calculateGrade(field('review_role').value);
            const preview = field('grade-preview');
            const recommendation = field('recommendation-preview');

            if (!result) {
                preview.textContent = 'Vyplňte všetky kritériá.';
                recommendation.textContent = '';
                return;
            }

            preview.textContent = `${result.letter} - ${gradeAdverbs[result.letter]} (skóre ${result.score.toFixed(2)})`;
            recommendation.textContent = result.points < 6 ? 'Prácu odporúčam k obhajobe.' : 'Prácu neodporúčam k obhajobe.';
        };

        const applyTypeAndRole = () => {
            const type = field('thesis_type').value;
            const role = field('review_role').value;
            const supervisor = field('supervisor_name');
            const isOpponent = role === 'opponent';

            document.querySelectorAll('[data-block]').forEach((section) => {
                const active = section.dataset.block in reviewConfig.roleBlocks[role];
                section.classList.toggle('hidden', !active);
                section.querySelectorAll('select, textarea').forEach((control) => { control.disabled = !active; });
            });

            document.querySelectorAll('[data-labels]').forEach((label) => {
                label.textContent = JSON.parse(label.dataset.labels)[`${type}:${role}`];
            });
            field('activity_title').textContent = `Aktivita ${reviewConfig.authorGenitive[type]}`;
            field('student_label').textContent = reviewConfig.authorNameLabel[type];

            const program = field('study_program');
            const selected = program.value;
            program.innerHTML = '<option value="">Vyberte študijný program</option>';
            reviewConfig.programs[type].forEach((name) => {
                program.add(new Option(name, name, false, name === selected));
            });

            field('opponent_field').classList.toggle('hidden', !isOpponent);
            field('supervisor_hint').classList.toggle('hidden', isOpponent);
            supervisor.readOnly = !isOpponent;
            supervisor.classList.toggle('bg-slate-50', !isOpponent);
            supervisor.classList.toggle('text-slate-600', !isOpponent);

            if (!isOpponent) {
                supervisor.value = reviewConfig.ownerName;
            } else if (supervisor.value === reviewConfig.ownerName) {
                supervisor.value = '';
            }

            updateGradePreview();
        };

        field('thesis_type').addEventListener('change', applyTypeAndRole);
        field('review_role').addEventListener('change', applyTypeAndRole);
        field('review_date').addEventListener('input', () => {
            field('academic_year').value = academicYearFor(field('review_date').value);
            checkForDuplicateReview();
        });
        reviewForm.addEventListener('change', (event) => {
            if (event.target.matches('[data-grade]')) {
                updateGradePreview();
            }
        });

        updateGradePreview();
    </script>
</x-app-layout>
