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
     * Nửa Trái Split-Screen: Gọi Wikipedia API tìm kiếm và trích xuất nội dung bài viết theo chủ đề
     */
    public function fetchWikipedia(Request $request)
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:150'],
        ]);

        $topic = trim($request->input('topic'));

        try {
            // Bước 1: Tìm tiêu đề bài viết khớp nhất trên Wikipedia tiếng Anh
            $searchResp = Http::withHeaders([
                'User-Agent' => 'AbyssalMindOceanQuiz/1.0 (https://ocean.io; admin@ocean.io)'
            ])->get('https://en.wikipedia.org/w/api.php', [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => $topic,
                'format' => 'json',
                'srlimit' => 1,
            ]);

            $searchData = $searchResp->json();
            if (empty($searchData['query']['search'])) {
                return response()->json([
                    'success' => false,
                    'message' => "No Wikipedia article found for topic: {$topic}",
                ], 404);
            }

            $bestTitle = $searchData['query']['search'][0]['title'];

            // Bước 2: Lấy văn bản thuần (Plaintext Extract) của bài viết đó
            $extractResp = Http::withHeaders([
                'User-Agent' => 'AbyssalMindOceanQuiz/1.0 (https://ocean.io; admin@ocean.io)'
            ])->get('https://en.wikipedia.org/w/api.php', [
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

            if (empty($extractText)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wikipedia article returned empty text.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'title' => $bestTitle,
                'source_url' => 'https://en.wikipedia.org/wiki/' . rawurlencode(str_replace(' ', '_', $bestTitle)),
                'extract' => $extractText,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Wikipedia API Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Nửa Phải Split-Screen: Gửi văn bản Wikipedia sang Gemini API để sinh bộ câu hỏi JSON
     */
    public function generateGemini(Request $request)
    {
        $request->validate([
            'passage' => ['required', 'string', 'min:80'],
            'question_count' => ['nullable', 'integer', 'min:3', 'max:10'],
        ]);

        $passage = $request->input('passage');
        $count = $request->input('question_count', 5);
        $apiKey = env('GEMINI_API_KEY');

        // Nếu chưa cấu hình GEMINI_API_KEY hoặc dùng key mẫu, cung cấp cơ chế AI Smart Parser dự phòng
        // để toàn nhóm vẫn test được trọn vẹn luồng Split-Screen ngay lập tức
        if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            return response()->json([
                'success' => true,
                'mode' => 'fallback_simulation',
                'questions' => $this->buildFallbackQuestionsFromPassage($passage, $count),
                'notice' => 'Generated via built-in NLP analyzer (Add GEMINI_API_KEY in .env for live Gemini 1.5 Flash calls).',
            ]);
        }

        $prompt = <<<PROMPT
You are an expert marine biology quiz generator. Analyze the following reference text and generate {$count} multiple-choice questions strictly based on the text.
Return ONLY a valid JSON array (no markdown formatting, no code fences) where each object has:
- "question_text": string
- "options": array of exactly 4 strings
- "correct_option_index": integer (0, 1, 2, or 3)
- "explanation": short string explaining why the answer is correct based on the text

Reference Text:
{$passage}
PROMPT;

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
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

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API Error: ' . $response->body(),
                ], 422);
            }

            $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($rawText));
            $questions = json_decode($cleanJson, true);

            if (!is_array($questions) || empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not parse JSON questions from Gemini response.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'mode' => 'gemini_live',
                'questions' => $questions,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini Connection Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lưu toàn bộ Quiz Set (Bài đọc + Danh sách câu hỏi đã được Admin duyệt/chỉnh sửa) vào MySQL
     */
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
                'message' => 'Quiz Set and ' . count($validated['questions']) . ' questions saved to MySQL!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database Save Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyQuizSet(QuizSet $quizSet)
    {
        $quizSet->delete();
        return back()->with('success', 'Quiz Set deleted from database.');
    }

    /**
     * Bộ phân tích câu dự phòng thông minh khi chưa điền GEMINI_API_KEY
     */
    private function buildFallbackQuestionsFromPassage(string $passage, int $count): array
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim($passage), -1, PREG_SPLIT_NO_EMPTY);
        $questions = [];

        for ($i = 0; $i < min($count, max(3, count($sentences))); $i++) {
            $sentence = $sentences[$i] ?? $sentences[0];
            $questions[] = [
                'question_text' => "According to the scientific passage, which statement accurately reflects key facts (#" . ($i + 1) . ")?",
                'options' => [
                    mb_substr($sentence, 0, 110) . (mb_strlen($sentence) > 110 ? '...' : ''),
                    'Sunlight penetrates freely below 5,000 meters to support coral reefs.',
                    'Water pressure decreases linearly with ocean depth.',
                    'Marine organisms cannot survive below 100 meters depth.'
                ],
                'correct_option_index' => 0,
                'explanation' => 'Directly verified from the reference passage: "' . mb_substr($sentence, 0, 140) . '..."',
            ];
        }

        return $questions;
    }
}