<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewDraft;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use ZipArchive;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $gradeValues = array_keys(config('review.grades'));
        $selectedGrade = $request->string('grade')->toString();
        $search = trim($request->string('q')->toString());
        $academicYear = trim($request->string('academic_year')->toString());
        $studyProgram = trim($request->string('study_program')->toString());
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $selectedScope = $isAdmin && $request->string('scope')->toString() === 'all' ? 'all' : 'mine';

        if (! in_array($selectedGrade, $gradeValues, true)) {
            $selectedGrade = null;
        }

        if (! preg_match('/^\d{4}\/\d{4}$/', $academicYear)) {
            $academicYear = null;
        }

        if ($dateFrom !== '' && ! strtotime($dateFrom)) {
            $dateFrom = null;
        }

        if ($dateTo !== '' && ! strtotime($dateTo)) {
            $dateTo = null;
        }

        $baseQuery = Review::query()
            ->with('user:id,name,email,is_admin')
            ->when($selectedScope === 'mine', fn (Builder $query) => $query->where('user_id', $user->id));

        $reviewsQuery = (clone $baseQuery)
            ->when($selectedGrade, fn ($query, $grade) => $query->where('final_grade', $grade))
            ->when($academicYear, fn ($query, $value) => $query->where('academic_year', $value))
            ->when($studyProgram, fn ($query, $value) => $query->where('study_program', $value))
            ->when($dateFrom, fn ($query, $value) => $query->whereDate('review_date', '>=', $value))
            ->when($dateTo, fn ($query, $value) => $query->whereDate('review_date', '<=', $value))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($where) use ($search) {
                    $where->where('student_name', 'like', "%{$search}%")
                        ->orWhere('thesis_title', 'like', "%{$search}%");
                });
            });

        return view('reviews.index', [
            'reviews' => $reviewsQuery->latest()->paginate(10)->withQueryString(),
            'drafts' => ReviewDraft::query()
                ->where('user_id', $user->id)
                ->where(function (Builder $query) {
                    $query->where('draft_key', 'new')->orWhereNotNull('review_id');
                })
                ->latest('updated_at')
                ->get(),
            'grades' => config('review.grades'),
            'selectedGrade' => $selectedGrade,
            'totalReviews' => (clone $baseQuery)->count(),
            'filteredReviews' => (clone $reviewsQuery)->count(),
            'isAdmin' => $isAdmin,
            'selectedScope' => $selectedScope,
            'selectedAcademicYear' => $academicYear,
            'selectedStudyProgram' => $studyProgram,
            'selectedDateFrom' => $dateFrom,
            'selectedDateTo' => $dateTo,
            'search' => $search,
            'yearOptions' => (clone $baseQuery)
                ->select('academic_year')
                ->distinct()
                ->orderByDesc('academic_year')
                ->pluck('academic_year'),
            'programOptions' => (clone $baseQuery)
                ->select('study_program')
                ->distinct()
                ->orderBy('study_program')
                ->pluck('study_program'),
        ]);
    }

    public function create(Request $request): View
    {
        $review = new Review;
        $draft = ReviewDraft::query()
            ->where('user_id', $request->user()->id)
            ->where('draft_key', 'new')
            ->first();

        $review->fill($draft?->payload ?? []);

        return view('reviews.form', $this->formData($review, $draft));
    }

    public function store(Request $request): RedirectResponse
    {
        $review = Review::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
        ]);
        $this->deleteDraft($request, 'new');

        return redirect()
            ->route('reviews.show', $review)
            ->with([
                'status' => 'Posudok bol ulozeny a pripraveny na export PDF.',
                'status_type' => 'success',
                'status_title' => 'Ulozene',
            ]);
    }

    public function show(Request $request, Review $review): View
    {
        $this->authorizeReview($request, $review);

        return view('reviews.show', $this->formData($review));
    }

    public function edit(Request $request, Review $review): View
    {
        $this->authorizeReview($request, $review);

        $draft = ReviewDraft::query()
            ->where('user_id', $request->user()->id)
            ->where('draft_key', 'review:'.$review->id)
            ->first();

        if ($draft) {
            $review->fill($draft->payload);
        }

        return view('reviews.form', $this->formData($review, $draft));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->authorizeReview($request, $review);

        $review->update($this->validatedData($request));
        $this->deleteDraft($request, 'review:'.$review->id);

        return redirect()
            ->route('reviews.show', $review)
            ->with([
                'status' => 'Posudok bol aktualizovany.',
                'status_type' => 'success',
                'status_title' => 'Aktualizovane',
            ]);
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->authorizeReview($request, $review);

        ReviewDraft::query()->where('review_id', $review->id)->delete();
        $review->delete();

        return redirect()
            ->route('reviews.index')
            ->with([
                'status' => 'Posudok bol odstraneny.',
                'status_type' => 'warning',
                'status_title' => 'Odstranene',
            ]);
    }

    public function duplicate(Request $request, Review $review): RedirectResponse
    {
        $this->authorizeReview($request, $review);

        $copy = $review->replicate();
        $copy->user_id = $request->user()->id;
        $copy->student_name = $review->student_name.' (kopia)';
        $copy->review_date = now()->toDateString();
        $copy->save();

        return redirect()
            ->route('reviews.edit', $copy)
            ->with([
                'status' => 'Vytvorila sa kopia posudku. Skontroluj a uloz upravy.',
                'status_type' => 'success',
                'status_title' => 'Kopia vytvorena',
            ]);
    }

    public function pdf(Request $request, Review $review)
    {
        $this->authorizeReview($request, $review);

        $fileName = Str::slug($review->student_name ?: 'posudok').'-posudok.pdf';

        return Pdf::loadView('reviews.pdf', $this->formData($review))
            ->setPaper('a4')
            ->download($fileName);
    }

    public function saveDraft(Request $request, ?Review $review = null): JsonResponse
    {
        if ($review) {
            $this->authorizeReview($request, $review);
            $draftKey = 'review:'.$review->id;
        } else {
            $draftKey = 'new';
        }

        $payload = $request->validate($this->draftRules());
        $draft = ReviewDraft::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'draft_key' => $draftKey,
            ],
            [
                'review_id' => $review?->id,
                'payload' => $payload,
            ],
        );

        return response()->json([
            'saved_at' => $draft->updated_at->toIso8601String(),
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'review_id' => ['nullable', 'integer'],
        ]);

        $query = Review::query()
            ->where('academic_year', trim($data['academic_year']));

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if (! empty($data['review_id'])) {
            $currentReview = Review::query()->findOrFail($data['review_id']);
            $this->authorizeReview($request, $currentReview);
            $query->where('id', '!=', $currentReview->id);
        }

        $studentName = mb_strtolower(trim($data['student_name']));
        $duplicate = $query->pluck('student_name')
            ->contains(fn (string $name) => mb_strtolower(trim($name)) === $studentName);

        return response()->json(['duplicate' => $duplicate]);
    }

    public function bulkCsv(Request $request)
    {
        $reviews = $this->selectedReviews($request);
        $fileName = 'posudky-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reviews) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Meno študenta', 'Názov práce', 'Študijný program', 'Akademický rok', 'Známka', 'Dátum'], ';', '"', '\\');

            foreach ($reviews as $review) {
                fputcsv($output, [
                    $this->safeCsvValue($review->student_name),
                    $this->safeCsvValue($review->thesis_title),
                    $this->safeCsvValue($review->study_program),
                    $this->safeCsvValue($review->academic_year),
                    $review->final_grade,
                    $review->review_date?->format('d.m.Y'),
                ], ';', '"', '\\');
            }

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function bulkPdf(Request $request)
    {
        $reviews = $this->selectedReviews($request);
        $archivePath = tempnam(sys_get_temp_dir(), 'bp-reviews-');

        if ($archivePath === false) {
            throw new RuntimeException('Nepodarilo sa vytvoriť dočasný súbor pre ZIP archív.');
        }

        $archive = new ZipArchive;
        $result = $archive->open($archivePath, ZipArchive::OVERWRITE);

        if ($result !== true) {
            unlink($archivePath);
            throw new RuntimeException('Nepodarilo sa otvoriť ZIP archív (kód '.$result.').');
        }

        try {
            foreach ($reviews as $review) {
                $fileName = Str::slug($review->student_name ?: 'posudok').'-'.$review->id.'-posudok.pdf';
                $pdf = Pdf::loadView('reviews.pdf', $this->formData($review))
                    ->setPaper('a4')
                    ->output();

                if (! $archive->addFromString($fileName, $pdf)) {
                    throw new RuntimeException('Nepodarilo sa pridať PDF posudku do ZIP archívu.');
                }
            }
        } catch (\Throwable $exception) {
            $archive->close();
            unlink($archivePath);
            throw $exception;
        }

        if (! $archive->close()) {
            unlink($archivePath);
            throw new RuntimeException('Nepodarilo sa dokončiť ZIP archív.');
        }

        return response()->download($archivePath, 'posudky-'.now()->format('Ymd-His').'.zip')
            ->deleteFileAfterSend(true);
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }

    private function selectedReviews(Request $request)
    {
        $data = $request->validate([
            'review_ids' => ['required', 'array', 'min:1', 'max:50'],
            'review_ids.*' => ['required', 'integer', 'distinct', 'exists:reviews,id'],
        ]);
        $query = Review::query()->whereKey($data['review_ids']);

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        $reviews = $query->get();
        abort_unless($reviews->count() === count($data['review_ids']), 403);

        return $reviews;
    }

    private function deleteDraft(Request $request, string $draftKey): void
    {
        ReviewDraft::query()
            ->where('user_id', $request->user()->id)
            ->where('draft_key', $draftKey)
            ->delete();
    }

    private function formData(Review $review, ?ReviewDraft $draft = null): array
    {
        return [
            'review' => $review,
            'draft' => $draft,
            'draftSaveUrl' => $review->exists
                ? route('reviews.draft.update', $review)
                : route('reviews.draft.store'),
            'duplicateCheckUrl' => route('reviews.duplicate-check'),
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
            'grades' => config('review.grades'),
            'recommendations' => config('review.recommendations'),
            'originalityStatuses' => config('review.originality_statuses'),
        ];
    }

    private function draftRules(): array
    {
        $gradeValues = array_keys(config('review.grades'));
        $rules = [
            'academic_year' => ['sometimes', 'nullable', 'string', 'max:20'],
            'student_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'study_program' => ['sometimes', 'nullable', 'string', 'max:255'],
            'thesis_title' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'supervisor_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'review_date' => ['sometimes', 'nullable', 'date'],
            'originality_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'originality_status' => ['sometimes', 'nullable', Rule::in(array_keys(config('review.originality_statuses')))],
            'final_recommendation' => ['sometimes', 'nullable', Rule::in(array_keys(config('review.recommendations')))],
            'final_grade' => ['sometimes', 'nullable', Rule::in($gradeValues)],
        ];

        foreach ([
            'activity_independence', 'activity_creativity', 'quality_overall_concept',
            'quality_topic_completeness', 'quality_topic_quality', 'quality_methods',
            'quality_complexity', 'quality_practicality', 'literature_sorting',
            'literature_usage', 'literature_conclusions', 'formal_logic', 'formal_style',
            'formal_terminology', 'formal_graphics',
        ] as $field) {
            $rules[$field] = ['sometimes', 'nullable', Rule::in($gradeValues)];
        }

        foreach ([
            'activity_comment', 'quality_comment', 'literature_comment', 'formal_comment',
            'questions', 'originality_comment', 'author_statement',
        ] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'string', 'max:50000'];
        }

        return $rules;
    }

    private function validatedData(Request $request): array
    {
        $gradeValues = array_keys(config('review.grades'));

        return $request->validate([
            'academic_year' => ['required', 'string', 'max:20'],
            'student_name' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'thesis_title' => ['required', 'string'],
            'supervisor_name' => ['required', 'string', 'max:255'],
            'place' => ['required', 'string', 'max:255'],
            'review_date' => ['required', 'date'],
            'originality_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'originality_status' => ['required', Rule::in(array_keys(config('review.originality_statuses')))],
            'activity_independence' => ['required', Rule::in($gradeValues)],
            'activity_creativity' => ['required', Rule::in($gradeValues)],
            'activity_comment' => ['nullable', 'string'],
            'quality_overall_concept' => ['required', Rule::in($gradeValues)],
            'quality_topic_completeness' => ['required', Rule::in($gradeValues)],
            'quality_topic_quality' => ['required', Rule::in($gradeValues)],
            'quality_methods' => ['required', Rule::in($gradeValues)],
            'quality_complexity' => ['required', Rule::in($gradeValues)],
            'quality_practicality' => ['required', Rule::in($gradeValues)],
            'quality_comment' => ['nullable', 'string'],
            'literature_sorting' => ['required', Rule::in($gradeValues)],
            'literature_usage' => ['required', Rule::in($gradeValues)],
            'literature_conclusions' => ['required', Rule::in($gradeValues)],
            'literature_comment' => ['nullable', 'string'],
            'formal_logic' => ['required', Rule::in($gradeValues)],
            'formal_style' => ['required', Rule::in($gradeValues)],
            'formal_terminology' => ['required', Rule::in($gradeValues)],
            'formal_graphics' => ['required', Rule::in($gradeValues)],
            'formal_comment' => ['nullable', 'string'],
            'final_recommendation' => ['required', Rule::in(array_keys(config('review.recommendations')))],
            'questions' => ['nullable', 'string'],
            'originality_comment' => ['nullable', 'string'],
            'final_grade' => ['required', Rule::in($gradeValues)],
            'author_statement' => ['required', 'string'],
        ]);
    }

    private function authorizeReview(Request $request, Review $review): void
    {
        abort_unless($request->user()->isAdmin() || $review->user_id === $request->user()->id, 403);
    }
}
