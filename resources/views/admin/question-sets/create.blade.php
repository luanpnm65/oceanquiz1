@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Tạo bộ câu hỏi thủ công | OceanQuiz')

@section('content')
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Bộ đề</p>
            <h1 class="page-title">Tạo bộ đề thủ công</h1>
            <p class="page-subtitle">Chọn bài đọc có sẵn, đặt tên bộ đề và chọn tầng đại dương. Bộ mới được lưu ở trạng thái nháp để bạn tự soạn 5 câu hỏi.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('admin.index') }}">Về Quản trị</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <div class="admin-grid admin-grid--aside">
        <section class="admin-card" aria-labelledby="set-create-title">
            <div>
                <h2 class="admin-card__title" id="set-create-title">Thông tin bộ đề</h2>
                <p class="admin-card__desc">Sau khi tạo, bạn sẽ được chuyển sang trang soạn và duyệt 5 câu hỏi.</p>
            </div>

            @if ($articles->isEmpty())
                <div class="alert alert--error" role="alert">
                    Chưa có bài đọc nào. Hãy <a href="{{ route('admin.wikipedia.index') }}">tìm bài Wikipedia</a> hoặc
                    <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a> trước khi tạo bộ đề.
                </div>
            @endif

            <form method="post" action="{{ route('admin.question-sets.store') }}">
                @csrf
                <div class="field">
                    <label class="field__label" for="article_id">Bài kiến thức</label>
                    <select class="form-control" id="article_id" name="article_id" required @disabled($articles->isEmpty())>
                        <option value="" disabled @selected(blank(old('article_id')))>Chọn bài đọc</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}"
                                @selected((string) old('article_id') === (string) $article->id)>{{ $article->title }}</option>
                        @endforeach
                    </select>
                    <p class="field__hint">Nội dung bài đọc sẽ được dùng làm tư liệu cho bộ đề.</p>
                </div>

                <div class="field">
                    <label class="field__label" for="title">Tên bộ câu hỏi</label>
                    <input class="form-control" id="title" name="title" type="text" value="{{ old('title') }}"
                           placeholder="Ví dụ: Bạch tuộc Dumbo và vùng nửa đêm" required maxlength="200">
                    <p class="field__hint">Tên này hiển thị cho người học khi bộ đề được xuất bản.</p>
                </div>

                @include('admin.partials.zone')

                <div class="admin-form__actions">
                    <button class="btn btn--primary" type="submit" @disabled($articles->isEmpty())>Tạo bộ nháp</button>
                    <a class="btn btn--ghost" href="{{ route('admin.index') }}">Hủy</a>
                </div>
            </form>
        </section>

        <aside class="admin-side admin-side--sticky" aria-label="Hướng dẫn tạo bộ đề">
            <section class="admin-card admin-card--accent">
                <p class="eyebrow">Sau khi tạo</p>
                <h2 class="admin-card__title">Các bước tiếp theo</h2>
                <ol class="admin-help-list">
                    <li>Soạn nội dung câu hỏi, 4 lựa chọn và đáp án đúng.</li>
                    <li>Viết giải thích ngắn cho từng câu.</li>
                    <li>Kiểm tra rồi xuất bản để người học có thể gặp bộ đề.</li>
                </ol>
                <div class="admin-action-stack mt-4">
                    <a class="btn btn--ghost" href="{{ route('admin.ai.index') }}">Nhờ Gemini soạn thay</a>
                </div>
            </section>
            <section class="admin-card">
                <h2 class="admin-card__title">Lưu ý</h2>
                <p class="text-sm text-muted">Bộ đề nháp chưa có lượt làm thì sửa và xóa được. Sau khi xuất bản hoặc có người làm bài, nội dung sẽ bị khóa để giữ lịch sử.</p>
            </section>
        </aside>
    </div>
</div>
@endsection
