@extends('admin.layout')

@section('title', 'Biên soạn bài kiến thức | AbyssalMind')

@section('admin-content')
    @php($locked = $article->exists && $article->isLocked())

    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Thư viện kiến thức</span>
            <h1 class="admin-page-title">{{ $article->exists ? 'Bài đã lưu' : 'Thêm bài kiến thức' }}</h1>
            <p class="admin-page-desc">
                Bài đọc là nguồn kiến thức cho bộ câu hỏi. Ghi rõ nguồn, đường dẫn và giấy phép để người học có thể kiểm chứng.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.articles.index') }}">← Danh sách bài</a>
        </div>
    </header>

    @if ($locked)
        <div class="admin-alert admin-alert-warning" role="alert">
            <span class="admin-alert-icon" aria-hidden="true">🔒</span>
            <div>
                <strong>Bài này đang được khóa</strong>
                Bài đã gắn với bộ đã xuất bản hoặc đã có lượt làm, nên nội dung không thể sửa để giữ nguyên lịch sử.
                Hãy tạo bài mới nếu cần thay đổi.
            </div>
        </div>
    @endif

    <form class="admin-form-grid" method="post" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
        @csrf
        @if ($article->exists)
            @method('patch')
        @endif

        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2 class="admin-card-title">Nội dung bài đọc</h2>
                    <p class="admin-card-sub">Tối thiểu 80 ký tự, tối đa 100.000 ký tự.</p>
                </div>
                <span class="admin-badge {{ $locked ? 'is-draft' : 'is-info' }}">{{ $locked ? 'Chỉ đọc' : 'Có thể biên tập' }}</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Tiêu đề <span class="req" aria-hidden="true">*</span></label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" required maxlength="200" value="{{ old('title', $article->title) }}" placeholder="Ví dụ: Cá voi xanh và vùng nước mở">
                @error('title')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="content">Nội dung <span class="req" aria-hidden="true">*</span></label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="18" minlength="80" maxlength="100000" required placeholder="Dán nội dung bài đọc đã kiểm chứng…">{{ old('content', $article->content) }}</textarea>
                <p class="form-hint">Nên giữ nguyên văn phong bài nguồn; bạn có thể rút gọn nhưng không nên thêm dữ kiện chưa kiểm chứng.</p>
                @error('content')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </section>

        <aside class="admin-stack">
            <section class="admin-card">
                <h2 class="admin-card-title">Nguồn & giấy phép</h2>

                <div class="form-group">
                    <label class="form-label" for="source_name">Tên nguồn</label>
                    <input class="form-control @error('source_name') is-invalid @enderror" id="source_name" name="source_name" value="{{ old('source_name', $article->source_name) }}" placeholder="Wikipedia tiếng Việt · Cá voi xanh">
                    @error('source_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="source_url">URL nguồn</label>
                    <input class="form-control @error('source_url') is-invalid @enderror" id="source_url" name="source_url" type="url" value="{{ old('source_url', $article->source_url) }}" placeholder="https://vi.wikipedia.org/…">
                    @error('source_url')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="license">Giấy phép</label>
                    <input class="form-control @error('license') is-invalid @enderror" id="license" name="license" value="{{ old('license', $article->license) }}" placeholder="CC BY-SA 4.0">
                    @error('license')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </section>

            <section class="admin-card">
                <h2 class="admin-card-title">Hoàn tất</h2>

                <div class="admin-actions-row">
                    @unless ($locked)
                        <button class="btn btn-primary" type="submit">💾 Lưu bài</button>
                    @endunless
                    <a class="btn btn-outline" href="{{ route('admin.articles.index') }}">Hủy</a>
                </div>

                @if ($article->exists)
                    <hr class="admin-divider">
                    <a class="btn btn-warning btn-block" href="{{ route('admin.ai.index', ['article_id' => $article->id]) }}">⚡ Sinh câu hỏi từ bài này</a>
                    <p class="form-hint">Chuyển sang Gemini với bài này được chọn sẵn.</p>
                @else
                    <p class="form-hint">Sau khi lưu, bạn có thể sinh 5 câu hỏi nháp bằng Gemini từ chính bài này.</p>
                @endif
            </section>
        </aside>
    </form>
@endsection
