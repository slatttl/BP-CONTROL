<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\ReviewDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewEfficiencyFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_draft_is_saved_and_restored_for_its_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('reviews.draft.store'), [
                'student_name' => 'Jana Nováková',
                'academic_year' => '2026/2027',
            ])
            ->assertOk();

        $this->assertDatabaseHas('review_drafts', [
            'user_id' => $user->id,
            'draft_key' => 'new',
        ]);

        $this->actingAs($user)
            ->get(route('reviews.create'))
            ->assertOk()
            ->assertSee('Jana Nováková')
            ->assertSee('2026/2027');
    }

    public function test_duplicate_check_matches_student_and_year_and_excludes_current_review(): void
    {
        $user = User::factory()->create();
        $review = Review::create($this->validReviewData($user));

        $this->actingAs($user)
            ->getJson(route('reviews.duplicate-check', [
                'student_name' => ' JANA NOVÁKOVÁ ',
                'academic_year' => '2026/2027',
            ]))
            ->assertOk()
            ->assertJson(['duplicate' => true]);

        $this->actingAs($user)
            ->getJson(route('reviews.duplicate-check', [
                'student_name' => $review->student_name,
                'academic_year' => $review->academic_year,
                'review_id' => $review->id,
            ]))
            ->assertOk()
            ->assertJson(['duplicate' => false]);
    }

    public function test_selected_reviews_can_be_exported_to_csv(): void
    {
        $user = User::factory()->create();
        $review = Review::create($this->validReviewData($user));

        $response = $this->actingAs($user)->post(route('reviews.bulk.csv'), [
            'review_ids' => [$review->id],
        ]);

        $response->assertDownload();
        $this->assertStringContainsString('Jana Nováková', $response->streamedContent());
    }

    public function test_selected_reviews_can_be_downloaded_as_a_pdf_zip(): void
    {
        $user = User::factory()->create();
        $review = Review::create($this->validReviewData($user));

        $response = $this->actingAs($user)->post(route('reviews.bulk.pdf'), [
            'review_ids' => [$review->id],
        ]);

        $response->assertDownload();

        $archive = new \ZipArchive;
        $archivePath = $response->baseResponse->getFile()->getPathname();

        try {
            $this->assertSame(true, $archive->open($archivePath));
            $this->assertSame(1, $archive->numFiles);
            $this->assertStringEndsWith('.pdf', $archive->getNameIndex(0));
        } finally {
            $archive->close();
            if (is_file($archivePath)) {
                unlink($archivePath);
            }
        }
    }

    public function test_user_cannot_bulk_export_another_users_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::create($this->validReviewData($owner));

        $this->actingAs($otherUser)
            ->post(route('reviews.bulk.csv'), ['review_ids' => [$review->id]])
            ->assertForbidden();
    }

    public function test_saving_a_complete_review_removes_its_new_draft(): void
    {
        $user = User::factory()->create();
        ReviewDraft::create([
            'user_id' => $user->id,
            'draft_key' => 'new',
            'payload' => ['student_name' => 'Jana Nováková'],
        ]);

        $this->actingAs($user)
            ->post(route('reviews.store'), $this->validReviewData($user))
            ->assertRedirect();

        $this->assertDatabaseMissing('review_drafts', [
            'user_id' => $user->id,
            'draft_key' => 'new',
        ]);
    }

    public function test_csv_export_neutralizes_formula_values(): void
    {
        $user = User::factory()->create();
        $data = $this->validReviewData($user);
        $data['thesis_title'] = '=HYPERLINK("https://example.test")';
        $review = Review::create($data);

        $response = $this->actingAs($user)->post(route('reviews.bulk.csv'), [
            'review_ids' => [$review->id],
        ]);

        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
    }

    private function validReviewData(User $user): array
    {
        return [
            'user_id' => $user->id,
            'thesis_type' => 'bachelor',
            'review_role' => 'supervisor',
            'academic_year' => '2026/2027',
            'student_name' => 'Jana Nováková',
            'study_program' => 'B-PIAR priemyselná informatika, automatizácia a robotika',
            'thesis_title' => 'Efektívne spracovanie dát',
            'supervisor_name' => 'Vedúci práce',
            'place' => 'Trnava',
            'review_date' => '2026-10-06',
            'originality_percentage' => 4,
            'originality_status' => 'vyhovujuca',
            'activity_independence' => 'A',
            'activity_creativity' => 'B',
            'activity_comment' => 'Dobrá práca.',
            'quality_overall_concept' => 'A',
            'quality_topic_completeness' => 'A',
            'quality_topic_quality' => 'A',
            'quality_methods' => 'A',
            'quality_complexity' => 'B',
            'quality_practicality' => 'A',
            'quality_comment' => 'Kvalitné spracovanie.',
            'literature_sorting' => 'A',
            'literature_usage' => 'A',
            'literature_conclusions' => 'B',
            'literature_comment' => 'Dostatočné zdroje.',
            'formal_logic' => 'A',
            'formal_style' => 'A',
            'formal_terminology' => 'A',
            'formal_graphics' => 'B',
            'formal_comment' => 'Prehľadné.',
            'final_recommendation' => 'recommend',
            'questions' => 'Bez otázok.',
            'originality_comment' => 'V poriadku.',
            'final_grade' => 'A',
            'author_statement' => 'Práca je autorská.',
        ];
    }
}
