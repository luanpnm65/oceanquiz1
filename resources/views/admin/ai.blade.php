@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Gemini & nhật ký | OceanQuiz')

@section('content')
@php
    $selectedArticleId = old('article_id', request('article_id'));
    $selectedArticleId = is_scalar($selectedArticleId) ? (string) $selectedArticleId : '';
@endphp
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Gemini</p>
            <h1 class="page-title">Tạo câu hỏi bằng Gemini</h1>
            <p class="page-subtitle">Chọn một bài đã lưu, đặt tên bộ đề và để Gemini soạn 5 câu hỏi nháp. Mọi lần gọi đều được ghi vào nhật ký.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('admin.articles.index') }}">Bài đã lưu</a>
            <a class="btn btn--ghost" href="{{ route('admin.index') }}">Về Quản trị</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <div class="admin-grid admin-grid--aside">
        <section class="admin-card" aria-labelledby="ai-form-title">
            <div>
                <h2 class="admin-card__title" id="ai-form-title">Sinh 5 câu hỏi nháp</h2>
                <p class="admin-card__desc">Quá trình chờ tối đa 90 giây. Câu hỏi trả về chỉ là bản nháp, cần kiểm chứng trước khi xuất bản.</p>
            </div>

            @if ($articles->isEmpty())
                <div class="alert alert--error" role="alert">
                    Chưa có bài nào trong thư viện. Hãy <a href="{{ route('admin.wikipedia.index') }}">tìm bài Wikipedia</a> hoặc
                    <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a> trước.
                </div>
            @endif

            <form method="post" action="{{ route('admin.ai.store') }}">
                @csrf
                <div class="field">
                    <label class="field__label" for="article_id">Bài đã lưu</label>
                    <select class="form-control" id="article_id" name="article_id" required @disabled($articles->isEmpty())>
                        <option value="" @selected($selectedArticleId === '')>Chọn bài</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}"
                                @selected($selectedArticleId === (string) $article->id)>{{ $article->title }}</option>
                        @endforeach
                    </select>
                    <p class="field__hint">Gemini chỉ đọc nội dung của bài được chọn.</p>
                </div>

                <div class="field">
                    <label class="field__label" for="title">Tên bộ câu hỏi</label>
                    <input class="form-control" id="title" name="title" type="text" value="{{ old('title') }}"
                           placeholder="Ví dụ: Bạch tuộc Dumbo và vùng nửa đêm" required maxlength="200">
                    <p class="field__hint">Tên này hiển thị cho người học khi bộ đề được xuất bản.</p>
                </div>

                @include('admin.partials.zone')

                <div class="admin-form__actions">
                    <button class="btn btn--primary" type="submit" @disabled(! $configured || $articles->isEmpty())>Sinh 5 câu nháp bằng Gemini</button>
                    @if (! $configured)
                        <span class="admin-status__hint">Nút bị khóa vì chưa cấu hình khóa Gemini.</span>
                    @elseif ($articles->isEmpty())
                        <span class="admin-status__hint">Nút bị khóa vì thư viện chưa có bài nào.</span>
                    @else
                        <span class="admin-status__hint">Bạn sẽ được chuyển sang trang duyệt bộ đề khi xong.</span>
                    @endif
                </div>
            </form>
        </section>

        <aside class="admin-side admin-side--sticky" aria-label="Trạng thái Gemini">
            <section class="admin-card admin-card--accent">
                <p class="eyebrow">Trạng thái</p>
                <h2 class="admin-card__title">Gemini</h2>
                <p class="admin-status">
                    @if ($configured)
                        <span class="badge badge--green">Đã cấu hình khóa</span>
                    @else
                        <span class="badge badge--red">Chưa cấu hình khóa</span>
                    @endif
                </p>
                <dl class="admin-meta">
                    <div class="admin-meta__row">
                        <dt>Model</dt>
                        <dd>{{ $model }}</dd>
                    </div>
                    <div class="admin-meta__row">
                        <dt>Thời gian chờ tối đa</dt>
                        <dd>90 giây</dd>
                    </div>
                    <div class="admin-meta__row">
                        <dt>Bài đã lưu</dt>
                        <dd>{{ $articles->count() }}</dd>
                    </div>
                    <div class="admin-meta__row">
                        <dt>Lần gọi trong nhật ký</dt>
                        <dd>{{ $logs->total() }}</dd>
                    </div>
                </dl>
                @if (! $configured)
                    <div class="alert alert--error" role="alert">
                        Chưa có khóa GEMINI_API_KEY trong tệp <code>.env</code>. Hãy nhờ quản trị máy chủ cấu hình rồi tải lại trang.
                    </div>
                @endif
                <p class="text-sm text-muted">Nội dung do AI soạn có thể sai; hãy đối chiếu với bài đọc trước khi xuất bản.</p>
            </section>
        </aside>
    </div>

    <section class="admin-card admin-table-card" aria-labelledby="ai-logs-title">
        <div class="admin-table-card__head">
            <div>
                <h2 class="card__title" id="ai-logs-title">Nhật ký sinh câu hỏi</h2>
                <p class="card__subtitle">Thời gian hiển thị theo múi giờ Việt Nam.</p>
            </div>
            <p class="text-sm text-faint">{{ $logs->total() }} lần gọi</p>
        </div>

        <div class="table-wrap admin-table-wrap">
            <table class="table">
                <caption class="sr-only">Nhật ký các lần gọi Gemini</caption>
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
                            $statusText = ['pending' => 'Đang xử lý', 'success' => 'Thành công', 'failed' => 'Lỗi'][$log->status] ?? $log->status;
                            $statusClass = ['success' => 'badge--green', 'failed' => 'badge--red'][$log->status] ?? '';
                        @endphp
                        <tr>
                            <td>
                                <span class="admin-cell-title">{{ $log->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</span>
                                <span class="admin-source">{{ $log->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i:s') }}</span>
                            </td>
                            <td>
                                <span class="admin-cell-title">{{ $log->article?->title ?? 'Bài đã xóa' }}</span>
                                <span class="admin-source">{{ $log->model }}</span>
                            </td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td>
                                @if ($log->quiz_set_id)
                                    <a href="{{ route('admin.question-sets.edit', $log->quiz_set_id) }}">Xem bộ nháp</a>
                                @endif
                                @if ($log->error_message)
                                    <p class="text-sm text-red">{{ $log->error_message }}</p>
                                @endif
                                @if ($log->response_text)
                                    <details class="admin-disclosure admin-log-details">
                                        <summary>Xem phản hồi của Gemini</summary>
                                        <div class="admin-disclosure__body">
                                            <pre class="admin-pre">{{ $log->response_text }}</pre>
                                        </div>
                                    </details>
                                @endif
                                @if (! $log->quiz_set_id && ! $log->error_message && ! $log->response_text)
                                    <span class="text-sm text-faint">Chưa có kết quả.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-table-empty">Chưa có lần gọi Gemini nào. Hãy dùng biểu mẫu phía trên để sinh câu hỏi đầu tiên.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="admin-pagination">
                {{ $logs->links('pagination::simple-default') }}
            </div>
        @endif
    </section>
</div>
@endsection
