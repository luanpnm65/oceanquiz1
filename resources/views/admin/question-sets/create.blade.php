@extends('admin.layout')

@section('title', 'Tạo bộ câu hỏi thủ công | AbyssalMind')

@section('admin-content')
    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Bước 2 · Không qua AI</span>
            <h1 class="admin-page-title">Tạo bộ nháp thủ công</h1>
            <p class="admin-page-desc">
                Chọn một bài đã lưu để tạo bộ 5 câu hỏi trống. Bộ nháp chưa xuất bản nên bạn có thể biên tập thoải mái.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.index') }}">← Bộ câu hỏi</a>
        </div>
    </header>

    <div class="admin-grid-2">
        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2 class="admin-card-title">Thông tin bộ đề</h2>
                    <p class="admin-card-sub">Bài đọc sẽ được sao chép vào bộ nháp tại thời điểm tạo.</p>
                </div>
            </div>

            @if ($articles->isEmpty())
                <div class="admin-alert admin-alert-info">
                    <span class="admin-alert-icon" aria-hidden="true">📚</span>
                    <div>
                        <strong>Chưa có bài đã lưu</strong>
                        Hãy <a href="{{ route('admin.wikipedia.index') }}">tìm bài Wikipedia</a> hoặc <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a> trước.
                    </div>
                </div>
            @endif

            <form class="admin-form" method="post" action="{{ route('admin.question-sets.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="article_id">Bài kiến thức <span class="req" aria-hidden="true">*</span></label>
                    <select class="form-control" id="article_id" name="article_id" required>
                        <option value="">Chọn bài</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}" @selected((string) old('article_id') === (string) $article->id)>{{ $article->title }}</option>
                        @endforeach
                    </select>
                    @error('article_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Tên bộ câu hỏi <span class="req" aria-hidden="true">*</span></label>
                    <input class="form-control" id="title" name="title" required maxlength="200" value="{{ old('title') }}" placeholder="Ví dụ: Bạch tuộc Dumbo và vùng nửa đêm">
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                @include('admin.partials.zone')

                <div class="admin-actions-row">
                    <button class="btn btn-primary" type="submit" @disabled($articles->isEmpty())>Tạo bộ nháp</button>
                    <a class="btn btn-outline" href="{{ route('admin.index') }}">Hủy</a>
                </div>
            </form>
        </section>

        <aside class="admin-card">
            <h2 class="admin-card-title">Sau khi tạo</h2>
            <ol class="admin-steps">
                <li>Bộ nháp xuất hiện trong <a href="{{ route('admin.index') }}">Bộ câu hỏi</a> với trạng thái “Bản nháp”.</li>
                <li>Soạn đủ 5 câu, mỗi câu 4 lựa chọn khác nhau và chọn một đáp án đúng.</li>
                <li>Lưu nội dung, sau đó dùng nút “Kiểm tra & xuất bản”.</li>
            </ol>

            <div class="admin-alert admin-alert-info">
                <span class="admin-alert-icon" aria-hidden="true">💡</span>
                <div>Muốn có câu hỏi gợi ý nhanh? Dùng <a href="{{ route('admin.ai.index') }}">Gemini & nhật ký</a> để sinh 5 câu nháp rồi kiểm chứng lại.</div>
            </div>
        </aside>
    </div>
@endsection
