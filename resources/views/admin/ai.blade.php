@extends('admin.layout')

@section('title', 'Gemini & nhật ký | AbyssalMind')

@section('admin-content')
    <header class="admin-page-head">
        <div>
            <span class="admin-eyebrow">Bước 2 · Sinh câu hỏi</span>
            <h1 class="admin-page-title">Tạo 5 câu hỏi nháp bằng Gemini</h1>
            <p class="admin-page-desc">
                Chọn một bài đã lưu, đặt tên bộ và chọn tầng sinh thái. Gemini chỉ tạo bản nháp — bạn là người kiểm chứng và xuất bản.
            </p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn-outline" href="{{ route('admin.articles.index') }}">📚 Bài đã lưu</a>
            <a class="btn btn-outline" href="{{ route('admin.index') }}">🗂️ Bộ câu hỏi</a>
        </div>
    </header>

    @unless ($configured)
        <div class="admin-alert admin-alert-warning" role="alert">
            <span class="admin-alert-icon" aria-hidden="true">🔑</span>
            <div>
                <strong>Chưa cấu hình GEMINI_API_KEY</strong>
                Thêm khóa vào <code>.env</code>, chạy <code>php artisan config:clear</code> rồi tải lại trang để bật tính năng sinh câu hỏi.
                Bạn vẫn có thể <a href="{{ route('admin.question-sets.create') }}">tạo bộ thủ công</a>.
            </div>
        </div>
    @endunless

    <div class="admin-grid-2">
        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2 class="admin-card-title">Thông tin bộ câu hỏi</h2>
                    <p class="admin-card-sub">Hệ thống luôn yêu cầu đúng 5 câu, 4 lựa chọn và một đáp án đúng.</p>
                </div>
                <span class="admin-status-note">
                    <span class="admin-badge {{ $configured ? 'is-live' : 'is-danger' }}">{{ $configured ? 'Đã cấu hình khóa' : 'Chưa cấu hình khóa' }}</span>
                    <span class="admin-chip">Model: {{ $model ?: 'chưa đặt' }}</span>
                </span>
            </div>

            @if ($articles->isEmpty())
                <div class="admin-alert admin-alert-info">
                    <span class="admin-alert-icon" aria-hidden="true">📚</span>
                    <div>
                        <strong>Chưa có bài nào trong thư viện</strong>
                        Hãy <a href="{{ route('admin.wikipedia.index') }}">tìm bài Wikipedia</a> hoặc <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a> trước khi sinh câu hỏi.
                    </div>
                </div>
            @endif

            <form class="admin-form" method="post" action="{{ route('admin.ai.store') }}" data-generation-form>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="article_id">Bài đã lưu <span class="req" aria-hidden="true">*</span></label>
                    <select class="form-control" id="article_id" name="article_id" required>
                        <option value="">Chọn bài</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}" @selected((string) old('article_id', request('article_id')) === (string) $article->id)>{{ $article->title }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Nội dung bài đọc sẽ được gửi tới Gemini. Chờ tối đa 90 giây.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Tên bộ câu hỏi <span class="req" aria-hidden="true">*</span></label>
                    <input class="form-control" id="title" name="title" required maxlength="200" value="{{ old('title') }}" placeholder="Ví dụ: Cá voi xanh và vùng nước mở">
                </div>

                @include('admin.partials.zone')

                <div class="admin-actions-row">
                    <button class="btn btn-warning" type="submit" @disabled(! $configured || $articles->isEmpty())>
                        Sinh 5 câu nháp bằng Gemini
                    </button>
                    <span class="form-hint">Không tự động xuất bản. Nội dung AI cần được kiểm chứng.</span>
                </div>
            </form>
        </section>

        <aside class="admin-card">
            <h2 class="admin-card-title">Quy trình & lưu ý</h2>

            <ol class="admin-steps">
                <li><strong>Chọn bài nguồn</strong> đã lưu, không sửa bài trong lúc sinh câu hỏi.</li>
                <li><strong>Gemini trả JSON</strong> đúng 5 câu; dữ liệu sai sẽ bị từ chối, không lưu câu hỏi giả.</li>
                <li><strong>Kiểm chứng</strong> lại bài đọc, đáp án và giải thích trước khi xuất bản.</li>
            </ol>

            <hr class="admin-divider">

            <div class="admin-alert admin-alert-info">
                <span class="admin-alert-icon" aria-hidden="true">🧪</span>
                <div>
                    <strong>Nội dung AI cần được kiểm chứng</strong>
                    Mọi phản hồi được ghi vào nhật ký bên dưới, khóa API luôn được che trước khi lưu.
                </div>
            </div>
        </aside>
    </div>

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2 class="admin-card-title">Nhật ký sinh câu hỏi</h2>
                <p class="admin-card-sub">Thời gian hiển thị theo múi giờ Việt Nam. Bấm vào phản hồi để xem dữ liệu gốc.</p>
            </div>
            <span class="admin-chip">{{ $logs->total() }} lần gọi</span>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table admin-table">
                <thead>
                    <tr>
                        <th scope="col">Thời gian (VN)</th>
                        <th scope="col">Bài / model</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $status = $log->status;
                            $statusClass = match ($status) {
                                'success' => 'is-live',
                                'pending' => 'is-pending',
                                'failed' => 'is-danger',
                                default => 'is-draft',
                            };
                            $statusLabel = ['pending' => 'Đang xử lý', 'success' => 'Thành công', 'failed' => 'Lỗi'][$status] ?? $status;
                        @endphp
                        <tr>
                            <td class="admin-num">{{ $log->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="admin-cell-title">{{ $log->article?->title ?? 'Bài đã xóa' }}</div>
                                <div class="admin-cell-meta">{{ $log->model }}</div>
                            </td>
                            <td><span class="admin-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                @if ($log->quiz_set_id)
                                    <a class="btn btn-outline btn-sm" href="{{ route('admin.question-sets.edit', $log->quiz_set_id) }}">Xem bộ nháp</a>
                                @endif

                                @if ($log->error_message)
                                    <p class="admin-log-error">{{ $log->error_message }}</p>
                                @endif

                                @if ($log->response_text)
                                    <details class="admin-details" style="margin-top: 0.5rem;">
                                        <summary>Xem phản hồi</summary>
                                        <div class="admin-details-body">
                                            <pre class="admin-log-response">{{ $log->response_text }}</pre>
                                        </div>
                                    </details>
                                @endif

                                @if (! $log->quiz_set_id && ! $log->error_message && ! $log->response_text)
                                    <span class="admin-muted">Đang chờ kết quả…</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="admin-empty">
                                    <span class="admin-empty-icon" aria-hidden="true">⚡</span>
                                    <div>Chưa có lần gọi Gemini.</div>
                                    <p class="form-hint">Sinh bộ câu hỏi đầu tiên ở biểu mẫu phía trên.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links('admin.partials.pagination') }}
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-generation-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var button = form.querySelector('button[type="submit"]');
                if (!button || button.disabled) {
                    return;
                }
                button.disabled = true;
                button.textContent = 'Đang tạo câu hỏi, vui lòng chờ…';
            });
        });
    </script>
@endpush
