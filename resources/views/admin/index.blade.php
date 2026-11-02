@extends('layouts.app')

@section('title', 'Admin AI Command Center | AbyssalMind')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <span class="zone-depth-pill">ADMINISTRATION & AI AUTOMATION</span>
            <h1 style="font-size: 1.9rem; margin-top: 0.4rem;">⚡ Wikipedia + Gemini AI Quiz Studio</h1>
        </div>
    </div>

    <!-- =================================================================
         SPLIT-SCREEN LAYOUT: LEFT (WIKIPEDIA API) | RIGHT (GEMINI AI)
         ================================================================= -->
    <div class="admin-split-screen">

        <!-- LEFT HALF: WIKIPEDIA API REFERENCE FETCHING -->
        <div class="bento-card">
            <h2 style="font-size: 1.3rem; margin-bottom: 0.4rem;">🌐 1. Wikipedia Reference Extractor</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.2rem;">
                Enter any marine topic (e.g., <em>Coral bleaching</em>, <em>Giant squid</em>, <em>Hydrothermal vent</em>) to pull reference text via Wikipedia API.
            </p>

            <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                <input type="text" id="wikiTopicInput" class="form-control" placeholder="Enter ocean topic (e.g., Great Barrier Reef)..." value="Hydrothermal vent">
                <button type="button" id="fetchWikiBtn" class="btn btn-primary" style="white-space: nowrap;">
                    🔍 Fetch Wikipedia
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Quiz Set Title</label>
                    <input type="text" id="quizTitleInput" class="form-control" placeholder="Article Title">
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Ocean Ecological Zone</label>
                    <select id="oceanZoneSelect" class="form-control">
                        <option value="Sunlit Zone (0m - 200m)">Sunlit Zone (0m - 200m)</option>
                        <option value="Twilight Zone (200m - 1,000m)">Twilight Zone (200m - 1,000m)</option>
                        <option value="Midnight Zone (1,000m - 4,000m)">Midnight Zone (1,000m - 4,000m)</option>
                        <option value="Abyssal Zone (4,000m - 6,000m)" selected>Abyssal Zone (4,000m - 6,000m)</option>
                        <option value="Hadalpelagic Zone (6,000m - 11,000m)">Hadalpelagic Zone (6,000m - 11,000m)</option>
                    </select>
                </div>
            </div>

            <input type="hidden" id="wikiSourceUrl" value="">

            <label style="font-size: 0.8rem; color: var(--text-muted);">Curated Reading Passage (Editable before AI analysis)</label>
            <textarea id="wikiPassageTextarea" rows="12" class="form-control" style="margin-top: 0.35rem; line-height: 1.65;" placeholder="Fetched Wikipedia passage will appear here..."></textarea>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.15rem;">
                <span id="wikiStatusMsg" style="font-size: 0.85rem; color: var(--cyan-glow);"></span>
                <button type="button" id="generateGeminiBtn" class="btn btn-warning">
                    ✨ Analyze & Generate Questions with Gemini AI →
                </button>
            </div>
        </div>

        <!-- RIGHT HALF: GEMINI AI GENERATED QUESTIONS (EDIT, DELETE, REGENERATE, SAVE) -->
        <div class="bento-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <h2 style="font-size: 1.3rem;">🤖 2. Gemini AI Question Inspector</h2>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Review, Edit, Delete, or Regenerate questions before saving to MySQL</p>
                    </div>
                    <button type="button" id="regenerateAiBtn" class="btn btn-outline" style="padding: 0.45rem 0.9rem; font-size: 0.82rem; display: none;">
                        🔄 Regenerate
                    </button>
                </div>

                <div id="aiQuestionsList" style="max-height: 520px; overflow-y: auto; padding-right: 0.4rem;">
                    <div style="text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
                        <div style="font-size: 2.8rem; margin-bottom: 0.5rem;">🧠</div>
                        <p>Fetch a Wikipedia article on the left, then click <strong>"Analyze & Generate Questions with Gemini AI"</strong>.</p>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" onclick="addBlankQuestionCard()" class="btn btn-outline" style="font-size: 0.85rem;">
                    ➕ Add Manual Question
                </button>
                <button type="button" id="saveQuizPackageBtn" class="btn btn-primary" disabled>
                    💾 Save Approved Quiz Set to Database
                </button>
            </div>
        </div>

    </div>

    <!-- =================================================================
         TABLE-BASED ADMIN PANEL: EXISTING QUIZ SETS IN DATABASE
         ================================================================= -->
    <div class="bento-card">
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem;">🗄️ Existing Ocean Quiz Sets in MySQL</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title & Topic</th>
                    <th>Ecological Zone</th>
                    <th>Questions</th>
                    <th>Total Attempts</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quizSets as $set)
                    <tr>
                        <td style="font-family: 'JetBrains Mono', monospace;">#{{ $set->id }}</td>
                        <td>
                            <div style="font-weight: 700; color: var(--cyan-glow);">{{ $set->title }}</div>
                            @if($set->source_url)
                                <a href="{{ $set->source_url }}" target="_blank" style="font-size: 0.78rem; color: var(--text-muted);">{{ $set->source_url }}</a>
                            @endif
                        </td>
                        <td>{{ $set->ocean_zone }}</td>
                        <td><span class="zone-depth-pill">{{ $set->questions_count }} Qs</span></td>
                        <td>{{ $set->attempts_count }} dives</td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.quiz-sets.destroy', $set) }}" method="POST" onsubmit="return confirm('Delete this Quiz Set and all its questions?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.85rem; font-size: 0.82rem;">🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let draftQuestions = [];

    // 1. FETCH WIKIPEDIA API
    document.getElementById('fetchWikiBtn').addEventListener('click', async () => {
        const topic = document.getElementById('wikiTopicInput').value.trim();
        const statusMsg = document.getElementById('wikiStatusMsg');
        if (!topic) return alert('Please enter a topic!');

        statusMsg.textContent = '⏳ Querying Wikipedia API...';

        try {
            const res = await fetch("{{ route('admin.wikipedia') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ topic })
            });

            const data = await res.json();
            if (!data.success) {
                statusMsg.textContent = '❌ ' + (data.message || 'Not found');
                return;
            }

            document.getElementById('quizTitleInput').value = data.title;
            document.getElementById('wikiPassageTextarea').value = data.extract;
            document.getElementById('wikiSourceUrl').value = data.source_url;
            statusMsg.textContent = '✅ Loaded Wikipedia article: ' + data.title;
        } catch (err) {
            statusMsg.textContent = '❌ Failed to contact Wikipedia API.';
        }
    });

    // 2. GENERATE OR REGENERATE QUESTIONS VIA GEMINI API
    async function triggerGeminiGeneration() {
        const passage = document.getElementById('wikiPassageTextarea').value.trim();
        const statusMsg = document.getElementById('wikiStatusMsg');

        if (passage.length < 80) {
            return alert('Please fetch or enter a reading passage of at least 80 characters first.');
        }

        statusMsg.textContent = '🤖 Gemini AI is analyzing passage and synthesizing questions...';
        document.getElementById('generateGeminiBtn').disabled = true;

        try {
            const res = await fetch("{{ route('admin.gemini') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ passage, question_count: 5 })
            });

            const data = await res.json();
            document.getElementById('generateGeminiBtn').disabled = false;

            if (!data.success) {
                statusMsg.textContent = '❌ ' + (data.message || 'Generation failed');
                return;
            }

            draftQuestions = data.questions;
            renderDraftQuestions();
            document.getElementById('regenerateAiBtn').style.display = 'inline-flex';
            document.getElementById('saveQuizPackageBtn').disabled = false;
            statusMsg.textContent = data.notice ? `ℹ️ ${data.notice}` : '✅ Gemini AI generated 5 questions!';
        } catch (err) {
            document.getElementById('generateGeminiBtn').disabled = false;
            statusMsg.textContent = '❌ Error communicating with AI service.';
        }
    }

    document.getElementById('generateGeminiBtn').addEventListener('click', triggerGeminiGeneration);
    document.getElementById('regenerateAiBtn').addEventListener('click', triggerGeminiGeneration);

    // 3. RENDER EDITABLE QUESTION CARDS ON RIGHT SPLIT-SCREEN
    function renderDraftQuestions() {
        const container = document.getElementById('aiQuestionsList');
        if (draftQuestions.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding: 2rem; color: var(--text-muted);">No questions in draft. Click Add Manual Question or Regenerate.</div>`;
            document.getElementById('saveQuizPackageBtn').disabled = true;
            return;
        }

        document.getElementById('saveQuizPackageBtn').disabled = false;
        container.innerHTML = draftQuestions.map((q, qIdx) => `
            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 14px; padding: 1.15rem; margin-bottom: 1rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.6rem;">
                    <strong style="color: var(--cyan-glow);">Question #${qIdx + 1}</strong>
                    <button type="button" onclick="removeDraftQuestion(${qIdx})" class="btn btn-danger" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                        ✕ Delete
                    </button>
                </div>

                <input type="text" class="form-control" style="margin-bottom: 0.65rem; font-weight: 600;"
                    value="${escapeHtml(q.question_text)}"
                    onchange="updateDraftField(${qIdx}, 'question_text', this.value)">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.65rem;">
                    ${[0, 1, 2, 3].map(optIdx => `
                        <div style="display:flex; align-items:center; gap: 0.4rem;">
                            <input type="radio" name="correct_opt_${qIdx}" ${Number(q.correct_option_index) === optIdx ? 'checked' : ''}
                                onchange="updateDraftField(${qIdx}, 'correct_option_index', ${optIdx})" title="Mark as Correct Answer">
                            <input type="text" class="form-control" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;"
                                value="${escapeHtml((q.options && q.options[optIdx]) || '')}"
                                onchange="updateDraftOption(${qIdx}, ${optIdx}, this.value)">
                        </div>
                    `).join('')}
                </div>

                <input type="text" class="form-control" style="font-size: 0.82rem; color: #cbd5e1;"
                    placeholder="Explanation (optional)"
                    value="${escapeHtml(q.explanation || '')}"
                    onchange="updateDraftField(${qIdx}, 'explanation', this.value)">
            </div>
        `).join('');
    }

    function updateDraftField(qIdx, field, val) {
        draftQuestions[qIdx][field] = val;
    }

    function updateDraftOption(qIdx, optIdx, val) {
        draftQuestions[qIdx].options[optIdx] = val;
    }

    function removeDraftQuestion(qIdx) {
        draftQuestions.splice(qIdx, 1);
        renderDraftQuestions();
    }

    function addBlankQuestionCard() {
        draftQuestions.push({
            question_text: 'New custom ocean question?',
            options: ['Option A', 'Option B', 'Option C', 'Option D'],
            correct_option_index: 0,
            explanation: 'Explanation based on passage.'
        });
        renderDraftQuestions();
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // 4. SAVE APPROVED QUIZ SET + QUESTIONS TO MYSQL
    document.getElementById('saveQuizPackageBtn').addEventListener('click', async () => {
        const payload = {
            title: document.getElementById('quizTitleInput').value.trim() || 'Ocean Expedition Set',
            topic: document.getElementById('wikiTopicInput').value.trim() || 'Marine Biology',
            ocean_zone: document.getElementById('oceanZoneSelect').value,
            reading_passage: document.getElementById('wikiPassageTextarea').value.trim(),
            source_url: document.getElementById('wikiSourceUrl').value.trim(),
            questions: draftQuestions
        };

        try {
            const res = await fetch("{{ route('admin.quiz-sets.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                alert('✅ ' + data.message);
                window.location.reload();
            } else {
                alert('❌ Failed to save: ' + (data.message || 'Validation error'));
            }
        } catch (err) {
            alert('❌ Error saving quiz set to database.');
        }
    });
</script>
@endpush