<?php
// [Thành viên 3 - Contributor B] app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\QuizSet;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $quizSets = QuizSet::withCount('questions', 'attempts')
            ->latest()
            ->get();

        return view('admin.index', compact('quizSets'));
    }

    /**
     * Gọi Wikipedia Tiếng Việt (vi.wikipedia.org), nếu không có tự động tìm trên en.wikipedia.org
     */
    public function fetchWikipedia(Request $request)
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:150'],
        ]);

        $topic = trim($request->input('topic'));

        try {
            $wikiLang = 'vi';
            $searchResp = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'AbyssalMindOceanQuiz/1.0 (https://ocean.io; admin@ocean.io)'
            ])->get("https://{$wikiLang}.wikipedia.org/w/api.php", [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => $topic,
                'format' => 'json',
                'srlimit' => 1,
            ]);

            $searchData = $searchResp->json();

            // Nếu từ khóa không có trên Wiki Tiếng Việt, tự động tìm trên Wiki Tiếng Anh
            if (empty($searchData['query']['search'])) {
                $wikiLang = 'en';
                $searchResp = Http::withoutVerifying()->withHeaders([
                    'User-Agent' => 'AbyssalMindOceanQuiz/1.0 (https://ocean.io; admin@ocean.io)'
                ])->get("https://{$wikiLang}.wikipedia.org/w/api.php", [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $topic,
                    'format' => 'json',
                    'srlimit' => 1,
                ]);
                $searchData = $searchResp->json();
            }

            if (empty($searchData['query']['search'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy bài viết Wikipedia nào cho chủ đề: {$topic}",
                ], 404);
            }

            $bestTitle = $searchData['query']['search'][0]['title'];

            $extractResp = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'AbyssalMindOceanQuiz/1.0 (https://ocean.io; admin@ocean.io)'
            ])->get("https://{$wikiLang}.wikipedia.org/w/api.php", [
                'action' => 'query',
                'prop' => 'extracts',
                'exchars' => 2800,
                'explaintext' => true,
                'titles' => $bestTitle,
                'redirects' => 1,
                'format' => 'json',
            ]);

            $pages = $extractResp->json()['query']['pages'] ?? [];
            $page = reset($pages);
            $extractText = trim($page['extract'] ?? '');

            return response()->json([
                'success' => true,
                'title' => $bestTitle,
                'source_url' => "https://{$wikiLang}.wikipedia.org/wiki/" . rawurlencode(str_replace(' ', '_', $bestTitle)),
                'extract' => $extractText,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi Wikipedia API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gửi văn bản sang Gemini AI và yêu cầu sinh câu hỏi trắc nghiệm bằng TIẾNG VIỆT
     */
    public function generateGemini(Request $request)
    {
        $request->validate([
            'passage' => ['required', 'string', 'min:80'],
            'question_count' => ['nullable', 'integer', 'min:3', 'max:10'],
        ]);

        $passage = $request->input('passage');
        $count = $request->input('question_count', 5);
        $apiKey = trim((string) env('GEMINI_API_KEY'));

        if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            return response()->json([
                'success' => true,
                'mode' => 'fallback_simulation',
                'questions' => $this->buildFallbackQuestionsFromPassage($passage, $count),
                'notice' => 'ℹ️ Đã tạo câu hỏi bằng bộ phân tích nội bộ (Thêm GEMINI_API_KEY vào .env để dùng Gemini AI).',
            ]);
        }

        $prompt = <<<PROMPT
Bạn là chuyên gia hải dương học. Hãy phân tích đoạn văn bản tham khảo dưới đây và tạo ra {$count} câu hỏi trắc nghiệm bằng TIẾNG VIỆT bám sát nội dung văn bản.
Chỉ trả về duy nhất một mảng JSON hợp lệ (không có markdown, không có ```json) trong đó mỗi phần tử gồm:
- "question_text": chuỗi câu hỏi bằng tiếng Việt
- "options": mảng gồm đúng 4 chuỗi đáp án bằng tiếng Việt
- "correct_option_index": số nguyên (0, 1, 2, hoặc 3) chỉ vị trí đáp án đúng
- "explanation": chuỗi giải thích ngắn gọn bằng tiếng Việt vì sao đáp án đó đúng dựa theo văn bản

Văn bản tham khảo:
{$passage}
PROMPT;

        $modelsToTry = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-flash-latest',
        ];

        foreach ($modelsToTry as $modelName) {
            try {
                $response = Http::withoutVerifying()->timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'responseMimeType' => 'application/json',
                        ]
                    ]
                );

                if ($response->successful()) {
                    $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
                    $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($rawText));
                    $questions = json_decode($cleanJson, true);

                    if (is_array($questions) && !empty($questions)) {
                        return response()->json([
                            'success' => true,
                            'mode' => 'gemini_live',
                            'questions' => $questions,
                            'notice' => "✅ Gemini AI ({$modelName}) đã tạo thành công " . count($questions) . " câu hỏi Tiếng Việt!",
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Thử model tiếp theo
            }
        }

        return response()->json([
            'success' => true,
            'mode' => 'fallback_simulation',
            'questions' => $this->buildFallbackQuestionsFromPassage($passage, $count),
            'notice' => '⚠️ Đã kích hoạt bộ tạo câu hỏi dự phòng từ văn bản.',
        ]);
    }

    public function storeQuizSet(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'ocean_zone' => ['required', 'string', 'max:255'],
            'reading_passage' => ['required', 'string', 'min:50'],
            'source_url' => ['nullable', 'string', 'max:500'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'size:4'],
            'questions.*.correct_option_index' => ['required', 'integer', 'min:0', 'max:3'],
            'questions.*.explanation' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $quizSet = QuizSet::create([
                'title' => $validated['title'],
                'topic' => $validated['topic'],
                'ocean_zone' => $validated['ocean_zone'],
                'reading_passage' => $validated['reading_passage'],
                'source_url' => $validated['source_url'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['questions'] as $q) {
                Question::create([
                    'quiz_set_id' => $quizSet->id,
                    'question_text' => $q['question_text'],
                    'options' => array_values($q['options']),
                    'correct_option_index' => (int) $q['correct_option_index'],
                    'explanation' => $q['explanation'] ?? null,
                    'time_limit' => 20,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu bộ đề cùng ' . count($validated['questions']) . ' câu hỏi vào cơ sở dữ liệu MySQL!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lưu Database: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyQuizSet(QuizSet $quizSet)
    {
        $quizSet->delete();
        return back()->with('success', 'Đã xóa bộ đề khỏi cơ sở dữ liệu.');
    }

    private function buildFallbackQuestionsFromPassage(string $passage, int $count): array
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim($passage), -1, PREG_SPLIT_NO_EMPTY);
        $questions = [];

        for ($i = 0; $i < min($count, max(3, count($sentences))); $i++) {
            $sentence = $sentences[$i] ?? $sentences[0];
            $questions[] = [
                'question_text' => "Theo tư liệu khoa học vừa đọc, nhận định nào sau đây là chính xác (Câu #" . ($i + 1) . ")?",
                'options' => [
                    mb_substr($sentence, 0, 110) . (mb_strlen($sentence) > 110 ? '...' : ''),
                    'Ánh sáng mặt trời có thể chiếu sáng rực rỡ xuống độ sâu hơn 8.000 mét.',
                    'Áp suất nước biển giảm dần khi lặn sâu xuống đáy đại dương.',
                    'Hoàn toàn không có sinh vật nào tồn tại được ở độ sâu quá 200 mét.'
                ],
                'correct_option_index' => 0,
                'explanation' => 'Trích xuất trực tiếp từ bài đọc: "' . mb_substr($sentence, 0, 140) . '..."',
            ];
        }

        return $questions;
    }
}