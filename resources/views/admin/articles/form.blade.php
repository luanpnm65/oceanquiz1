@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', ($article->exists ? 'Bài đã lưu' : 'Thêm bài kiến thức') . ' | OceanQuiz')

@section('content')
@php
    $canEdit = ! $article->exists || ! $article->isLocked();
    $locked = $article->exists && $article->isLocked();
@endphp
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Thư viện</p>
            <h1 class="page-title">{{ $article->exists ? 'Bài đã lưu' : 'Thêm bài kiến thức' }}</h1>
            <p class="page-subtitle">{{ $article->exists ? 'Xem lại nội dung bài đọc và nguồn trước khi sinh câu hỏi.' : 'Dán nội dung bài đọc tiếng Việt và ghi rõ nguồn để dùng cho bộ đề mới.' }}</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('admin.articles.index') }}">Danh sách bài</a>
        </div>
    </div>

    @include('admin.partials.nav')

    @if ($locked)
        <div class="alert alert--error mb-5" role="alert">
            <p><strong>Bài này đang bị khóa.</strong> Bài đã gắn với bộ xuất bản hoặc đã có lượt làm, nên không thể sửa nội dung.</p>
            <p class="mt-2">Tạo bài mới nếu cần thay đổi, lịch sử làm bài vẫn được giữ nguyên.</p>
        </div>
    @endif

    <div class="admin-grid admin-grid--aside">
        <section class="admin-card" aria-labelledby="article-form-title">
            <div>
                <h2 class="admin-card__title" id="article-form-title">Nội dung bài viết</h2>
                <p class="admin-card__desc">Nội dung càng rõ ràng thì câu hỏi do Gemini soạn càng sát bài đọc.</p>
            </div>

            <form method="post"
                  action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
                @csrf
                @if ($article->exists)
                    @method('patch')
                @endif

                <div class="field">
                    <label class="field__label" for="title">Tiêu đề</label>
                    <input class="form-control" id="title" name="title" type="text"
                           value="{{ old('title', $article->title) }}" required maxlength="200"
                           placeholder="Ví dụ: Bạch tuộc Dumbo và vùng nửa đêm" @disabled(! $canEdit)>
                </div>

                <div class="field">
                    <label class="field__label" for="content">Nội dung</label>
                    <textarea class="form-control" id="content" name="content" rows="18"
                              minlength="80" maxlength="100000" required
                              placeholder="Dán nội dung bài đọc tiếng Việt vào đây…" @disabled(! $canEdit)>{{ old('content', $article->content) }}</textarea>
                    <p class="field__hint">Tối thiểu 80 ký tự, tối đa 100.000 ký tự.</p>
                </div>

                <div class="admin-form__row">
                    <div class="field">
                        <label class="field__label" for="source_name">Tên nguồn</label>
                        <input class="form-control" id="source_name" name="source_name" type="text"
                               value="{{ old('source_name', $article->source_name) }}" maxlength="255"
                               placeholder="Ví dụ: Wikipedia tiếng Việt" @disabled(! $canEdit)>
                    </div>
                    <div class="field">
                        <label class="field__label" for="license">Giấy phép</label>
                        <input class="form-control" id="license" name="license" type="text"
                               value="{{ old('license', $article->license) }}" maxlength="255"
                               placeholder="Ví dụ: CC BY-SA 4.0" @disabled(! $canEdit)>
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="source_url">URL nguồn</label>
                    <input class="form-control" id="source_url" name="source_url" type="url"
                           value="{{ old('source_url', $article->source_url) }}" maxlength="255"
                           placeholder="https://vi.wikipedia.org/…" @disabled(! $canEdit)>
                    <p class="field__hint">Liên kết đầy đủ tới bài gốc, dùng để ghi công tác giả.</p>
                </div>

                @if ($canEdit)
                    <div class="admin-form__actions">
                        <button class="btn btn--primary" type="submit">Lưu bài</button>
                        <a class="btn btn--ghost" href="{{ route('admin.articles.index') }}">Hủy</a>
                    </div>
                @endif
            </form>
        </section>

        <aside class="admin-side admin-side--sticky" aria-label="Hướng dẫn và bước tiếp theo">
            <section class="admin-card admin-card--accent">
                <p class="eyebrow">Quy trình</p>
                <h2 class="admin-card__title">Từ bài đọc tới bộ đề</h2>
                <ol class="admin-help-list">
                    <li>Lưu nội dung bài đọc cùng nguồn rõ ràng.</li>
                    <li>Để Gemini soạn 5 câu hỏi nháp từ chính bài này.</li>
                    <li>Kiểm chứng từng câu rồi mới xuất bản bộ đề.</li>
                </ol>
                @if ($article->exists)
                    <div class="admin-action-stack mt-4">
                        <a class="btn btn--primary" href="{{ route('admin.ai.index', ['article_id' => $article->id]) }}">Sinh câu hỏi từ bài này</a>
                        <a class="btn btn--ghost" href="{{ route('admin.question-sets.create') }}">Tạo bộ thủ công</a>
                    </div>
                @else
                    <p class="field__hint">Sau khi lưu bài, bạn có thể sinh câu hỏi bằng Gemini ngay từ trang này.</p>
                @endif
            </section>

            @if ($locked)
                <section class="admin-card">
                    <h2 class="admin-card__title">Vì sao bị khóa?</h2>
                    <p class="text-sm text-muted">Bài đọc đã được dùng cho một bộ đề đã xuất bản hoặc đã có lượt làm. Sửa nội dung lúc này sẽ làm lệch câu hỏi và kết quả cũ, nên hệ thống giữ nguyên bản gốc.</p>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
