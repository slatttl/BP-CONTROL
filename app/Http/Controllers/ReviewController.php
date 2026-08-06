<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

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

    public function create(): View
    {
        return view('reviews.form', $this->formData(new Review()));
    }

    public function store(Request $request): RedirectResponse
    {
        $review = Review::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
        ]);

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

        return view('reviews.form', $this->formData($review));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->authorizeReview($request, $review);

        $review->update($this->validatedData($request));

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

    private function formData(Review $review): array
    {
        return [
            'review' => $review,
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
            'grades' => config('review.grades'),
            'recommendations' => config('review.recommendations'),
            'originalityStatuses' => config('review.originality_statuses'),
        ];
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
