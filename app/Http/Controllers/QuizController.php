<?php

// [Thành viên 2 - Contributor A] app/Http/Controllers/QuizController.php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    /**
     * Bốc ngẫu nhiên 1 bộ đề đang kích hoạt kèm danh sách câu hỏi.
     */
    public function play()
    {
        $quizSet = QuizSet::with('questions')
            ->where('is_active', true)
            ->has('questions')
            ->inRandomOrder()
            ->first();

        if (! $quizSet) {
            return redirect()->route('dashboard')
                ->with('error', 'Chưa có bộ đề nào được xuất bản. Hãy quay lại sau.');
        }

        return view('quiz.read', compact('quizSet'));
    }

    /**
     * Bước 1 → 2: tạo lượt làm bài mới rồi chuyển sang câu hỏi đầu tiên.
     */
    public function start(Request $request, QuizSet $quizSet)
    {
        abort_unless($quizSet->is_active, 404);

        $totalQuestions = $quizSet->questions()->count();
        abort_if($totalQuestions === 0, 404);

        $attempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_set_id' => $quizSet->id,
            'score' => 0,
            'correct_count' => 0,
            'total_questions' => $totalQuestions,
            'duration_seconds' => 0,
        ]);

        $request->session()->put($this->shownKey($attempt), []);

        return redirect()->route('quiz.question', $attempt);
    }

    /**
     * Bước 2: hiển thị câu hỏi chưa trả lời tiếp theo.
     * Server giữ mốc thời gian bắt đầu câu hỏi; quá hạn thì tự ghi nhận "hết giờ".
     */
    public function question(Request $request, QuizAttempt $attempt)
    {
        $this->guardAttempt($attempt);

        if ($attempt->answers()->count() >= $attempt->total_questions) {
            return redirect()->route('quiz.result', $attempt);
        }

        $questions = $attempt->quizSet->questions()->orderBy('id')->get();
        $answeredIds = $attempt->answers()->pluck('question_id')->all();

        $question = $questions->first(
            fn (Question $candidate) => ! in_array($candidate->id, $answeredIds, true)
        );

        if (! $question) {
            return redirect()->route('quiz.result', $attempt);
        }

        $timeLimit = max(1, (int) $question->time_limit);
        $shownKey = $this->shownKey($attempt);
        $shown = $request->session()->get($shownKey, []);

        if (! isset($shown[$question->id])) {
            $shown[$question->id] = time();
            $request->session()->put($shownKey, $shown);
        }

        $elapsed = max(0, time() - (int) $shown[$question->id]);

        if ($elapsed >= $timeLimit) {
            $this->recordAnswer($attempt, $question, null, $timeLimit);

            return redirect()->route('quiz.question', $attempt);
        }

        $remaining = max(0, $timeLimit - $elapsed);
        $number = $questions->search(fn (Question $item) => $item->id === $question->id) + 1;
        $total = $questions->count();
        $currentPoints = (int) $attempt->answers()->sum('points_earned');
        $quizSet = $attempt->quizSet;

        return view('quiz.question', compact(
            'attempt',
            'quizSet',
            'question',
            'number',
            'total',
            'remaining',
            'timeLimit',
            'currentPoints'
        ));
    }

    /**
     * Nhận đáp án của một câu hỏi (form thường, không JavaScript), tính điểm phía server.
     */
    public function answer(Request $request, QuizAttempt $attempt)
    {
        $this->guardAttempt($attempt);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option_index' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $question = $attempt->quizSet->questions()->findOrFail($data['question_id']);
        $existing = $attempt->answers()->where('question_id', $question->id)->first();

        if ($existing) {
            return redirect()->route('quiz.answer-result', [$attempt, $existing]);
        }

        $timeLimit = max(1, (int) $question->time_limit);
        $shown = $request->session()->get($this->shownKey($attempt), []);
        $startedAt = (int) ($shown[$question->id] ?? time());
        $secondsTaken = min($timeLimit, max(0, time() - $startedAt));

        $answer = $this->recordAnswer(
            $attempt,
            $question,
            (int) $data['selected_option_index'],
            $secondsTaken
        );

        return redirect()->route('quiz.answer-result', [$attempt, $answer]);
    }

    /**
     * Kết quả từng câu: đúng/sai, đáp án đúng, giải thích, nguồn.
     */
    public function answerResult(QuizAttempt $attempt, QuizAttemptAnswer $answer)
    {
        $this->guardAttempt($attempt);
        abort_unless($answer->quiz_attempt_id === $attempt->id, 404);

        $question = $answer->question;
        $quizSet = $attempt->quizSet;
        $nextUrl = $attempt->answers()->count() >= $attempt->total_questions
            ? route('quiz.result', $attempt)
            : route('quiz.question', $attempt);

        return view('quiz.answer-result', compact(
            'attempt',
            'answer',
            'question',
            'quizSet',
            'nextUrl'
        ));
    }

    /**
     * Kết quả cả bài: điểm, số câu đúng, tổng thời gian và bảng chi tiết từng câu.
     */
    public function result(QuizAttempt $attempt)
    {
        $this->guardAttempt($attempt);

        if ($attempt->answers()->count() < $attempt->total_questions) {
            return redirect()->route('quiz.question', $attempt);
        }

        $answers = $attempt->answers()
            ->with('question')
            ->get()
            ->sortBy(fn (QuizAttemptAnswer $item) => $item->question->id)
            ->values();

        $quizSet = $attempt->quizSet;
        $mistakeCount = $answers->where('is_correct', false)->count();
        $durationSeconds = (int) $attempt->duration_seconds;

        return view('quiz.result', compact(
            'attempt',
            'answers',
            'quizSet',
            'mistakeCount',
            'durationSeconds'
        ));
    }

    /**
     * Ghi nhận một câu trả lời (kể cả hết giờ) và cộng điểm vào lượt làm bài.
     */
    private function recordAnswer(
        QuizAttempt $attempt,
        Question $question,
        ?int $selectedOptionIndex,
        float $secondsTaken
    ): QuizAttemptAnswer {
        $timeLimit = max(1, (int) $question->time_limit);
        $secondsTaken = min(max($secondsTaken, 0), $timeLimit);
        $isCorrect = $selectedOptionIndex !== null
            && $selectedOptionIndex === (int) $question->correct_option_index;

        $points = 0;
        if ($isCorrect) {
            $speedFactor = 1 - (($secondsTaken / $timeLimit) * 0.5);
            $points = (int) round(1000 * max($speedFactor, 0.5));
        }

        $answer = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_index' => $selectedOptionIndex,
            'is_correct' => $isCorrect,
            'time_taken' => $secondsTaken,
            'points_earned' => $points,
        ]);

        $attempt->score += $points;
        $attempt->correct_count += $isCorrect ? 1 : 0;
        $attempt->duration_seconds += (int) round($secondsTaken);
        $attempt->save();

        if ($attempt->answers()->count() >= $attempt->total_questions) {
            $attempt->user->increment('total_score', $attempt->score);
            $attempt->user->increment('quizzes_completed', 1);
        }

        return $answer;
    }

    private function shownKey(QuizAttempt $attempt): string
    {
        return 'quiz.attempt.'.$attempt->id.'.shown';
    }

    private function guardAttempt(QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
    }

    /**
     * Nhận kết quả từ Kahoot UI qua AJAX, tính điểm và cập nhật Global Leaderboard.
     * Giữ nguyên để tương thích với bản backend đã nộp trước đó.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'quiz_set_id' => ['required', Rule::exists('quiz_sets', 'id')->where('is_active', true)],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.selected_option_index' => ['nullable', 'integer'],
            'answers.*.time_taken' => ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();
        $totalScore = 0;
        $correctCount = 0;
        $totalQuestions = count($validated['answers']);

        DB::beginTransaction();
        try {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_set_id' => $validated['quiz_set_id'],
                'score' => 0,
                'correct_count' => 0,
                'total_questions' => $totalQuestions,
                'duration_seconds' => $validated['duration_seconds'],
            ]);

            foreach ($validated['answers'] as $ansData) {
                $question = Question::find($ansData['question_id']);
                $selectedIdx = $ansData['selected_option_index'];
                $timeTaken = min((float) $ansData['time_taken'], (float) $question->time_limit);

                $isCorrect = ($selectedIdx !== null && (int) $selectedIdx === (int) $question->correct_option_index);

                // Công thức tính điểm chuẩn Kahoot: Tối đa 1000 điểm/câu, giảm dần theo thời gian phản xạ (tối thiểu 500 điểm nếu đúng)
                $pointsEarned = 0;
                if ($isCorrect) {
                    $correctCount++;
                    $timeLimit = max($question->time_limit, 1);
                    $speedFactor = 1 - (($timeTaken / $timeLimit) * 0.5);
                    $pointsEarned = (int) round(1000 * max($speedFactor, 0.5));
                    $totalScore += $pointsEarned;
                }

                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_index' => $selectedIdx,
                    'is_correct' => $isCorrect,
                    'time_taken' => $timeTaken,
                    'points_earned' => $pointsEarned,
                ]);
            }

            $attempt->update([
                'score' => $totalScore,
                'correct_count' => $correctCount,
            ]);

            // Cập nhật điểm tích lũy và số bài đã hoàn thành của User cho Global Leaderboard
            $user->increment('total_score', $totalScore);
            $user->increment('quizzes_completed', 1);

            DB::commit();

            return response()->json([
                'success' => true,
                'score' => $totalScore,
                'correct_count' => $correctCount,
                'total_questions' => $totalQuestions,
                'redirect_url' => route('dashboard'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attempt: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tính năng Review Mistakes: Tự động gom các câu trả lời sai trong 5 lần làm bài gần nhất
     */
    public function mistakes()
    {
        $user = Auth::user();

        // Lấy ID của 5 lần làm bài gần nhất
        $lastFiveAttemptIds = QuizAttempt::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->pluck('id');

        // Lấy tất cả câu trả lời sai trong 5 lần đó, kèm thông tin Question và QuizSet
        $wrongAnswers = QuizAttemptAnswer::with(['question.quizSet', 'attempt'])
            ->whereIn('quiz_attempt_id', $lastFiveAttemptIds)
            ->where('is_correct', false)
            ->latest()
            ->get()
            ->unique('question_id')
            ->values();

        return view('quiz.mistakes', compact('wrongAnswers'));
    }
}
