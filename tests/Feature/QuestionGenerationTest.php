<?php

namespace Tests\Feature;

use App\Models\AiGenerationLog;
use App\Models\Article;
use App\Models\QuizAttempt;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuestionGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function article(): Article
    {
        return Article::create(['title' => 'Cá voi xanh', 'content' => str_repeat('Cá voi xanh là động vật có vú sống dưới biển. ', 10)]);
    }

    private function questions(): array
    {
        return array_map(fn ($i) => ['content' => 'Câu hỏi '.$i, 'options' => ['A '.$i, 'B '.$i, 'C '.$i, 'D '.$i], 'correct_index' => 1, 'explanation' => 'Giải thích '.$i], range(1, 5));
    }

    private function generate(Article $a)
    {
        return $this->post('/admin/ai', ['article_id' => $a->id, 'title' => 'Quiz cá voi', 'ocean_zone' => 'Tầng Ánh Sáng (0m - 200m)']);
    }

    private function fakeGemini(array $questions): void
    {
        config(['services.gemini.key' => 'test-secret', 'services.gemini.model' => 'gemini-3.5-flash-lite']);
        Http::fake(['*generateContent' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['questions' => $questions])]]]]]])]);
    }

    public function test_admin_only_and_all_management_pages_render(): void
    {
        $this->get('/admin/articles')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/ai')->assertRedirect('/dashboard');
        $a = $this->article();
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/articles', '/admin/articles/create', '/admin/articles/'.$a->id.'/edit', '/admin/wikipedia', '/admin/ai', '/admin/question-sets/create'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_wikipedia_search_preview_import_and_no_duplicate_or_overwrite(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*w/api.php*' => function ($request) {
            return Http::response(isset($request['list']) ? ['query' => ['search' => [['pageid' => 123, 'title' => 'Cá voi', 'snippet' => 'Một <span>loài</span> cá voi']]]] : ['query' => ['pages' => [['pageid' => 123, 'title' => 'Cá voi', 'ns' => 0, 'extract' => str_repeat('Bài đọc về cá voi. ', 20)]]]]);
        }]);
        $this->actingAs($this->admin())->get('/admin/wikipedia?search=ca')->assertOk()->assertSee('Xem trước bài này')->assertSee('Một loài cá voi');
        $this->get('/admin/wikipedia?page_id=123')->assertOk()->assertSee('Lưu bài vào thư viện');
        $this->post('/admin/wikipedia', ['page_id' => 123])->assertRedirect();
        $a = Article::firstOrFail();
        $a->update(['content' => 'Nội dung đã biên tập']);
        $this->post('/admin/wikipedia', ['page_id' => 123])->assertRedirect();
        $this->assertDatabaseCount('articles', 1);
        $this->assertSame('Nội dung đã biên tập', $a->fresh()->content);
    }

    public function test_valid_ai_creates_five_draft_questions_then_publish_and_keep_play_compatible(): void
    {
        $a = $this->article();
        $this->fakeGemini($this->questions());
        $this->actingAs($this->admin());
        $this->generate($a)->assertSessionHasNoErrors()->assertRedirect();
        $set = QuizSet::firstOrFail();
        $this->assertFalse($set->is_active);
        $this->assertCount(5, $set->questions);
        $this->assertSame(['A 1', 'B 1', 'C 1', 'D 1'], $set->questions->first()->options);
        $this->get('/admin/question-sets/'.$set->id.'/edit')->assertOk()->assertSee('Câu 5');
        $this->get('/quiz/play')->assertRedirect('/dashboard');
        $this->post('/admin/question-sets/'.$set->id.'/publish')->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($set->fresh()->is_active);
        $this->get('/quiz/play')->assertOk()->assertSee('Quiz cá voi');
        $this->assertDatabaseHas('ai_generation_logs', ['status' => 'success', 'quiz_set_id' => $set->id]);
        Http::assertSent(fn ($r) => $r->hasHeader('x-goog-api-key', 'test-secret') && ! str_contains($r->url(), 'test-secret'));
        $this->patch('/admin/articles/'.$a->id, ['title' => 'Đổi', 'content' => str_repeat('x', 100)])->assertStatus(422);
    }

    public function test_missing_key_provider_error_and_invalid_response_never_create_a_fake_quiz(): void
    {
        $a = $this->article();
        $this->actingAs($this->admin());
        Http::preventStrayRequests();
        config(['services.gemini.key' => null]);
        $this->generate($a)->assertSessionHasErrors('gemini');
        Http::assertNothingSent();
        config(['services.gemini.key' => 'test-secret']);
        Http::fake(['*' => Http::response(['error' => ['status' => 'PERMISSION_DENIED', 'message' => 'bad test-secret']], 403)]);
        $this->generate($a)->assertSessionHasErrors('gemini');
        $this->assertStringNotContainsString('test-secret', AiGenerationLog::latest('id')->first()->error_message);
        $bad = $this->questions();
        $bad[1]['options'][1] = $bad[1]['options'][0];
        $this->fakeGemini($bad);
        $this->generate($a)->assertSessionHasErrors('gemini');
        $this->assertDatabaseCount('quiz_sets', 0);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('ai_generation_logs', 3);
    }

    public function test_manual_draft_validation_edit_publish_unpublish_and_history_protection(): void
    {
        $a = $this->article();
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->post('/admin/question-sets', ['article_id' => $a->id, 'title' => 'Thủ công', 'ocean_zone' => 'Vùng biển'])->assertRedirect();
        $set = QuizSet::firstOrFail();
        $this->get('/admin/question-sets/'.$set->id.'/edit')->assertOk();
        $this->post('/admin/question-sets/'.$set->id.'/publish')->assertSessionHasErrors('questions');
        $questions = array_map(fn ($q) => ['question_text' => $q['content'], 'options' => $q['options'], 'correct_option_index' => $q['correct_index'], 'explanation' => $q['explanation']], $this->questions());
        $data = ['title' => 'Thủ công', 'ocean_zone' => 'Vùng biển', 'questions' => $questions];
        $this->patch('/admin/question-sets/'.$set->id, $data)->assertSessionHasNoErrors();
        $this->assertCount(5, $set->fresh()->questions);
        $this->post('/admin/question-sets/'.$set->id.'/publish')->assertSessionHasNoErrors();
        $this->patch('/admin/question-sets/'.$set->id, $data)->assertStatus(422);
        QuizAttempt::create(['user_id' => $admin->id, 'quiz_set_id' => $set->id, 'score' => 100, 'correct_count' => 1, 'total_questions' => 5, 'duration_seconds' => 10]);
        $this->post('/admin/question-sets/'.$set->id.'/unpublish')->assertRedirect();
        $this->patch('/admin/question-sets/'.$set->id, $data)->assertStatus(422);
        $this->delete('/admin/question-sets/'.$set->id)->assertStatus(422);
        $this->assertDatabaseHas('quiz_attempts', ['quiz_set_id' => $set->id, 'score' => 100]);
        $this->postJson('/quiz/submit', ['quiz_set_id' => $set->id, 'duration_seconds' => 10, 'answers' => []])->assertUnprocessable();
    }

    public function test_migration_backfills_existing_content_without_changing_publication_or_scores(): void
    {
        $migration = require database_path('migrations/2026_11_03_000001_add_question_generation.php');
        $migration->down();
        $admin = $this->admin();
        $id = DB::table('quiz_sets')->insertGetId(['title' => 'Bộ cũ', 'topic' => 'Biển', 'ocean_zone' => 'Biển', 'reading_passage' => 'Nội dung cũ', 'is_active' => true, 'created_by' => $admin->id]);
        $attempt = QuizAttempt::create(['user_id' => $admin->id, 'quiz_set_id' => $id, 'score' => 999, 'correct_count' => 1, 'total_questions' => 5, 'duration_seconds' => 10]);
        $migration->up();
        $set = QuizSet::findOrFail($id);
        $this->assertTrue($set->is_active);
        $this->assertSame('Nội dung cũ', $set->article->content);
        $this->assertSame(999, $attempt->fresh()->score);
    }

    public function test_fresh_seed_has_saved_articles_and_existing_play_still_renders(): void
    {
        $this->seed();
        $this->assertDatabaseCount('articles', 2);
        $this->assertSame(0, QuizSet::whereNull('article_id')->count());
        $this->actingAs(User::where('email', 'admin@ocean.io')->firstOrFail())->get('/quiz/play')->assertOk();
    }
}