@extends('admin.layout')

@section('title', 'Kiểm duyệt bộ câu hỏi | AbyssalMind')

@section('admin-content')
    @php
        $editable = ! $set->is_active && ! $set->attempts()->exists();
        $attemptCount = $set->attempts()->count();
    @endphp

    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Bước 3 · Kiểm duyệt</span>
            <h1 class="admin-page-title">{{ $set->title }}</h1>
            <p class="admin-page-desc">
                {{ $set->topic ?: 'Chưa phân loại' }} · {{ $set->ocean_zone }}
                @if ($set->source === 'ai')
                    · Sinh bởi Gemini
                @elseif ($set->source === 'manual')
                    · Soạn thủ công
                @endif
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.index') }}">← Bộ câu hỏi</a>
        </div>
    </header>

    <section class="admin-card">
        <div class="admin-card-head">
            <div class="admin-meta-row">
                <span class="admin-badge {{ $set->is_active ? 'is-live' : 'is-draft' }}">{{ $set->is_active ? 'Đã xuất bản' : 'Bản nháp' }}</span>
                <span class="admin-chip">{{ $set->questions->count() }}/5 câu</span>
                <span class="admin-chip">{{ number_format($attemptCount) }} lượt làm</span>
                @unless ($editable)
                    <span class="admin-chip">🔒 Nội dung đang khóa</span>
                @endunless
            </div>

            <div class="admin-actions-row">
                <form method="post" action="{{ $set->is_active ? route('admin.question-sets.unpublish', $set) : route('admin.question-sets.publish', $set) }}">
                    @csrf
                    <button class="btn btn-warning btn-sm">{{ $set->is_active ? 'Gỡ xuất bản' : 'Kiểm tra & xuất bản' }}</button>
                </form>

                @if ($editable)
                    <form method="post" action="{{ route('admin.question-sets.destroy', $set) }}" onsubmit="return confirm('Xóa bộ nháp chưa có lượt làm này?')">
                        @csrf
                        @method('delete')
                        <button class="btn btn-danger btn-sm">Xóa bộ nháp</button>
                    </form>
                @endif
            </div>
        </div>

        <p class="form-hint">
            @if ($set->is_active)
                Bộ đang hiển thị trong trò chơi. Gỡ xuất bản sẽ giữ nguyên lịch sử làm bài của người học.
            @elseif ($attemptCount > 0)
                Bộ đã có lượt làm nên không thể sửa nội dung hay xóa; bạn chỉ có thể xuất bản hoặc gỡ xuất bản.
            @else
                Lưu thay đổi trước khi xuất bản. Chỉ bộ đủ 5 câu hợp lệ mới được đưa vào trò chơi.
            @endif
        </p>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2 class="admin-card-title">Bài đọc nguồn</h2>
                <p class="admin-card-sub">Đối chiếu câu hỏi với bài đọc trước khi xuất bản.</p>
            </div>
            @if ($set->source_url)
                <a class="admin-source-link" href="{{ $set->source_url }}" target="_blank" rel="noopener noreferrer">Nguồn bài đọc ↗</a>
            @endif
        </div>

        <details class="admin-details">
            <summary>Đọc bài kiến thức và kiểm chứng câu hỏi</summary>
            <div class="admin-details-body">
                <div class="admin-passage">{{ $set->reading_passage }}</div>
                @if ($set->source_url)
                    <div class="admin-meta-row">
                        <span>Nguồn: <a class="admin-source-link" href="{{ $set->source_url }}" target="_blank" rel="noopener noreferrer">{{ $set->article->source_name ?? $set->source_url }}</a></span>
                    </div>
                @endif
            </div>
        </details>
    </section>

    @unless ($editable)
        <div class="admin-alert admin-alert-info" role="alert">
            <span class="admin-alert-icon" aria-hidden="true">🔒</span>
            <div>
                <strong>Nội dung đang khóa</strong>
                Bộ đã xuất bản hoặc đã có lượt làm nên không được sửa. Các ô bên dưới chỉ để xem lại.
            </div>
        </div>
    @endunless

    <form class="admin-form" method="post" action="{{ route('admin.question-sets.update', $set) }}">
        @csrf
        @method('patch')

        <fieldset @disabled(! $editable) class="admin-stack">
            <section class="admin-card">
                <h2 class="admin-card-title">Thông tin bộ đề</h2>

                <div class="form-group">
                    <label class="form-label" for="title">Tên bộ câu hỏi <span class="req" aria-hidden="true">*</span></label>
                    <input class="form-control" id="title" name="title" value="{{ old('title', $set->title) }}" required maxlength="200">
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                @include('admin.partials.zone')

                @error('questions')<span class="form-error">{{ $message }}</span>@enderror
            </section>

            @for ($i = 0; $i < 5; $i++)
                @php($q = $set->questions->get($i))

                <section class="admin-question-card">
                    <div class="admin-question-head">
                        <span class="admin-question-index"><span class="num">{{ $i + 1 }}</span> Câu {{ $i + 1 }}</span>
                        <span class="admin-chip">{{ $q ? 'Đã có nội dung' : 'Chưa soạn' }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="question_{{ $i }}">Nội dung câu hỏi <span class="req" aria-hidden="true">*</span></label>
                        <textarea class="form-control" id="question_{{ $i }}" name="questions[{{ $i }}][question_text]" required maxlength="2000" placeholder="Nhập câu hỏi dựa trên bài đọc…">{{ old('questions.' . $i . '.question_text', $q?->question_text) }}</textarea>
                    </div>

                    <div class="form-group">
                        <span class="form-label">Bốn lựa chọn & đáp án đúng <span class="req" aria-hidden="true">*</span></span>

                        <div class="admin-option-list">
                            @for ($j = 0; $j < 4; $j++)
                                @php($correctIndex = old('questions.' . $i . '.correct_option_index', $q?->correct_option_index))
                                <div class="admin-option-row">
                                    <input type="radio"
                                           name="questions[{{ $i }}][correct_option_index]"
                                           value="{{ $j }}"
                                           required
                                           @checked((string) $correctIndex === (string) $j)
                                           aria-label="Chọn lựa chọn {{ chr(65 + $j) }} làm đáp án đúng cho câu {{ $i + 1 }}"
                                           title="Chọn làm đáp án đúng">
                                    <span class="admin-option-letter" aria-hidden="true">{{ chr(65 + $j) }}</span>
                                    <input class="form-control"
                                           id="option_{{ $i }}_{{ $j }}"
                                           name="questions[{{ $i }}][options][]"
                                           value="{{ old('questions.' . $i . '.options.' . $j, $q?->options[$j] ?? '') }}"
                                           required
                                           maxlength="1000"
                                           placeholder="Lựa chọn {{ chr(65 + $j) }}"
                                           aria-label="Lựa chọn {{ chr(65 + $j) }} của câu {{ $i + 1 }}">
                                </div>
                            @endfor
                        </div>

                        <p class="form-hint">Tích ô tròn bên trái để chọn đáp án đúng. Bốn lựa chọn phải khác nhau.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="explanation_{{ $i }}">Giải thích <span class="req" aria-hidden="true">*</span></label>
                        <textarea class="form-control" id="explanation_{{ $i }}" name="questions[{{ $i }}][explanation]" required maxlength="5000" placeholder="Vì sao đáp án này đúng, dựa trên bài đọc…">{{ old('questions.' . $i . '.explanation', $q?->explanation) }}</textarea>
                    </div>
                </section>
            @endfor

            @if ($editable)
                <div class="admin-actions-row">
                    <button class="btn btn-primary" type="submit">💾 Lưu 5 câu nháp</button>
                    <span class="form-hint">Sau khi lưu, dùng nút “Kiểm tra & xuất bản” ở đầu trang.</span>
                </div>
            @endif
        </fieldset>
    </form>
@endsection
