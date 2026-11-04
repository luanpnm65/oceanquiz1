@extends('admin.layout')

@section('title', 'Bài kiến thức đã lưu | AbyssalMind')

@section('admin-content')
    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Thư viện kiến thức</span>
            <h1 class="admin-page-title">Bài kiến thức đã lưu</h1>
            <p class="admin-page-desc">
                Mỗi bộ câu hỏi gắn với một bài đọc có nguồn rõ ràng. Bài đã xuất bản hoặc đã có lượt làm sẽ được khóa để giữ lịch sử.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.wikipedia.index') }}">🔎 Tìm Wikipedia</a>
            <a class="btn btn-primary" href="{{ route('admin.articles.create') }}">➕ Thêm bài thủ công</a>
        </div>
    </header>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2 class="admin-card-title">Danh sách bài đã lưu</h2>
                <p class="admin-card-sub">Bấm “Xem / sửa bài” để biên tập, hoặc “Sinh câu hỏi” để chuyển thẳng sang Gemini.</p>
            </div>
            <span class="admin-chip">{{ $articles->total() }} bài</span>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Tiêu đề</th>
                        <th scope="col">Nguồn</th>
                        <th scope="col">Bộ câu hỏi</th>
                        <th scope="col">Cập nhật</th>
                        <th scope="col" class="col-action">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>
                                <div class="admin-cell-title">{{ $article->title }}</div>
                                <div class="admin-cell-meta">{{ mb_strimwidth(strip_tags($article->content), 0, 120, '…') }}</div>
                            </td>
                            <td>
                                @if ($article->source_url)
                                    <a class="admin-source-link" href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer">{{ $article->source_name ?: 'Nguồn ngoài' }}</a>
                                @else
                                    <span class="admin-muted">{{ $article->source_name ?: 'Tự biên soạn' }}</span>
                                @endif
                                <div class="admin-cell-meta">{{ $article->license ?: 'Chưa ghi giấy phép' }}</div>
                            </td>
                            <td class="admin-num">{{ $article->quiz_sets_count }}</td>
                            <td class="admin-cell-meta">{{ $article->updated_at?->diffForHumans() }}</td>
                            <td class="col-action">
                                <div class="admin-actions-row" style="justify-content: flex-end;">
                                    <a class="btn btn-outline btn-sm" href="{{ route('admin.articles.edit', $article) }}">Xem / sửa bài</a>
                                    <a class="btn btn-warning btn-sm" href="{{ route('admin.ai.index', ['article_id' => $article->id]) }}">Sinh câu hỏi</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="admin-empty">
                                    <span class="admin-empty-icon" aria-hidden="true">📚</span>
                                    <div>Chưa có bài nào trong thư viện.</div>
                                    <div class="admin-actions-row">
                                        <a class="btn btn-outline btn-sm" href="{{ route('admin.wikipedia.index') }}">Tìm bài Wikipedia</a>
                                        <a class="btn btn-primary btn-sm" href="{{ route('admin.articles.create') }}">Nhập bài thủ công</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $articles->links('admin.partials.pagination') }}
    </section>
@endsection
