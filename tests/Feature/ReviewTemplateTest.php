<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $type, string $role, array $overrides = []): array
    {
        $data = [
            'thesis_type' => $type,
            'review_role' => $role,
            'student_name' => 'Patrik Beneš',
            'study_program' => config("review.thesis_types.{$type}.programs.0"),
            'thesis_title' => 'Návrh a realizácia riadiaceho systému',
            'supervisor_name' => 'doc. Ing. Eduard Nemlaha, PhD.',
            'place' => 'Trnava',
            'review_date' => '2026-06-14',
            'originality_percentage' => 6.91,
            'originality_status' => 'vyhovujuca',
            'questions' => 'Prečo?',
            'originality_comment' => 'Práca je autorská.',
        ];

        foreach (array_keys(config('review.role_blocks.'.$role)) as $block) {
            foreach (array_keys(config("review.blocks.{$block}.criteria")) as $field) {
                $data[$field] = 'A';
            }
            $data[config("review.blocks.{$block}.comment")] = 'Komentár.';
        }

        return [...$data, ...$overrides];
    }

    public function test_academic_year_is_derived_from_the_review_date(): void
    {
        $this->assertSame('2025/2026', Review::academicYearFor(now()->setDate(2026, 6, 14)));
        $this->assertSame('2026/2027', Review::academicYearFor(now()->setDate(2026, 9, 1)));
        $this->assertSame('2025/2026', Review::academicYearFor(now()->setDate(2026, 8, 31)));
    }

    public function test_supervisor_review_uses_logged_in_user_and_computed_grade(): void
    {
        $user = User::factory()->create(['name' => 'Ing. Prihlásený']);

        $this->actingAs($user)
            ->post(route('reviews.store'), $this->payload('bachelor', 'supervisor', [
                'supervisor_name' => 'Podvrhnuté meno',
                'academic_year' => '1999/2000',
                'final_grade' => 'FX',
            ]))
            ->assertRedirect();

        $review = Review::firstOrFail();
        $this->assertSame('Ing. Prihlásený', $review->supervisor_name);
        $this->assertNull($review->opponent_name);
        $this->assertSame('2025/2026', $review->academic_year);
        $this->assertSame('A', $review->final_grade);
        $this->assertSame('recommend', $review->final_recommendation);
    }

    public function test_opponent_review_uses_logged_in_user_as_opponent_and_skips_activity(): void
    {
        $user = User::factory()->create(['name' => 'prof. Oponent']);

        $this->actingAs($user)
            ->post(route('reviews.store'), $this->payload('master', 'opponent', [
                'quality_topic_quality' => 'E',
            ]))
            ->assertRedirect();

        $review = Review::firstOrFail();
        $this->assertSame('prof. Oponent', $review->opponent_name);
        $this->assertSame('doc. Ing. Eduard Nemlaha, PhD.', $review->supervisor_name);
        $this->assertNull($review->activity_independence);
        $this->assertSame('E', $review->final_grade);
    }

    public function test_failing_critical_criterion_gives_not_recommended_fx(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reviews.store'), $this->payload('bachelor', 'supervisor', ['quality_topic_completeness' => 'FX']));

        $review = Review::firstOrFail();
        $this->assertSame('FX', $review->final_grade);
        $this->assertSame('not_recommend', $review->final_recommendation);
    }

    public function test_every_text_field_and_study_program_is_required(): void
    {
        $user = User::factory()->create();

        foreach (['questions', 'originality_comment', 'quality_comment', 'activity_comment', 'student_name', 'thesis_title'] as $field) {
            $this->actingAs($user)
                ->post(route('reviews.store'), $this->payload('bachelor', 'supervisor', [$field => '']))
                ->assertSessionHasErrors($field);
        }

        $this->actingAs($user)
            ->post(route('reviews.store'), $this->payload('bachelor', 'supervisor', ['study_program' => 'Neexistujúci program']))
            ->assertSessionHasErrors('study_program');

        $this->actingAs($user)
            ->post(route('reviews.store'), $this->payload('bachelor', 'opponent', ['supervisor_name' => '']))
            ->assertSessionHasErrors('supervisor_name');

        $this->assertSame(0, Review::count());
    }

    public function test_study_program_must_match_the_thesis_type(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reviews.store'), $this->payload('master', 'supervisor', [
                'study_program' => 'B-MCHT mechatronika',
            ]))
            ->assertSessionHasErrors('study_program');
    }

    public function test_all_four_variants_render_form_show_and_pdf(): void
    {
        $user = User::factory()->create();

        foreach (['bachelor', 'master'] as $type) {
            foreach (['supervisor', 'opponent'] as $role) {
                $this->actingAs($user)
                    ->post(route('reviews.store'), $this->payload($type, $role, ['student_name' => "{$type}-{$role}"]))
                    ->assertSessionHasNoErrors();

                $review = Review::where('student_name', "{$type}-{$role}")->firstOrFail();

                $this->actingAs($user)->get(route('reviews.show', $review))->assertOk();
                $this->actingAs($user)->get(route('reviews.edit', $review))->assertOk();
                $pdfResponse = $this->actingAs($user)
                    ->get(route('reviews.pdf', $review))
                    ->assertOk();
                $pdfContent = $pdfResponse->getContent();
                $this->assertStringContainsString('LiberationSerif', $pdfContent);
                $this->assertSame(3, preg_match_all('/\/Type\s*\/Page(?!s)\b/', $pdfContent));
            }
        }

        $this->actingAs($user)->get(route('reviews.create'))->assertOk();
        $this->actingAs($user)->get(route('reviews.index', ['thesis_type' => 'master', 'review_role' => 'opponent']))->assertOk();
    }

    public function test_pdf_is_streamed_inline_unless_download_is_requested(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('reviews.store'), $this->payload('bachelor', 'supervisor'));
        $review = Review::firstOrFail();

        $this->actingAs($user)->get(route('reviews.pdf', $review))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename='.\Illuminate\Support\Str::slug($review->student_name).'-posudok.pdf');
        $this->assertStringStartsWith('attachment', $this->actingAs($user)
            ->get(route('reviews.pdf', ['review' => $review, 'download' => 1]))
            ->headers->get('Content-Disposition'));
    }

    public function test_pdf_does_not_truncate_long_comments(): void
    {
        $user = User::factory()->create();
        $marker = 'KONIECTEXTU';
        $this->actingAs($user)->post(route('reviews.store'), $this->payload('bachelor', 'supervisor', [
            'literature_comment' => str_repeat('slovo ', 200).$marker,
        ]));
        $review = Review::firstOrFail();

        $html = view('reviews.pdf', [
            'review' => $review,
            'grades' => config('review.grades'),
            'gradeAdverbs' => config('review.grade_adverbs'),
            'originalityStatuses' => config('review.originality_statuses'),
        ])->render();

        $this->assertStringContainsString($marker, $html);
    }

    public function test_unsaved_form_values_can_be_previewed_as_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.preview'), $this->payload('master', 'opponent'))
            ->assertOk();

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertSame(0, Review::count());

        $this->actingAs($user)
            ->post(route('reviews.preview'), ['review_role' => 'opponent'])
            ->assertOk();
    }

    public function test_opening_the_preview_url_directly_uses_the_draft_instead_of_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('reviews.preview'))->assertRedirect(route('reviews.create'));

        $this->actingAs($user)->post(route('reviews.draft.store'), $this->payload('bachelor', 'supervisor'))->assertOk();
        $response = $this->actingAs($user)->get(route('reviews.preview'))->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
