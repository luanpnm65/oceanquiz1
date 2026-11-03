@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Kiểm duyệt bộ câu hỏi | OceanQuiz')

@section('content')
@php
    $editable = ! $set->is_active && ! $set->attempts()->exists();
    $attemptCount = $set->attempts()->count();
    $questionCount = $set->questions->count();
    $zoneDot = 1;
    foreach (['Ánh Sáng' => 1, 'Chạng Vạng' => 2, 'Nửa Đêm' => 3, 'Đáy Thẳm' => 4, 'Hố Thẳm' => 5] as $needle => $index) {
        if ($set->ocean_zone && str_contains($set->ocean_zone, $needle)) {
            $zoneDot = $index;
            break;
        }
    }
@endphp
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Bộ đề</p>
            <h1 class="page-title">{{ $set->title }}</h1>
            <p class="page-subtitle">Kiểm duyệt 5 câu hỏi, phần giải thích và đáp án đúng trước khi xuất bản.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('admin.index') }}">Về Quản trị</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <div class="admin-status mb-5">
        @if ($set->is_active)
            <span class="badge badge--green">Đã xuất bản</span>
        @else
            <span class="badge">Bản nháp</span>
        @endif
        <span class="text-sm text-muted">{{ $questionCount }} câu</span>
        <span class="text-sm text-faint" aria-hidden="true">·</span>
        <span class="text-sm text-muted">{{ $attemptCount }} lượt làm</span>
        <span class="text-sm text-faint" aria-hidden="true">·</span>
        <span class="zone text-sm">
            <span class="zone__dot zone__dot--{{ $zoneDot }}" aria-hidden="true"></span>
            {{ $set->ocean_zone }}
        </span>
    </div>

    @if (! $editable)
        <div class="alert mb-5" role="status">
            <p><strong>Bộ đề đang khóa.</strong> Nội dung bên dưới chỉ để xem, không sửa được vì bộ đã xuất bản hoặc đã có lượt làm.</p>
            <p class="mt-2">Muốn thay đổi nội dung, hãy tạo bộ đề mới từ cùng bài đọc.</p>
        </div>
    @endif

    <div class="admin-grid admin-grid--aside">
        <div class="stack-5">
            <section class="admin-card" aria-labelledby="set-reading-title">
                <div>
                    <h2 class="admin-card__title" id="set-reading-title">Bài đọc và nguồn</h2>
                    <p class="admin-card__desc">Đọc lại tư liệu để kiểm chứng từng câu hỏi bên dưới.</p>
                </div>

                @if ($set->article)
                    <p class="text-sm text-muted">Bài gốc:
                        <a href="{{ route('admin.articles.edit', $set->article) }}">{{ $set->article->title }}</a>
                    </p>
                @endif

                <details class="admin-disclosure">
                    <summary>Mở bài đọc · {{ number_format(mb_strlen($set->reading_passage ?? '')) }} ký tự</summary>
                    <div class="admin-disclosure__body">
                        <p class="admin-passage">{{ $set->reading_passage ?: 'Bộ đề này chưa có nội dung bài đọc.' }}</p>
                        <div class="admin-disclosure__foot">
                            @if ($set->source_url)
                                <a href="{{ $set->source_url }}" target="_blank" rel="noopener noreferrer">Nguồn bài đọc</a>
                            @else
                                <span>Không có liên kết nguồn.</span>
                            @endif
                            @if ($set->article)
                                <span aria-hidden="true">·</span>
                                <a href="{{ route('admin.articles.edit', $set->article) }}">Xem bài đã lưu</a>
                            @endif
                        </div>
                    </div>
                </details>
            </section>

            <section class="admin-card" aria-labelledby="set-form-title">
                <div>
                    <h2 class="admin-card__title" id="set-form-title">Nội dung bộ câu hỏi</h2>
                    <p class="admin-card__desc">Đúng 5 câu, mỗi câu 4 lựa chọn khác nhau và một đáp án đúng.</p>
                </div>

                @if (! $editable)
                    <p class="admin-readonly-note">Các ô bên dưới đang bị khóa. Bộ đã xuất bản hoặc đã có lượt làm không sửa được nội dung.</p>
                @endif

                <form method="post" action="{{ route('admin.question-sets.update', $set) }}">
                    @csrf
                    @method('patch')
                    <fieldset class="admin-fieldset" @disabled(! $editable)>
                        <legend class="sr-only">Nội dung 5 câu hỏi của bộ đề</legend>

                        <div class="admin-form__row">
                            <div class="field">
                                <label class="field__label" for="title">Tên bộ câu hỏi</label>
                                <input class="form-control" id="title" name="title" type="text"
                                       value="{{ old('title', $set->title) }}" required maxlength="200">
                            </div>
                            @include('admin.partials.zone')
                        </div>

                        @for ($i = 0; $i < 5; $i++)
                            @php
                                $question = $set->questions->get($i);
                                $questionOptions = $question?->options ?? [];
                            @endphp
                            <div class="admin-question">
                                <div class="admin-question__head">
                                    <span class="step-number" aria-hidden="true">{{ $i + 1 }}</span>
                                    <h3 class="admin-question__title">Câu {{ $i + 1 }}</h3>
                                </div>

                                <div class="field">
                                    <label class="field__label" for="question_{{ $i }}">Nội dung câu hỏi</label>
                                    <textarea class="form-control" id="question_{{ $i }}"
                                              name="questions[{{ $i }}][question_text]" rows="2" required maxlength="2000"
                                              placeholder="Nhập câu hỏi tiếng Việt…">{{ old('questions.' . $i . '.question_text', $question?->question_text) }}</textarea>
                                </div>

                                <div class="admin-options">
                                    @for ($j = 0; $j < 4; $j++)
                                        <div class="admin-option">
                                            <span class="admin-option__letter" aria-hidden="true">{{ chr(65 + $j) }}</span>
                                            <div class="admin-option__body">
                                                <label class="admin-option__label" for="option_{{ $i }}_{{ $j }}">Lựa chọn {{ chr(65 + $j) }}</label>
                                                <input class="form-control" id="option_{{ $i }}_{{ $j }}"
                                                       name="questions[{{ $i }}][options][]" type="text"
                                                       value="{{ old('questions.' . $i . '.options.' . $j, $questionOptions[$j] ?? '') }}"
                                                       required maxlength="1000">
                                            </div>
                                            <label class="admin-option__correct">
                                                <input type="radio"
                                                       name="questions[{{ $i }}][correct_option_index]"
                                                       value="{{ $j }}" required
                                                       aria-label="Chọn lựa chọn {{ chr(65 + $j) }} là đáp án đúng"
                                                       @checked((string) old('questions.' . $i . '.correct_option_index', $question?->correct_option_index) === (string) $j)>
                                                <span>Đáp án đúng</span>
                                            </label>
                                        </div>
                                    @endfor
                                </div>

                                <div class="field admin-question__extra">
                                    <label class="field__label" for="explanation_{{ $i }}">Giải thích</label>
                                    <textarea class="form-control" id="explanation_{{ $i }}"
                                              name="questions[{{ $i }}][explanation]" rows="2" required maxlength="5000"
                                              placeholder="Vì sao đáp án này đúng…">{{ old('questions.' . $i . '.explanation', $question?->explanation) }}</textarea>
                                </div>
                            </div>
                        @endfor

                        @if ($editable)
                            <div class="admin-form__actions">
                                <button class="btn btn--primary" type="submit">Lưu 5 câu nháp</button>
                                <a class="btn btn--ghost" href="{{ route('admin.index') }}">Hủy</a>
                            </div>
                        @endif
                    </fieldset>
                </form>
            </section>
        </div>

        <aside class="admin-side admin-side--sticky" aria-label="Xuất bản và lưu trữ bộ đề">
            <section class="admin-card admin-card--accent">
                <p class="eyebrow">Xuất bản</p>
                <h2 class="admin-card__title">Trạng thái bộ đề</h2>
                <p class="admin-status">
                    @if ($set->is_active)
                        <span class="badge badge--green">Đã xuất bản</span>
                    @else
                        <span class="badge">Bản nháp</span>
                    @endif
                </p>
                <dl class="admin-meta">
                    <div class="admin-meta__row">
                        <dt>Số câu</dt>
                        <dd>{{ $questionCount }}</dd>
                    </div>
                    <div class="admin-meta__row">
                        <dt>Lượt làm</dt>
                        <dd>{{ $attemptCount }}</dd>
                    </div>
                    <div class="admin-meta__row">
                        <dt>Tầng đại dương</dt>
                        <dd>{{ $set->ocean_zone }}</dd>
                    </div>
                </dl>
                <p class="text-sm text-muted">
                    {{ $set->is_active
                        ? 'Gỡ xuất bản sẽ ẩn bộ đề khỏi trò chơi nhưng giữ nguyên lịch sử làm bài.'
                        : 'Lưu thay đổi trước khi xuất bản. Chỉ bộ đủ 5 câu hợp lệ mới được đưa vào trò chơi.' }}
                </p>
                <div class="admin-action-stack">
                    <form method="post"
                          action="{{ $set->is_active ? route('admin.question-sets.unpublish', $set) : route('admin.question-sets.publish', $set) }}">
                        @csrf
                        <button class="btn {{ $set->is_active ? 'btn--ghost' : 'btn--primary' }}" type="submit">{{ $set->is_active ? 'Gỡ xuất bản' : 'Kiểm tra & xuất bản' }}</button>
                    </form>
                </div>
            </section>

            @if ($editable)
                <section class="admin-card">
                    <h2 class="admin-card__title">Xóa bộ nháp</h2>
                    <p class="text-sm text-muted">Chỉ xóa được bộ nháp chưa có lượt làm. Thao tác này không thể hoàn tác.</p>
                    <div class="admin-danger-zone">
                        <form method="post" action="{{ route('admin.question-sets.destroy', $set) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn--danger" type="submit">Xóa bộ nháp</button>
                        </form>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
