@extends('admin.layout')

@section('title', 'Trung tâm biên soạn | AbyssalMind')

@section('admin-content')
    @php
        $publishedCount = $quizSets->where('is_active', true)->count();
        $draftCount = $quizSets->count() - $publishedCount;
        $questionCount = $quizSets->sum('questions_count');
        $attemptCount = $quizSets->sum('attempts_count');
    @endphp

    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Trung Tâm AI</span>
            <h1 class="admin-page-title">Trung tâm biên soạn</h1>
            <p class="admin-page-desc">
                Tìm bài → lưu bài → tạo 5 câu nháp → kiểm duyệt → xuất bản. Mọi thay đổi nội dung đều được kiểm tra trước khi đưa vào trò chơi.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.wikipedia.index') }}">🔎 Tìm Wikipedia</a>
            <a class="btn btn-primary" href="{{ route('admin.question-sets.create') }}">➕ Tạo bộ thủ công</a>
        </div>
    </header>

    <section class="admin-stat-grid" aria-label="Tổng quan kho câu hỏi">
        <article class="admin-stat">
            <div class="admin-stat-label">Tổng bộ đề</div>
            <div class="admin-stat-value is-plain">{{ $quizSets->count() }}</div>
            <div class="admin-stat-hint">{{ $questionCount }} câu hỏi đã soạn</div>
        </article>
        <article class="admin-stat">
            <div class="admin-stat-label">Đã xuất bản</div>
            <div class="admin-stat-value is-live">{{ $publishedCount }}</div>
            <div class="admin-stat-hint">Đang hiển thị trong trò chơi</div>
        </article>
        <article class="admin-stat">
            <div class="admin-stat-label">Bản nháp</div>
            <div class="admin-stat-value is-draft">{{ $draftCount }}</div>
            <div class="admin-stat-hint">Chờ kiểm duyệt nội dung</div>
        </article>
        <article class="admin-stat">
            <div class="admin-stat-label">Lượt làm bài</div>
            <div class="admin-stat-value">{{ number_format($attemptCount) }}</div>
            <div class="admin-stat-hint">Tích lũy trên mọi bộ đề</div>
        </article>
    </section>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2 class="admin-card-title">Bộ câu hỏi</h2>
                <p class="admin-card-sub">Bấm “Xem / biên tập” để kiểm chứng bài đọc, sửa câu hỏi và xuất bản.</p>
            </div>
            <span class="admin-chip">{{ $quizSets->count() }} bộ</span>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Bộ đề</th>
                        <th scope="col">Tầng sinh thái</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Số câu</th>
                        <th scope="col">Lượt làm</th>
                        <th scope="col" class="col-action">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quizSets as $set)
                        <tr>
                            <td>
                                <div class="admin-cell-title">{{ $set->title }}</div>
                                <div class="admin-cell-meta">
                                    {{ $set->topic ?: 'Chưa phân loại' }}
                                    @if ($set->source === 'ai')
                                        · <span class="admin-badge is-info">Gemini</span>
                                    @elseif ($set->source === 'manual')
                                        · <span class="admin-badge is-draft">Thủ công</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="admin-chip">{{ $set->ocean_zone }}</span></td>
                            <td>
                                <span class="admin-badge {{ $set->is_active ? 'is-live' : 'is-draft' }}">
                                    {{ $set->is_active ? 'Đã xuất bản' : 'Bản nháp' }}
                                </span>
                            </td>
                            <td class="admin-num">{{ $set->questions_count }}</td>
                            <td class="admin-num">{{ number_format($set->attempts_count) }}</td>
                            <td class="col-action">
                                <a class="btn btn-outline btn-sm" href="{{ route('admin.question-sets.edit', $set) }}">Xem / biên tập</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">
                                    <span class="admin-empty-icon" aria-hidden="true">🗂️</span>
                                    <div>Chưa có bộ câu hỏi nào.</div>
                                    <div class="admin-actions-row">
                                        <a class="btn btn-outline btn-sm" href="{{ route('admin.wikipedia.index') }}">Tìm bài Wikipedia</a>
                                        <a class="btn btn-primary btn-sm" href="{{ route('admin.question-sets.create') }}">Tạo bộ thủ công</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
