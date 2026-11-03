@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Quản trị | OceanQuiz')

@section('content')
@php
    $drafts = $quizSets->where('is_active', false);
    $latestDraft = $drafts->first();
    $latestSet = $quizSets->first();
    $zoneDotMap = ['Ánh Sáng' => 1, 'Chạng Vạng' => 2, 'Nửa Đêm' => 3, 'Đáy Thẳm' => 4, 'Hố Thẳm' => 5];
    $searchValue = old('search', request('search'));
    $searchValue = is_string($searchValue) ? $searchValue : '';
@endphp
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <h1 class="page-title">Quản trị</h1>
            <p class="page-subtitle">Tạo bộ đề mới từ bài viết Wikipedia và câu hỏi do Gemini AI soạn. Mỗi bước là một biểu mẫu riêng.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('dashboard') }}">Về bảng điều khiển</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <div class="admin-grid admin-grid--equal">
        <section class="admin-card" aria-labelledby="admin-step-1-title">
            <div class="admin-card__head">
                <span class="step-number" aria-hidden="true">1</span>
                <div>
                    <h2 class="admin-card__title" id="admin-step-1-title">Lấy bài viết từ Wikipedia</h2>
                    <p class="admin-card__desc">Nhập chủ đề đại dương bằng tiếng Việt, ví dụ: Cá voi xanh, Rãnh Mariana, San hô.</p>
                </div>
            </div>

            <form class="admin-inline-form" method="get" action="{{ route('admin.wikipedia.index') }}">
                <div class="field">
                    <label class="field__label" for="search">Chủ đề</label>
                    <input class="form-control" type="search" id="search" name="search"
                           value="{{ $searchValue }}" placeholder="Ví dụ: Bạch tuộc Dumbo"
                           required maxlength="100" autocomplete="off">
                </div>
                <button class="btn btn--primary" type="submit">Lấy bài viết</button>
            </form>
            <p class="field__hint">Tìm bằng tiếng Việt để có kết quả sát nhất.</p>

            <div class="admin-note">
                @if ($latestSet && $latestSet->topic)
                    <span>Bài đã lưu gần nhất: <strong>{{ $latestSet->topic }}</strong>@if ($latestSet->questions_count) · {{ $latestSet->questions_count }} câu hỏi @endif</span>
                @else
                    <span>Chưa có bài nào được lưu. Hãy tìm một chủ đề đại dương để bắt đầu, sau đó lưu bài vào thư viện.</span>
                @endif
            </div>

            <div class="admin-subnav">
                <a href="{{ route('admin.articles.index') }}">Bài đã lưu</a>
                <a href="{{ route('admin.ai.index') }}">Gemini &amp; nhật ký</a>
            </div>
        </section>

        <section class="admin-card" aria-labelledby="admin-step-2-title">
            <div class="admin-card__head">
                <span class="step-number" aria-hidden="true">2</span>
                <div>
                    <h2 class="admin-card__title" id="admin-step-2-title">Duyệt câu hỏi do AI tạo</h2>
                    <p class="admin-card__desc">Xem trước, sửa hoặc xóa từng câu trước khi lưu vào cơ sở dữ liệu.</p>
                </div>
            </div>

            @if ($drafts->isNotEmpty())
                <div class="admin-draft-list">
                    @foreach ($drafts as $draft)
                        <div class="admin-draft">
                            <div class="admin-draft__main">
                                <p class="admin-draft__title">{{ $draft->title }}</p>
                                <div class="admin-draft__meta">
                                    <span class="badge">Bản nháp</span>
                                    <span>{{ $draft->questions_count }} câu</span>
                                    @if ($draft->ocean_zone)
                                        <span aria-hidden="true">·</span>
                                        <span>{{ $draft->ocean_zone }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="admin-draft__actions">
                                <a href="{{ route('admin.question-sets.edit', $draft) }}">Sửa</a>
                                @if ($draft->article_id)
                                    <a href="{{ route('admin.ai.index', ['article_id' => $draft->article_id]) }}">Sinh câu hỏi</a>
                                @else
                                    <a href="{{ route('admin.ai.index') }}">Sinh câu hỏi</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">Chưa có bộ nháp nào. Hãy lấy bài Wikipedia rồi sinh câu hỏi bằng Gemini.</div>
            @endif

            <div class="admin-card__foot">
                <a class="btn btn--ghost" href="{{ route('admin.question-sets.create') }}">Thêm câu hỏi thủ công</a>
                <a class="btn btn--primary admin-spacer" href="{{ $latestDraft ? route('admin.question-sets.edit', $latestDraft) : route('admin.ai.index') }}">Lưu bộ đề</a>
            </div>
        </section>

        <section class="admin-card admin-table-card admin-grid--full" aria-labelledby="admin-sets-title">
            <div class="admin-table-card__head">
                <div>
                    <h2 class="card__title" id="admin-sets-title">Danh sách bộ đề</h2>
                    <p class="card__subtitle">Tất cả bộ đề đã tạo, kèm trạng thái và lượt làm.</p>
                </div>
                <p class="text-sm text-faint">{{ $quizSets->count() }} bộ đề</p>
            </div>

            @if ($quizSets->isNotEmpty())
                <div class="table-wrap admin-table-wrap">
                    <table class="table">
                        <caption class="sr-only">Danh sách bộ đề đã tạo</caption>
                        <thead>
                            <tr>
                                <th scope="col">Mã</th>
                                <th scope="col">Tiêu đề và nguồn</th>
                                <th scope="col">Tầng đại dương</th>
                                <th scope="col" class="num">Số câu</th>
                                <th scope="col" class="num">Lượt làm</th>
                                <th scope="col" class="admin-th-actions">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quizSets as $set)
                                @php
                                    $zoneShort = trim(preg_replace('/\s*\([^)]*\)/u', '', $set->ocean_zone ?? ''));
                                    $zoneDot = 1;
                                    foreach ($zoneDotMap as $needle => $index) {
                                        if ($zoneShort !== '' && str_contains($zoneShort, $needle)) {
                                            $zoneDot = $index;
                                            break;
                                        }
                                    }
                                    $sourceDisplay = $set->source_url ? urldecode(preg_replace('#^https?://#i', '', $set->source_url)) : null;
                                    $canDelete = ! $set->is_active && $set->attempts_count === 0;
                                @endphp
                                <tr>
                                    <td><span class="admin-id">#{{ $set->id }}</span></td>
                                    <td>
                                        <span class="admin-cell-title">{{ $set->title }}</span>
                                        @if ($sourceDisplay)
                                            <a class="admin-source" href="{{ $set->source_url }}" target="_blank"
                                               rel="noopener noreferrer" title="{{ $set->source_url }}">{{ mb_strimwidth($sourceDisplay, 0, 46, '…', 'UTF-8') }}</a>
                                        @else
                                            <span class="admin-source admin-source--empty">Không có nguồn</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="zone">
                                            <span class="zone__dot zone__dot--{{ $zoneDot }}" aria-hidden="true"></span>
                                            {{ $zoneShort !== '' ? $zoneShort : 'Chưa xác định' }}
                                        </span>
                                    </td>
                                    <td class="num">{{ $set->questions_count }}</td>
                                    <td class="num">{{ $set->attempts_count }}</td>
                                    <td>
                                        <div class="admin-cell-actions">
                                            @if ($canDelete)
                                                <form method="post" action="{{ route('admin.question-sets.destroy', $set) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="btn btn--danger btn--sm" type="submit">Xóa</button>
                                                </form>
                                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.question-sets.edit', $set) }}">Sửa</a>
                                            @else
                                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.question-sets.edit', $set) }}">Xem / biên tập</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="admin-empty-wrapper">
                    <div class="empty-state">Chưa có bộ đề nào. Hãy lấy bài Wikipedia ở bước 1 và sinh câu hỏi bằng Gemini.</div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
