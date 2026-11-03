@extends('layouts.app')

@section('title', 'Trung Tâm Quản Trị AI | AbyssalMind')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <span class="zone-depth-pill">QUẢN TRỊ HỆ THỐNG & TỰ ĐỘNG HÓA AI</span>
            <h1 style="font-size: 1.9rem; margin-top: 0.4rem;">⚡ Trình Tạo Đề Tự Động Wikipedia + Gemini AI</h1>
        </div>
    </div>

    <!-- GIAO DIỆN CHIA ĐÔI MÀN HÌNH: TRÁI (WIKIPEDIA) | PHẢI (GEMINI AI) -->
    <div class="admin-split-screen">

        <!-- NỬA TRÁI: TRÍCH XUẤT BÀI VIẾT TỪ WIKIPEDIA TIẾNG VIỆT -->
        <div class="bento-card">
            <h2 style="font-size: 1.3rem; margin-bottom: 0.4rem;">🌐 1. Trích Xuất Tư Liệu Từ Wikipedia</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.2rem;">
                Nhập chủ đề đại dương bằng Tiếng Việt (VD: <em>Cá voi xanh</em>, <em>Rãnh Mariana</em>, <em>San hô</em>, <em>Cá mập trắng lớn</em>) để lấy văn bản từ Wikipedia.
            </p>

            <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                <input type="text" id="wikiTopicInput" class="form-control" placeholder="Nhập chủ đề (VD: Cá voi xanh)..." value="Cá voi xanh">
                <button type="button" id="fetchWikiBtn" class="btn btn-primary" style="white-space: nowrap;">
                    🔍 Lấy Bài Wikipedia
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Tiêu Đề Bộ Đề</label>
                    <input type="text" id="quizTitleInput" class="form-control" placeholder="Tiêu đề bài đọc">
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Tầng Sinh Thái Đại Dương</label>
                    <select id="oceanZoneSelect" class="form-control">
                        <option value="Tầng Ánh Sáng (0m - 200m)">Tầng Ánh Sáng (0m - 200m)</option>
                        <option value="Tầng Chạng Vạng (200m - 1,000m)">Tầng Chạng Vạng (200m - 1,000m)</option>
                        <option value="Tầng Nửa Đêm (1,000m - 4,000m)">Tầng Nửa Đêm (1,000m - 4,000m)</option>
                        <option value="Tầng Đáy Thẳm (4,000m - 6,000m)" selected>Tầng Đáy Thẳm (4,000m - 6,000m)</option>
                        <option value="Tầng Hố Thẳm (6,000m - 11,000m)">Tầng Hố Thẳm (6,000m - 11,000m)</option>
                    </select>
                </div>
            </div>

            <input type="hidden" id="wikiSourceUrl" value="">

            <label style="font-size: 0.8rem; color: var(--text-muted);">Nội Dung Bài Đọc Tham Khảo (Có thể chỉnh sửa trước khi tạo câu hỏi)</label>
            <textarea id="wikiPassageTextarea" rows="12" class="form-control" style="margin-top: 0.35rem; line-height: 1.65;" placeholder="Nội dung trích xuất từ Wikipedia sẽ hiển thị tại đây..."></textarea>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.15rem;">
                <span id="wikiStatusMsg" style="font-size: 0.85rem; color: var(--cyan-glow);"></span>
                <button type="button" id="generateGeminiBtn" class="btn btn-warning">
                    ✨ Phân Tích & Tạo Câu Hỏi Bằng Gemini AI →
                </button>
            </div>
        </div>

        <!-- NỬA PHẢI: CÂU HỎI DO GEMINI AI TẠO (SỬA, XÓA, TẠO LẠI, LƯU) -->
        <div class="bento-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <h2 style="font-size: 1.3rem;">🤖 2. Kiểm Duyệt Câu Hỏi Từ Gemini AI</h2>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Xem trước, Chỉnh sửa, Xóa hoặc Tạo lại trước khi lưu vào MySQL</p>
                    </div>
                    <button type="button" id="regenerateAiBtn" class="btn btn-outline" style="padding: 0.45rem 0.9rem; font-size: 0.82rem; display: none;">
                        🔄 Tạo Lại (Regenerate)
                    </button>
                </div>

                <div id="aiQuestionsList" style="max-height: 520px; overflow-y: auto; padding-right: 0.4rem;">
                    <div style="text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
                        <div style="font-size: 2.8rem; margin-bottom: 0.5rem;">🧠</div>
                        <p>Hãy lấy một bài viết Wikipedia ở cột bên trái, sau đó bấm nút <strong>"Phân Tích & Tạo Câu Hỏi Bằng Gemini AI"</strong>.</p>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" onclick="addBlankQuestionCard()" class="btn btn-outline" style="font-size: 0.85rem;">
                    ➕ Thêm Câu Hỏi Thủ Công
                </button>
                <button type="button" id="saveQuizPackageBtn" class="btn btn-primary" disabled>
                    💾 Lưu Bộ Đề Vào Database
                </button>
            </div>
        </div>

    </div>

    <!-- BẢNG QUẢN LÝ CÁC BỘ ĐỀ ĐANG CÓ TRONG DATABASE -->
    <div class="bento-card">
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem;">🗄️ Danh Sách Bộ Đề Trong Cơ Sở Dữ Liệu (MySQL)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đề</th>
                    <th>Tiêu Đề & Nguồn</th>
                    <th>Tầng Sinh Thái</th>
                    <th>Số Câu Hỏi</th>
                    <th>Lượt Làm Bài</th>
                    <th style="text-align: right;">Thao Tác</th>
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
                        <td><span class="zone-depth-pill">{{ $set->questions_count }} câu</span></td>
                        <td>{{ $set->attempts_count }} lượt</td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.quiz-sets.destroy', $set) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ đề này và toàn bộ câu hỏi bên trong?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.85rem; font-size: 0.82rem;">🗑️ Xóa</button>
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

    document.getElementById('fetchWikiBtn').addEventListener('click', async () => {
        const topic = document.getElementById('wikiTopicInput').value.trim();
        const statusMsg = document.getElementById('wikiStatusMsg');
        if (!topic) return alert('Vui lòng nhập chủ đề cần tìm!');

        statusMsg.textContent = '⏳ Đang tải dữ liệu từ Wikipedia Tiếng Việt...';

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
                statusMsg.textContent = '❌ ' + (data.message || 'Không tìm thấy bài viết');
                return;
            }

            document.getElementById('quizTitleInput').value = data.title;
            document.getElementById('wikiPassageTextarea').value = data.extract;
            document.getElementById('wikiSourceUrl').value = data.source_url;
            statusMsg.textContent = '✅ Đã tải bài viết Wikipedia: ' + data.title;
        } catch (err) {
            statusMsg.textContent = '❌ Lỗi kết nối tới Wikipedia API.';
        }
    });

    async function triggerGeminiGeneration() {
        const passage = document.getElementById('wikiPassageTextarea').value.trim();
        const statusMsg = document.getElementById('wikiStatusMsg');

        if (passage.length < 80) {
            return alert('Vui lòng lấy bài viết Wikipedia hoặc nhập văn bản dài tối thiểu 80 ký tự.');
        }

        statusMsg.textContent = '🤖 Gemini AI đang đọc văn bản và tạo bộ câu hỏi trắc nghiệm Tiếng Việt...';
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
                statusMsg.textContent = '❌ ' + (data.message || 'Lỗi tạo câu hỏi');
                return;
            }

            draftQuestions = data.questions;
            renderDraftQuestions();
            document.getElementById('regenerateAiBtn').style.display = 'inline-flex';
            document.getElementById('saveQuizPackageBtn').disabled = false;
            statusMsg.textContent = data.notice ? `${data.notice}` : '✅ Đã tạo thành công 5 câu hỏi!';
        } catch (err) {
            document.getElementById('generateGeminiBtn').disabled = false;
            statusMsg.textContent = '❌ Lỗi giao tiếp với AI.';
        }
    }

    document.getElementById('generateGeminiBtn').addEventListener('click', triggerGeminiGeneration);
    document.getElementById('regenerateAiBtn').addEventListener('click', triggerGeminiGeneration);

    function renderDraftQuestions() {
        const container = document.getElementById('aiQuestionsList');
        if (draftQuestions.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding: 2rem; color: var(--text-muted);">Chưa có câu hỏi nào. Bấm Thêm Câu Hỏi Thủ Công hoặc Tạo Lại.</div>`;
            document.getElementById('saveQuizPackageBtn').disabled = true;
            return;
        }

        document.getElementById('saveQuizPackageBtn').disabled = false;
        container.innerHTML = draftQuestions.map((q, qIdx) => `
            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 14px; padding: 1.15rem; margin-bottom: 1rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.6rem;">
                    <strong style="color: var(--cyan-glow);">Câu hỏi #${qIdx + 1} (Tích tròn vào đáp án đúng)</strong>
                    <button type="button" onclick="removeDraftQuestion(${qIdx})" class="btn btn-danger" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                        ✕ Xóa câu này
                    </button>
                </div>

                <input type="text" class="form-control" style="margin-bottom: 0.65rem; font-weight: 600;"
                    value="${escapeHtml(q.question_text)}"
                    onchange="updateDraftField(${qIdx}, 'question_text', this.value)">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.65rem;">
                    ${[0, 1, 2, 3].map(optIdx => `
                        <div style="display:flex; align-items:center; gap: 0.4rem;">
                            <input type="radio" name="correct_opt_${qIdx}" ${Number(q.correct_option_index) === optIdx ? 'checked' : ''}
                                onchange="updateDraftField(${qIdx}, 'correct_option_index', ${optIdx})" title="Chọn làm đáp án đúng">
                            <input type="text" class="form-control" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;"
                                value="${escapeHtml((q.options && q.options[optIdx]) || '')}"
                                onchange="updateDraftOption(${qIdx}, ${optIdx}, this.value)">
                        </div>
                    `).join('')}
                </div>

                <input type="text" class="form-control" style="font-size: 0.82rem; color: #cbd5e1;"
                    placeholder="Giải thích đáp án (tùy chọn)"
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
            question_text: 'Nhập nội dung câu hỏi mới?',
            options: ['Đáp án A', 'Đáp án B', 'Đáp án C', 'Đáp án D'],
            correct_option_index: 0,
            explanation: 'Giải thích dựa trên bài đọc.'
        });
        renderDraftQuestions();
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.getElementById('saveQuizPackageBtn').addEventListener('click', async () => {
        const payload = {
            title: document.getElementById('quizTitleInput').value.trim() || 'Bộ Đề Khám Phá Đại Dương',
            topic: document.getElementById('wikiTopicInput').value.trim() || 'Sinh học biển',
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
                alert('❌ Lỗi lưu dữ liệu: ' + (data.message || 'Vui lòng kiểm tra lại'));
            }
        } catch (err) {
            alert('❌ Có lỗi xảy ra khi lưu bộ đề vào MySQL.');
        }
    });
</script>
@endpush