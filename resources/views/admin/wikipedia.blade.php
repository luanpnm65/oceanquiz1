@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('title', 'Tìm bài Wikipedia | OceanQuiz')

@section('content')
@php
    $searchValue = old('search', request('search'));
    $searchValue = is_string($searchValue) ? $searchValue : '';
@endphp
<div class="container admin-page">
    <div class="page-head">
        <div class="page-head__text">
            <p class="eyebrow">Quản trị · Bước 1</p>
            <h1 class="page-title">Tìm bài viết Wikipedia</h1>
            <p class="page-subtitle">Tìm bài viết tiếng Việt về đại dương, xem trước nội dung rồi lưu vào thư viện để Gemini soạn câu hỏi.</p>
        </div>
        <div class="admin-head-actions">
            <a class="btn btn--ghost" href="{{ route('admin.articles.create') }}">Nhập bài thủ công</a>
            <a class="btn btn--ghost" href="{{ route('admin.index') }}">Về Quản trị</a>
        </div>
    </div>

    @include('admin.partials.nav')

    <section class="admin-card mb-5" aria-labelledby="wiki-search-title">
        <div>
            <h2 class="admin-card__title" id="wiki-search-title">Tìm trên Wikipedia</h2>
            <p class="admin-card__desc">Nhập chủ đề đại dương bằng tiếng Việt, ví dụ: Cá voi xanh, Rãnh Mariana, San hô.</p>
        </div>
        <form class="admin-inline-form" method="get" action="{{ route('admin.wikipedia.index') }}">
            <div class="field">
                <label class="field__label" for="search">Chủ đề</label>
                <input class="form-control" type="search" id="search" name="search"
                       value="{{ $searchValue }}" placeholder="Ví dụ: Bạch tuộc Dumbo"
                       required maxlength="100" autocomplete="off">
            </div>
            <button class="btn btn--primary" type="submit">Tìm bài viết</button>
        </form>
        <p class="field__hint">Kết quả lấy từ Wikipedia tiếng Việt, tối đa 10 bài mỗi lần tìm.</p>
    </section>

    @if ($apiError)
        <div class="alert alert--error mb-5" role="alert">
            <p><strong>Chưa lấy được dữ liệu Wikipedia.</strong> {{ $apiError }}</p>
            <p class="mt-2"><a href="{{ route('admin.articles.create') }}">Nhập bài thủ công</a> nếu Wikipedia tiếp tục không phản hồi.</p>
        </div>
    @endif

    @if ($preview)
        <section class="admin-card mb-5" aria-labelledby="wiki-preview-title">
            <div class="admin-card__head admin-card__head--split">
                <div>
                    <p class="eyebrow">Xem trước trước khi lưu</p>
                    <h2 class="admin-card__title" id="wiki-preview-title">{{ $preview['title'] }}</h2>
                    <p class="admin-preview__meta">
                        <span>Nguồn:
                            <a href="{{ $preview['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ $preview['source_name'] }}</a>
                        </span>
                        <span class="dot" aria-hidden="true">·</span>
                        <span>{{ $preview['license'] }}</span>
                        <span class="dot" aria-hidden="true">·</span>
                        <span>Mã bài #{{ $preview['wiki_page_id'] }}</span>
                    </p>
                </div>
                <span class="badge badge--teal">Bản xem trước</span>
            </div>

            <details class="admin-disclosure">
                <summary>Nội dung bài viết · {{ number_format(mb_strlen($preview['content'])) }} ký tự</summary>
                <div class="admin-disclosure__body">
                    <p class="admin-passage">{{ $preview['content'] }}</p>
                </div>
            </details>

            <div class="admin-card__foot">
                <form method="post" action="{{ route('admin.wikipedia.store') }}">
                    @csrf
                    <input type="hidden" name="page_id" value="{{ $preview['wiki_page_id'] }}">
                    <button class="btn btn--primary" type="submit">Lưu bài vào thư viện</button>
                </form>
                @if ($searchValue !== '')
                    <a class="btn btn--ghost admin-spacer" href="{{ route('admin.wikipedia.index', ['search' => $searchValue]) }}">Quay lại kết quả</a>
                @endif
            </div>
        </section>
    @endif

    @if (count($results) > 0)
        <section class="admin-card mb-5" aria-labelledby="wiki-results-title">
            <div class="admin-card__head">
                <div>
                    <h2 class="admin-card__title" id="wiki-results-title">Kết quả tìm kiếm</h2>
                    <p class="admin-card__desc">{{ count($results) }} bài phù hợp với “{{ $searchValue }}”.</p>
                </div>
            </div>
            <ul class="admin-result-list">
                @foreach ($results as $result)
                    <li class="admin-result">
                        <div class="admin-result__main">
                            <h3 class="admin-result__title">{{ $result['title'] }}</h3>
                            <p class="admin-result__snippet">{{ $result['snippet'] }}</p>
                        </div>
                        <div class="admin-result__actions">
                            <a class="btn btn--ghost btn--sm"
                               href="{{ route('admin.wikipedia.index', ['search' => $searchValue, 'page_id' => $result['id']]) }}">Xem trước bài này</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @elseif ($searchValue !== '' && ! $apiError)
        <div class="empty-state mb-5">
            <p>Không tìm thấy bài phù hợp với “{{ $searchValue }}”.</p>
            <p class="mt-2">Hãy thử từ khóa khác hoặc <a href="{{ route('admin.articles.create') }}">nhập bài thủ công</a>.</p>
        </div>
    @elseif (! $preview && ! $apiError)
        <div class="empty-state mb-5">
            <p>Nhập một chủ đề đại dương ở ô phía trên để bắt đầu tìm bài viết.</p>
            <p class="mt-2">Bài đã lưu trước đó nằm trong mục <a href="{{ route('admin.articles.index') }}">Bài đã lưu</a>.</p>
        </div>
    @endif

    <p class="text-sm text-faint">Nội dung Wikipedia thuộc giấy phép CC BY-SA; khi lưu bài, OceanQuiz giữ nguyên liên kết tới bài nguồn và ghi công tác giả.</p>
</div>
@endsection
