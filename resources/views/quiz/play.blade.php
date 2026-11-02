@extends('layouts.app')

@section('title', 'Live Quiz Arena: ' . $quizSet->title)

@section('content')
<div class="container">

    <!-- =================================================================
         STEP 1: READING PHASE (JOURNALISTIC DARK LAYOUT + 3-MIN COUNTDOWN)
         ================================================================= -->
    <div id="readingPhaseContainer" class="reading-journal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="zone-depth-pill">STEP 1 OF 2 • SCIENTIFIC BRIEFING</span>
                <span style="margin-left: 0.65rem; font-size: 0.85rem; color: var(--text-muted);">🌊 {{ $quizSet->ocean_zone }}</span>
            </div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.25rem; font-weight: 700; color: var(--cyan-glow);">
                ⏳ <span id="readingTimeText">03:00</span>
            </div>
        </div>

        <!-- 3-Minute Horizontal Countdown Progress Bar -->
        <div class="timer-progress-wrapper">
            <div id="readingProgressBar" class="timer-progress-bar"></div>
        </div>

        <h1 style="font-size: 2rem; margin-top: 1.2rem;">{{ $quizSet->title }}</h1>

        <article class="journal-passage">{{ $quizSet->reading_passage }}</article>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 1.5rem; margin-top: 1.5rem;">
            <div style="font-size: 0.88rem; color: var(--text-muted);">
                📌 Contains <strong>{{ $quizSet->questions->count() }} speed-scored questions</strong> based on this passage.
            </div>
            <button type="button" id="startQuizEarlyBtn" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.9rem 1.8rem;">
                ⚡ Start Quiz Early →
            </button>
        </div>
    </div>

    <!-- =================================================================
         STEP 2: KAHOOT / QUIZIZZ MINIMALIST HIGH-FOCUS INTERFACE
         ================================================================= -->
    <div id="kahootPhaseContainer" class="kahoot-arena" style="display: none;">

        <!-- Top Telemetry Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(12, 25, 48, 0.85); padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
            <div style="font-weight: 700;">
                Question <span id="qCurrentIndex" style="color: var(--cyan-glow);">1</span> of {{ $quizSet->questions->count() }}
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.45rem; font-weight: 800; color: #fbbf24;">
                ⏱️ <span id="questionTimerDisplay">20.0</span>s
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.2rem; font-weight: 800; color: #4ade80;">
                🏆 <span id="liveScoreDisplay">0</span> pts
            </div>
        </div>

        <!-- Rapid Question Countdown Bar -->
        <div class="timer-progress-wrapper" style="margin: 0;">
            <div id="questionProgressBar" class="timer-progress-bar"></div>
        </div>

        <!-- Question Text Banner -->
        <div class="kahoot-question-banner" id="kahootQuestionText">
            Loading question...
        </div>

        <!-- Real-Time Feedback Banner (Hidden until answered) -->
        <div id="instantFeedbackBanner" style="display: none; padding: 1rem 1.5rem; border-radius: 14px; text-align: center; font-weight: 700; font-size: 1.15rem;"></div>

        <!-- 4 Vibrant Colored Option Boxes -->
        <div class="kahoot-options-grid">
            <button type="button" class="kahoot-btn opt-0" onclick="selectKahootAnswer(0)">
                <span class="kahoot-shape">▲</span>
                <span id="optText0">Option A</span>
            </button>
            <button type="button" class="kahoot-btn opt-1" onclick="selectKahootAnswer(1)">
                <span class="kahoot-shape">◆</span>
                <span id="optText1">Option B</span>
            </button>
            <button type="button" class="kahoot-btn opt-2" onclick="selectKahootAnswer(2)">
                <span class="kahoot-shape">●</span>
                <span id="optText2">Option C</span>
            </button>
            <button type="button" class="kahoot-btn opt-3" onclick="selectKahootAnswer(3)">
                <span class="kahoot-shape">■</span>
                <span id="optText3">Option D</span>
            </button>
        </div>
    </div>

    <!-- =================================================================
         FINAL PODIUM SUMMARY MODAL
         ================================================================= -->
    <div id="quizCompletionBox" class="reading-journal-box" style="display: none; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 0.5rem;">🏆</div>
        <h2 style="font-size: 2.2rem;">Expedition Complete!</h2>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Your telemetry has been synchronized with the Global Ocean Leaderboard.</p>

        <div class="stat-grid-2" style="max-width: 480px; margin: 1.5rem auto;">
            <div class="stat-tile">
                <div style="font-size: 0.8rem; color: var(--text-muted);">FINAL SCORE</div>
                <div class="value" id="finalScoreValue">0</div>
            </div>
            <div class="stat-tile">
                <div style="font-size: 0.8rem; color: var(--text-muted);">ACCURACY</div>
                <div class="value" id="finalAccuracyValue">0/0</div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">📊 View Detailed Breakdown</a>
            <a href="{{ route('leaderboard') }}" class="btn btn-outline">🌍 Global Leaderboard</a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const quizSetId = {{ $quizSet->id }};
    const questions = @json($quizSet->questions);
    const submitUrl = "{{ route('quiz.submit') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- STEP 1: 3-MINUTE (180s) READING PHASE TIMER ---
    const READING_TOTAL_SECONDS = 180;
    let readingRemaining = READING_TOTAL_SECONDS;
    let readingInterval = null;

    function startReadingTimer() {
        readingInterval = setInterval(() => {
            readingRemaining--;
            const mins = String(Math.floor(readingRemaining / 60)).padStart(2, '0');
            const secs = String(readingRemaining % 60).padStart(2, '0');
            document.getElementById('readingTimeText').textContent = `${mins}:${secs}`;

            const pct = (readingRemaining / READING_TOTAL_SECONDS) * 100;
            document.getElementById('readingProgressBar').style.width = `${pct}%`;

            if (readingRemaining <= 0) {
                clearInterval(readingInterval);
                launchKahootPhase();
            }
        }, 1000);
    }

    document.getElementById('startQuizEarlyBtn').addEventListener('click', () => {
        clearInterval(readingInterval);
        launchKahootPhase();
    });

    // --- STEP 2: KAHOOT ENGINE ---
    let currentQIndex = 0;
    let liveTotalScore = 0;
    let questionStartTime = 0;
    let questionTimerInterval = null;
    let recordedAnswers = [];
    let quizStartTimestamp = 0;
    let isLocked = false;

    function launchKahootPhase() {
        document.getElementById('readingPhaseContainer').style.display = 'none';
        document.getElementById('kahootPhaseContainer').style.display = 'flex';
        quizStartTimestamp = Date.now();
        loadQuestion(0);
    }

    function loadQuestion(index) {
        if (index >= questions.length) {
            finishAndSubmitQuiz();
            return;
        }

        isLocked = false;
        currentQIndex = index;
        const q = questions[index];

        document.getElementById('qCurrentIndex').textContent = index + 1;
        document.getElementById('kahootQuestionText').textContent = q.question_text;
        document.getElementById('instantFeedbackBanner').style.display = 'none';

        const buttons = document.querySelectorAll('.kahoot-btn');
        buttons.forEach((btn, idx) => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.outline = 'none';
            document.getElementById(`optText${idx}`).textContent = q.options[idx] || '';
        });

        const timeLimit = q.time_limit || 20;
        let timeLeft = timeLimit;
        questionStartTime = performance.now();

        document.getElementById('questionProgressBar').style.width = '100%';
        document.getElementById('questionTimerDisplay').textContent = timeLeft.toFixed(1);

        clearInterval(questionTimerInterval);
        questionTimerInterval = setInterval(() => {
            const elapsed = (performance.now() - questionStartTime) / 1000;
            timeLeft = Math.max(0, timeLimit - elapsed);
            document.getElementById('questionTimerDisplay').textContent = timeLeft.toFixed(1);
            document.getElementById('questionProgressBar').style.width = `${(timeLeft / timeLimit) * 100}%`;

            if (timeLeft <= 0) {
                clearInterval(questionTimerInterval);
                selectKahootAnswer(null); // Timeout
            }
        }, 100);
    }

    function selectKahootAnswer(selectedIndex) {
        if (isLocked) return;
        isLocked = true;
        clearInterval(questionTimerInterval);

        const q = questions[currentQIndex];
        const timeLimit = q.time_limit || 20;
        const elapsedSeconds = Math.min((performance.now() - questionStartTime) / 1000, timeLimit);
        const isCorrect = (selectedIndex !== null && selectedIndex === Number(q.correct_option_index));

        let earnedPoints = 0;
        if (isCorrect) {
            const speedFactor = 1 - ((elapsedSeconds / timeLimit) * 0.5);
            earnedPoints = Math.round(1000 * Math.max(speedFactor, 0.5));
            liveTotalScore += earnedPoints;
            document.getElementById('liveScoreDisplay').textContent = liveTotalScore.toLocaleString();
        }

        // Visual Feedback on Kahoot Buttons
        const buttons = document.querySelectorAll('.kahoot-btn');
        buttons.forEach((btn, idx) => {
            btn.disabled = true;
            if (idx === Number(q.correct_option_index)) {
                btn.style.outline = '4px solid #ffffff';
                btn.style.opacity = '1';
            } else {
                btn.style.opacity = '0.32';
            }
        });

        // Show Instant Feedback Banner
        const banner = document.getElementById('instantFeedbackBanner');
        banner.style.display = 'block';
        if (isCorrect) {
            banner.style.background = 'rgba(34, 197, 94, 0.25)';
            banner.style.border = '1px solid #4ade80';
            banner.style.color = '#4ade80';
            banner.innerHTML = `✅ CORRECT! +${earnedPoints} Speed Points (${elapsedSeconds.toFixed(1)}s)`;
        } else {
            banner.style.background = 'rgba(239, 68, 68, 0.25)';
            banner.style.border = '1px solid #f87171';
            banner.style.color = '#fca5a5';
            banner.innerHTML = `❌ ${selectedIndex === null ? "TIME'S UP!" : "INCORRECT!"} Correct Answer: ${q.options[q.correct_option_index]}`;
        }

        recordedAnswers.push({
            question_id: q.id,
            selected_option_index: selectedIndex,
            time_taken: Number(elapsedSeconds.toFixed(2))
        });

        setTimeout(() => {
            loadQuestion(currentQIndex + 1);
        }, 2200);
    }

    async function finishAndSubmitQuiz() {
        document.getElementById('kahootPhaseContainer').style.display = 'none';
        document.getElementById('quizCompletionBox').style.display = 'block';

        const durationSeconds = Math.max(1, Math.round((Date.now() - quizStartTimestamp) / 1000));

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quiz_set_id: quizSetId,
                    duration_seconds: durationSeconds,
                    answers: recordedAnswers
                })
            });

            const data = await response.json();
            if (data.success) {
                document.getElementById('finalScoreValue').textContent = data.score.toLocaleString() + ' pts';
                document.getElementById('finalAccuracyValue').textContent = `${data.correct_count} / ${data.total_questions}`;
            }
        } catch (err) {
            console.error('Error submitting quiz:', err);
        }
    }

    // Start Step 1 immediately on page load
    startReadingTimer();
</script>
@endpush
```

**Phân công:** `[Thành viên 2 - Contributor A] resources/views/quiz/mistakes.blade.php`
```html
<!-- [Thành viên 2 - Contributor A] resources/views/quiz/mistakes.blade.php -->
@extends('layouts.app')

@section('title', 'Targeted Mistake Review | AbyssalMind')

@section('content')
<div class="container" style="max-width: 960px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div>
            <span class="zone-depth-pill">ADAPTIVE LEARNING DECK</span>
            <h1 style="font-size: 1.85rem; margin-top: 0.4rem;">🎯 Review Mistakes (Last 5 Attempts)</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">
                Questions you missed in your last 5 expeditions are automatically aggregated here for targeted mastery.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">← Back to Bento Dashboard</a>
    </div>

    @forelse($wrongAnswers as $idx => $wrongItem)
        @php
            $q = $wrongItem->question;
            $options = $q ? $q->options : [];
        @endphp
        @if($q)
            <div class="bento-card" style="margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.8rem; color: var(--cyan-glow); font-weight: 700;">
                        📚 Topic: {{ $q->quizSet->title ?? 'Ocean Set' }} ({{ $q->quizSet->ocean_zone ?? '' }})
                    </span>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                        Missed {{ $wrongItem->created_at->diffForHumans() }}
                    </span>
                </div>

                <h3 style="font-size: 1.2rem; margin-bottom: 1rem;">{{ $idx + 1 }}. {{ $q->question_text }}</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    @foreach($options as $optIdx => $optText)
                        @php
                            $isCorrectOpt = ($optIdx === (int)$q->correct_option_index);
                            $wasSelectedWrong = ($wrongItem->selected_option_index !== null && $optIdx === (int)$wrongItem->selected_option_index);
                        @endphp
                        <div style="padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid {{ $isCorrectOpt ? '#22c55e' : ($wasSelectedWrong ? '#ef4444' : 'rgba(255,255,255,0.1)') }}; background: {{ $isCorrectOpt ? 'rgba(34, 197, 94, 0.14)' : ($wasSelectedWrong ? 'rgba(239, 68, 68, 0.14)' : 'rgba(255,255,255,0.03)') }};">
                            <strong>{{ chr(65 + $optIdx) }}.</strong> {{ $optText }}
                            @if($isCorrectOpt)
                                <span style="float: right; color: #4ade80; font-weight: 700;">✓ Correct</span>
                            @elseif($wasSelectedWrong)
                                <span style="float: right; color: #f87171; font-weight: 700;">✗ Your Choice</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div style="background: rgba(0, 242, 254, 0.08); border-left: 3px solid var(--cyan-glow); padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                        <strong>💡 Scientific Explanation:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endif
    @empty
        <div class="bento-card" style="text-align: center; padding: 3.5rem 1.5rem;">
            <div style="font-size: 3.5rem; margin-bottom: 0.75rem;">🌟</div>
            <h2>Flawless Sonar Record!</h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 1.5rem;">
                You have zero unanswered or incorrect questions across your last 5 quiz attempts.
            </p>
            <a href="{{ route('quiz.play') }}" class="btn btn-primary">🚀 Take Another Ocean Quiz</a>
        </div>
    @endforelse
</div>
@endsection